<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: BatchModel
 *
 * Handles stock batches for inventory tracking (FIFO).
 */
class BatchModel extends Model
{
    protected $table = 'batches';
    protected $primary_key = 'batch_id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Paginate batches joined with items, ordered FIFO.
     *
     * @param string $q
     * @param int|null $per_page
     * @param int|null $page
     * @return array
     */
    public function paginate_with_items(string $q = '', ?int $per_page = null, ?int $page = null, ?string $category = null)
    {
        $query = $this->db->table($this->table . ' b')
            ->join('items i', 'b.item_id = i.item_id', 'INNER ');

        if (!empty($q)) {
            $like = '%' . $q . '%';
            $query->grouped(function ($db) use ($like) {
                $db->like('i.item_name', $like)
                    ->or_like('b.batch_code', $like)
                    ->or_like('i.category', $like);
            });
        }

        if (!empty($category)) {
            $query->where('i.category', $category);
        }

        $count_query = clone $query;
        $total = $count_query->select_count('b.batch_id', 'count')->get()['count'] ?? 0;

        $query->select('b.batch_id, b.item_id, b.batch_code, b.quantity, b.remaining_quantity, b.location, b.manufacture_date, b.expiry_date, b.received_date, i.item_name, i.category');
        $query->order_by('b.received_date', 'ASC');

        if (is_null($page) || is_null($per_page)) {
            return [
                'records' => $query->get_all(),
                'total_rows' => (int) $total
            ];
        }

        $records = $query
            ->pagination($per_page, $page)
            ->get_all();

        return [
            'records' => $records,
            'total_rows' => (int) $total
        ];
    }

    /**
     * Sum remaining quantities by location.
     *
     * @param string $location
     * @return int
     */
    public function total_by_location(string $location): int
    {
        $rows = $this->db->table($this->table)
            ->where('location', $location)
            ->select('quantity, remaining_quantity')
            ->get_all();

        $total = 0;
        foreach ($rows as $row) {
            $remaining = isset($row['remaining_quantity']) && $row['remaining_quantity'] !== null
                ? (int) $row['remaining_quantity']
                : (int) ($row['quantity'] ?? 0);
            if ($remaining > 0) {
                $total += $remaining;
            }
        }

        return $total;
    }

    public function remaining_for_item_location(int $item_id, string $location): int
    {
        $result = $this->db->table($this->table)
            ->where('item_id', $item_id)
            ->where('location', $location)
            ->select_sum('remaining_quantity', 'total')
            ->get();

        return (int) ($result['total'] ?? 0);
    }

    public function has_any_batch_for_item(int $item_id): bool
    {
        $result = $this->db->table($this->table)
            ->where('item_id', $item_id)
            ->select_count('batch_id', 'count')
            ->get();

        return ((int) ($result['count'] ?? 0)) > 0;
    }

    public function deduct_stock_for_item(int $item_id, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        foreach (['main', 'reserve'] as $location) {
            if ($quantity <= 0) {
                break;
            }

            $batches = $this->db->table($this->table)
                ->where('item_id', $item_id)
                ->where('location', $location)
                ->where('remaining_quantity', '>', 0)
                ->order_by('received_date', 'ASC')
                ->get_all();

            foreach ($batches as $batch) {
                if ($quantity <= 0) {
                    break;
                }

                $current = isset($batch['remaining_quantity']) && $batch['remaining_quantity'] !== null
                    ? (int) $batch['remaining_quantity']
                    : (int) ($batch['quantity'] ?? 0);
                if ($current <= 0) {
                    continue;
                }

                $take = min($quantity, $current);
                $newRemaining = $current - $take;

                $this->db->table($this->table)
                    ->where('batch_id', $batch['batch_id'])
                    ->update(['remaining_quantity' => $newRemaining]);

                $quantity -= $take;
            }
        }
    }

    private function refill_main_if_below_critical(int $item_id): void
    {
        $item = $this->db->table('items')
            ->where('item_id', $item_id)
            ->get();

        if (!$item) {
            return;
        }

        $critical = 10;

        $mainRemaining = $this->remaining_for_item_location($item_id, 'main');
        if ($mainRemaining > $critical) {
            return;
        }

        $reserves = $this->db->table($this->table)
            ->where('item_id', $item_id)
            ->where('location', 'reserve')
            ->where('remaining_quantity', '>', 0)
            ->order_by('received_date', 'ASC')
            ->get_all();

        foreach ($reserves as $batch) {
            $this->db->table($this->table)
                ->where('batch_id', $batch['batch_id'])
                ->update(['location' => 'main']);

            $mainRemaining += (int) ($batch['remaining_quantity'] ?? 0);
            if ($mainRemaining > $critical) {
                break;
            }
        }
    }

    /**
     * Public wrapper to allow controllers to manually trigger a refill
     * from reserve to main when stock is low.
     */
    public function refill_main_from_reserve(int $item_id): void
    {
        $this->refill_main_if_below_critical($item_id);
    }

    /**
     * Aggregated stock per item per location.
     *
     * @return array
     */
    public function inventory_summary()
    {
        $sql = "
            SELECT
                i.item_id,
                i.item_name,
                i.category,
                i.critical_level,
                COALESCE(SUM(CASE WHEN b.location = 'main' THEN COALESCE(b.remaining_quantity, b.quantity) END), 0)   AS main_qty,
                COALESCE(SUM(CASE WHEN b.location = 'reserve' THEN COALESCE(b.remaining_quantity, b.quantity) END), 0) AS reserve_qty,
                COALESCE(MIN(b.batch_id), 0) AS batch_id
            FROM items i
            LEFT JOIN batches b ON b.item_id = i.item_id
            GROUP BY i.item_id, i.item_name, i.category, i.critical_level
            ORDER BY i.item_name ASC
        ";

        $stmt = $this->db->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


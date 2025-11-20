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
    public function paginate_with_items(string $q = '', ?int $per_page = null, ?int $page = null)
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

        $count_query = clone $query;
        $total = $count_query->select_count('b.batch_id', 'count')->get()['count'] ?? 0;

        $query->select('b.batch_id, b.batch_code, b.quantity, b.remaining_quantity, b.location, b.expiry_date, b.received_date, i.item_name, i.category');
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
        $result = $this->db->table($this->table)
            ->where('location', $location)
            ->select_sum('remaining_quantity', 'total')
            ->get();

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Aggregated stock per item per location.
     *
     * @return array
     */
    public function inventory_summary()
    {
        $sql = "
            SELECT i.item_id,
                   i.item_name,
                   COALESCE(SUM(CASE WHEN b.location = 'main' THEN b.remaining_quantity END), 0) AS main_qty,
                   COALESCE(SUM(CASE WHEN b.location = 'reserve' THEN b.remaining_quantity END), 0) AS reserve_qty
            FROM items i
            LEFT JOIN batches b ON b.item_id = i.item_id
            GROUP BY i.item_id, i.item_name
            ORDER BY i.item_name ASC
        ";

        $stmt = $this->db->raw($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


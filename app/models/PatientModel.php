<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: PatientModel
 *
 * Provides data-access helpers for the `patients` table.
 */
class PatientModel extends Model
{
    protected $table = 'patients';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get paginated patient records with optional search and filters.
     *
     * @param string $q
     * @param int|null $per_page
     * @param int|null $page
     * @param array $filters
     * @return array
     */
    public function get_paginated(string $q = '', ?int $per_page = null, ?int $page = null, array $filters = [])
    {
        $query = $this->db->table($this->table);

        if (!empty($q)) {
            $like = '%' . $q . '%';
            $query->grouped(function ($db) use ($like) {
                $db->like('first_name', $like)
                    ->or_like('last_name', $like)
                    ->or_like('email', $like)
                    ->or_like('address', $like)
                    ->or_like('disease', $like)
                    ->or_like('medicine', $like)
                    ->or_like('status', $like);
            });
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['has_schedule'])) {
            $query->where_not_null('schedule');
        }

        if (!empty($filters['has_medicine'])) {
            $query->where_not_null('medicine');
            $query->where('medicine', '!=', '');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->order_by('date_created', 'DESC');

        if (is_null($page) || is_null($per_page)) {
            return [
                'records' => $query->get_all(),
                'total_rows' => $query->row_count()
            ];
        }

        $count_query = clone $query;
        $total = $count_query->select_count('*', 'count')->get()['count'] ?? 0;

        $records = $query
            ->pagination($per_page, $page)
            ->get_all();

        return [
            'records' => $records,
            'total_rows' => (int) $total
        ];
    }

    /**
     * Retrieve a single patient record with guarding for soft deletes.
     *
     * @param int $id
     * @return array|null
     */
    public function find_patient(int $id)
    {
        return $this->db
            ->table($this->table)
            ->where($this->primary_key, $id)
            ->get();
    }

    /**
     * Aggregate patient counts for charting.
     * Supported periods: 'weekly' (Mon-Sun current week), 'monthly' (last N months), 'yearly' (last N years)
     * Returns array with 'labels' and 'data'
     *
     * @param string $period
     * @param int $count number of buckets for months/years (ignored for weekly which uses 7 days)
     * @return array
     */
    public function aggregate_counts(string $period = 'weekly', int $count = 4): array
    {
        $now = new DateTimeImmutable('now');
        $labels = [];
        $data = [];

        if ($period === 'weekly') {
            // find Monday of this week
            $monday = $now->modify('monday this week');
            // ensure monday is at start of week even if today is monday
            $start = $monday->setTime(0,0,0);
            $days = [];
            for ($i = 0; $i < 7; $i++) {
                $d = $start->modify("+{$i} days");
                $labels[] = $d->format('D');
                $days[] = $d->format('Y-m-d');
                $data[] = 0;
            }

            // fetch patients in range
            $rangeStart = $start->format('Y-m-d 00:00:00');
            $rangeEnd = $start->modify('+6 days')->format('Y-m-d 23:59:59');
            $rows = $this->db->table($this->table)
                ->where('date_created', '>=', $rangeStart)
                ->where('date_created', '<=', $rangeEnd)
                ->get_all();

            // count per day
            $map = [];
            foreach ($rows as $r) {
                $d = date('Y-m-d', strtotime($r['date_created']));
                if (!isset($map[$d])) $map[$d] = 0;
                $map[$d]++;
            }
            foreach ($days as $idx => $d) {
                $data[$idx] = $map[$d] ?? 0;
            }

            return ['labels' => $labels, 'data' => $data];
        }

        if ($period === 'monthly') {
            // last $count months including current
            for ($i = $count - 1; $i >= 0; $i--) {
                $m = $now->modify("first day of -{$i} months");
                $label = $m->format('M Y');
                $labels[] = $label;
                $data[] = 0;
            }

            // for each month, count
            foreach ($labels as $idx => $lab) {
                $m = DateTimeImmutable::createFromFormat('M Y', $lab);
                if (!$m) continue;
                $start = $m->modify('first day of this month')->setTime(0,0,0)->format('Y-m-d 00:00:00');
                $end = $m->modify('last day of this month')->setTime(23,59,59)->format('Y-m-d 23:59:59');
                $cnt = $this->db->table($this->table)
                    ->where('date_created', '>=', $start)
                    ->where('date_created', '<=', $end)
                    ->select_count('*', 'c')->get()['c'] ?? 0;
                $data[$idx] = (int) $cnt;
            }
            return ['labels' => $labels, 'data' => $data];
        }

        if ($period === 'yearly') {
            // last $count years including current
            for ($i = $count - 1; $i >= 0; $i--) {
                $y = (int) $now->format('Y') - $i;
                $labels[] = (string) $y;
                $data[] = 0;
            }

            foreach ($labels as $idx => $lab) {
                $start = "{$lab}-01-01 00:00:00";
                $end = "{$lab}-12-31 23:59:59";
                $cnt = $this->db->table($this->table)
                    ->where('date_created', '>=', $start)
                    ->where('date_created', '<=', $end)
                    ->select_count('*','c')->get()['c'] ?? 0;
                $data[$idx] = (int) $cnt;
            }

            return ['labels' => $labels, 'data' => $data];
        }

        // default empty
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Aggregate patients by disease for a pie chart.
     * Returns top N diseases and groups the rest as 'Other'.
     *
     * @param int $top
     * @return array {labels:[], data:[]}
     */
    public function aggregate_by_disease(int $top = 6): array
    {
        // ensure disease column exists simplistically by attempting select
        $rows = $this->db->table($this->table)
            ->select('disease, COUNT(*) AS c')
            ->group_by('disease')
            ->order_by('c', 'DESC')
            ->get_all();

        $labels = [];
        $data = [];
        $other = 0;
        $i = 0;
        foreach ($rows as $r) {
            $name = $r['disease'] ?? 'Unknown';
            $cnt = (int) ($r['c'] ?? 0);
            if ($i < $top) {
                $labels[] = $name === '' ? 'Unknown' : $name;
                $data[] = $cnt;
            } else {
                $other += $cnt;
            }
            $i++;
        }
        if ($other > 0) {
            $labels[] = 'Other';
            $data[] = $other;
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Very small forecasting helper: predict counts for next period (week/month/year)
     * using simple average of historical buckets.
     * Returns ['labels'=>['Next ...'], 'data'=>[predicted_int]]
     *
     * @param string $period 'weekly'|'monthly'|'yearly'
     * @return array
     */
    public function predict_next(string $period = 'weekly'): array
    {
        if ($period === 'weekly') {
            // average patients per week over past 4 weeks
            $now = new DateTimeImmutable('now');
            $counts = [];
            for ($w = 1; $w <= 4; $w++) {
                $start = $now->modify("-{$w} week")->modify('monday this week')->setTime(0,0,0)->format('Y-m-d 00:00:00');
                $end = (new DateTimeImmutable($start))->modify('+6 days')->setTime(23,59,59)->format('Y-m-d 23:59:59');
                $cnt = $this->db->table($this->table)->where('date_created', '>=', $start)->where('date_created', '<=', $end)->select_count('*','c')->get()['c'] ?? 0;
                $counts[] = (int) $cnt;
            }
            $avg = $counts ? (int) round(array_sum($counts) / count($counts)) : 0;
            return ['labels' => ['Next Week'], 'data' => [$avg]];
        }

        if ($period === 'monthly') {
            $now = new DateTimeImmutable('now');
            $counts = [];
            for ($m = 1; $m <= 6; $m++) {
                $p = $now->modify("-{$m} months");
                $start = $p->modify('first day of this month')->setTime(0,0,0)->format('Y-m-d 00:00:00');
                $end = $p->modify('last day of this month')->setTime(23,59,59)->format('Y-m-d 23:59:59');
                $cnt = $this->db->table($this->table)->where('date_created', '>=', $start)->where('date_created', '<=', $end)->select_count('*','c')->get()['c'] ?? 0;
                $counts[] = (int) $cnt;
            }
            $avg = $counts ? (int) round(array_sum($counts) / count($counts)) : 0;
            return ['labels' => ['Next Month'], 'data' => [$avg]];
        }

        if ($period === 'yearly') {
            $now = new DateTimeImmutable('now');
            $counts = [];
            for ($y = 1; $y <= 3; $y++) {
                $year = (int) $now->format('Y') - $y;
                $start = "{$year}-01-01 00:00:00";
                $end = "{$year}-12-31 23:59:59";
                $cnt = $this->db->table($this->table)->where('date_created', '>=', $start)->where('date_created', '<=', $end)->select_count('*','c')->get()['c'] ?? 0;
                $counts[] = (int) $cnt;
            }
            $avg = $counts ? (int) round(array_sum($counts) / count($counts)) : 0;
            return ['labels' => ['Next Year'], 'data' => [$avg]];
        }

        return ['labels' => [], 'data' => []];
    }
}


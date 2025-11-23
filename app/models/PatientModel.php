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
     * Get patients depending on role.
     *
     * @param string $role 'admin' or 'user'
     * @param int|null $user_id Required if role is 'user'
     * @param string $q Optional search query
     * @return array
     */
    public function get_by_role(string $role, ?int $user_id = null, string $q = ''): array
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

        if ($role === 'user' && $user_id) {
            $query->where('user_id', $user_id);
        }

        $query->order_by('date_created', 'DESC');
        return $query->get_all();
    }

    /**
     * Get paginated patient records with optional search and filters.
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

        if (isset($filters['type'])) $query->where('type', $filters['type']);
        if (!empty($filters['has_schedule'])) $query->where_not_null('schedule');
        if (!empty($filters['has_medicine'])) {
            $query->where_not_null('medicine');
            $query->where('medicine', '!=', '');
        }
        if (!empty($filters['status'])) $query->where('status', $filters['status']);

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
     * Retrieve a single patient record.
     */
    public function find_patient(int $id)
    {
        return $this->db->table($this->table)
            ->where($this->primary_key, $id)
            ->get();
    }

    /**
     * Aggregate patient counts for charting.
     */
    public function aggregate_counts(string $period = 'weekly', int $count = 4): array
    {
        $now = new DateTimeImmutable('now');
        $labels = [];
        $data = [];

        if ($period === 'weekly') {
            $start = $now->modify('monday this week')->setTime(0,0,0);
            $days = [];
            for ($i=0;$i<7;$i++){
                $d = $start->modify("+{$i} days");
                $labels[] = $d->format('D');
                $days[] = $d->format('Y-m-d');
                $data[] = 0;
            }

            $rangeStart = $start->format('Y-m-d 00:00:00');
            $rangeEnd = $start->modify('+6 days')->format('Y-m-d 23:59:59');

            $rows = $this->db->table($this->table)
                ->where('date_created', '>=', $rangeStart)
                ->where('date_created', '<=', $rangeEnd)
                ->get_all();

            $map = [];
            foreach ($rows as $r) {
                $d = date('Y-m-d', strtotime($r['date_created']));
                if (!isset($map[$d])) $map[$d] = 0;
                $map[$d]++;
            }

            foreach ($days as $idx=>$d) $data[$idx] = $map[$d] ?? 0;
            return ['labels'=>$labels,'data'=>$data];
        }

        if ($period==='monthly') {
            for($i=$count-1;$i>=0;$i--){
                $m = $now->modify("first day of -{$i} months");
                $label = $m->format('M Y');
                $labels[] = $label;
                $data[] = 0;
            }
            foreach($labels as $idx=>$lab){
                $m = DateTimeImmutable::createFromFormat('M Y',$lab);
                if(!$m) continue;
                $start = $m->modify('first day of this month')->setTime(0,0,0)->format('Y-m-d 00:00:00');
                $end = $m->modify('last day of this month')->setTime(23,59,59)->format('Y-m-d 23:59:59');
                $cnt = $this->db->table($this->table)->where('date_created','>=',$start)->where('date_created','<=',$end)->select_count('*','c')->get()['c'] ?? 0;
                $data[$idx] = (int)$cnt;
            }
            return ['labels'=>$labels,'data'=>$data];
        }

        if ($period==='yearly') {
            for($i=$count-1;$i>=0;$i--){
                $y = (int)$now->format('Y')-$i;
                $labels[] = (string)$y;
                $data[] = 0;
            }
            foreach($labels as $idx=>$lab){
                $start = "{$lab}-01-01 00:00:00";
                $end = "{$lab}-12-31 23:59:59";
                $cnt = $this->db->table($this->table)->where('date_created','>=',$start)->where('date_created','<=',$end)->select_count('*','c')->get()['c'] ?? 0;
                $data[$idx] = (int)$cnt;
            }
            return ['labels'=>$labels,'data'=>$data];
        }

        return ['labels'=>$labels,'data'=>$data];
    }

    /**
     * Aggregate patients by disease.
     */
    public function aggregate_by_disease(int $top = 6): array
    {
        $rows = $this->db->table($this->table)
            ->select('disease, COUNT(*) AS c')
            ->group_by('disease')
            ->order_by('c','DESC')
            ->get_all();

        $labels=[]; $data=[]; $other=0; $i=0;
        foreach($rows as $r){
            $name = $r['disease'] ?? 'Unknown';
            $cnt = (int)($r['c'] ?? 0);
            if($i<$top){ $labels[] = $name===''?'Unknown':$name; $data[]=$cnt; }
            else $other += $cnt;
            $i++;
        }
        if($other>0){ $labels[]='Other'; $data[]=$other; }
        return ['labels'=>$labels,'data'=>$data];
    }

    /**
     * Predict next period patient count
     */
    public function predict_next(string $period='weekly'): array
    {
        $now = new DateTimeImmutable('now');
        $counts = [];

        if ($period==='weekly') {
            for($w=1;$w<=4;$w++){
                $start = $now->modify("-{$w} week")->modify('monday this week')->setTime(0,0,0)->format('Y-m-d 00:00:00');
                $end = (new DateTimeImmutable($start))->modify('+6 days')->setTime(23,59,59)->format('Y-m-d 23:59:59');
                $cnt = $this->db->table($this->table)->where('date_created','>=',$start)->where('date_created','<=',$end)->select_count('*','c')->get()['c'] ?? 0;
                $counts[] = (int)$cnt;
            }
            $avg = $counts? (int)round(array_sum($counts)/count($counts)) : 0;
            return ['labels'=>['Next Week'],'data'=>[$avg]];
        }

        if ($period==='monthly') {
            for($m=1;$m<=6;$m++){
                $p = $now->modify("-{$m} months");
                $start = $p->modify('first day of this month')->setTime(0,0,0)->format('Y-m-d 00:00:00');
                $end = $p->modify('last day of this month')->setTime(23,59,59)->format('Y-m-d 23:59:59');
                $cnt = $this->db->table($this->table)->where('date_created','>=',$start)->where('date_created','<=',$end)->select_count('*','c')->get()['c'] ?? 0;
                $counts[] = (int)$cnt;
            }
            $avg = $counts? (int)round(array_sum($counts)/count($counts)) : 0;
            return ['labels'=>['Next Month'],'data'=>[$avg]];
        }

        if ($period==='yearly') {
            for($y=1;$y<=3;$y++){
                $year = (int)$now->format('Y')-$y;
                $start = "{$year}-01-01 00:00:00";
                $end = "{$year}-12-31 23:59:59";
                $cnt = $this->db->table($this->table)->where('date_created','>=',$start)->where('date_created','<=',$end)->select_count('*','c')->get()['c'] ?? 0;
                $counts[] = (int)$cnt;
            }
            $avg = $counts? (int)round(array_sum($counts)/count($counts)) : 0;
            return ['labels'=>['Next Year'],'data'=>[$avg]];
        }

        return ['labels'=>[],'data'=>[]];
    }

    public function count_appointments_on_date(string $date, ?int $exclude_id=null): int
    {
        $query = $this->db->table($this->table)
            ->where('schedule','LIKE',$date.'%')
            ->where_not_null('schedule');
        if($exclude_id!==null) $query->where('id','!=',$exclude_id);
        $result = $query->select_count('*','count')->get();
        return (int)($result['count'] ?? 0);
    }

    public function exceeds_daily_limit(string $date,int $max_per_day=5, ?int $exclude_id=null): bool
    {
        return $this->count_appointments_on_date($date,$exclude_id) >= $max_per_day;
    }

    public function get_appointments_by_date(string $date): array
    {
        return $this->db->table($this->table)
            ->select('id, first_name, last_name, schedule, type, status')
            ->where('schedule','LIKE',$date.'%')
            ->where_not_null('schedule')
            ->order_by('schedule','ASC')
            ->get_all();
    }

    public function get_available_slots(string $date): array
    {
        $all_slots=[];
        for($hour=8;$hour<=17;$hour++) $all_slots[]=sprintf('%02d:00',$hour);

        $booked = $this->db->table($this->table)->select('schedule')->where('schedule','LIKE',$date.'%')->where_not_null('schedule')->get_all();
        $booked_times=[];
        foreach($booked as $apt){
            if(!empty($apt['schedule']) && strpos($apt['schedule'],' ')!==false){
                $parts = explode(' ',$apt['schedule']);
                $booked_times[] = substr($parts[1] ?? '',0,5);
            }
        }

        return array_values(array_diff($all_slots,$booked_times));
    }

    public function has_time_conflict(string $date,string $time, ?int $exclude_id=null): bool
    {
        try{
            $query = $this->db->table($this->table)->where('schedule','LIKE',$date.'%')->where_not_null('schedule');
            if($exclude_id!==null) $query->where('id','!=',$exclude_id);

            $appointments = $query->get_all();
            if(empty($appointments)) return false;

            list($req_hour,$req_min) = explode(':',$time);
            $req_minutes = (int)$req_hour*60 + (int)$req_min;

            foreach($appointments as $apt){
                if(!empty($apt['schedule']) && strpos($apt['schedule'],' ')!==false){
                    $parts = explode(' ',$apt['schedule']);
                    list($apt_hour,$apt_min) = explode(':', substr($parts[1],0,5));
                    $apt_minutes = (int)$apt_hour*60 + (int)$apt_min;
                    if(abs($req_minutes-$apt_minutes)<30) return true;
                }
            }

            return false;
        }catch(Exception $e){ return false; }
    }
  
        public function get_upcoming_by_user(int $user_id): array
        {
            $now = date('Y-m-d H:i:s');
            return $this->db->table($this->table)
                ->where('user_id', $user_id)
                ->where('schedule', '>=', $now)
                ->order_by('schedule', 'ASC')
                ->get_all();
        }


        public function get_past_by_user(int $user_id): array
        {
            $now = date('Y-m-d H:i:s');
            return $this->db->table($this->table)
                ->where('user_id', $user_id)
                ->where('schedule', '<', $now)
                ->order_by('schedule', 'DESC')
                ->get_all();
        }
}

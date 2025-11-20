<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: Model
 * 
 * Automatically generated via CLI.
 */
class Models extends Model {
    protected $table = 'patients';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }
    public function page($q, $records_per_page = null, $page = null) {
            if (is_null($page)) {
                return $this->db->table($this->table)->all();
            } 
            else {
                $query = $this->db->table($this->table);
               if (!empty($q)) {
                $query->like('id', '%'.$q.'%')
                      ->or_like('First_Name', '%'.$q.'%')
                      ->or_like('Last_Name', '%'.$q.'%')
                      ->or_like('Age', '%'.$q.'%')
                      ->or_like('Email', '%'.$q.'%')
                      ->or_like('Address', '%'.$q.'%')
                      ->or_like('Disease', '%'.$q.'%');
         }
            $query->order_by('id', 'ASC');

                // Clone before pagination
                $countQuery = clone $query;

                $data['total_rows'] = $countQuery->select_count('*', 'count')->get()['count'];

                $data['records'] = $query->pagination($records_per_page, $page)->get_all();
                return $data;
            }
            
        }
}
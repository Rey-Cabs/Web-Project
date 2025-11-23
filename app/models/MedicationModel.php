<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class MedicationModel extends Model
{
    protected $table = 'medications';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get active medications for a specific user
     */
    public function get_active_by_user(int $user_id): array
    {
        $now = date('Y-m-d');
        return $this->db->table($this->table)
            ->where('user_id', $user_id)
            ->where('end_date', '>=', $now)
            ->order_by('start_date', 'ASC')
            ->get_all();
    }

    /**
     * Get completed medications for a specific user
     */
    public function get_completed_by_user(int $user_id): array
    {
        $now = date('Y-m-d');
        return $this->db->table($this->table)
            ->where('user_id', $user_id)
            ->where('end_date', '<', $now)
            ->order_by('end_date', 'DESC')
            ->get_all();
    }

    /**
     * Optionally, get all medications for admin view
     */
    public function get_all_by_role(string $role, ?int $user_id = null): array
    {
        $query = $this->db->table($this->table);
        if ($role === 'user' && $user_id) {
            $query->where('user_id', $user_id);
        }
        $query->order_by('start_date', 'DESC');
        return $query->get_all();
    }

    /**
     * Get all medications for a specific patient (used in records page)
     */
    public function get_by_patient(int $patientId): array
    {
        return $this->db->table($this->table)
            ->where('user_id', $patientId)
            ->order_by('start_date', 'DESC')
            ->get_all();
    }
}

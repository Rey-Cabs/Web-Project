<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: UserModel
 *
 * Handles CRUD operations for the `users` table.
 */
class UserModel extends Model
{
    protected $table = 'users';
    protected $primary_key = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Find user by email address.
     *
     * @param string $email
     * @return array|null
     */
    public function find_by_email(string $email)
    {
        return $this->db
            ->table($this->table)
            ->where('email', $email)
            ->get();
    }

    /**
     * Retrieve all users ordered by latest created.
     *
     * @return array
     */
    public function all_users()
    {
        return $this->db
            ->table($this->table)
            ->order_by('created_at', 'ASC')
            ->get_all();
    }
}


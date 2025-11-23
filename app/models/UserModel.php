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

    /**
     * Find user by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function find_by_id(int $id)
    {
        return $this->db
            ->table($this->table)
            ->where('id', $id)
            ->get();
    }

    /**
     * Update user information.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_user(int $id, array $data)
    {
        return $this->db
            ->table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Delete user by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete_user(int $id)
    {
        return $this->db
            ->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    /**
     * Get all users excluding the provided user ID.
     *
     * @param int $exclude_id
     * @return array
     */
    public function all_users_except(int $exclude_id)
    {
        return $this->db
            ->table($this->table)
            ->where('id', '!=', $exclude_id)
            ->order_by('created_at', 'DESC')
            ->get_all();
    }
}


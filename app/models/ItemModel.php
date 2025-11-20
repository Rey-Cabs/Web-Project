<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: ItemModel
 *
 * Handles CRUD for inventory items.
 */
class ItemModel extends Model
{
    protected $table = 'items';
    protected $primary_key = 'item_id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve all items ordered alphabetically.
     *
     * @return array
     */
    public function all_items()
    {
        return $this->db
            ->table($this->table)
            ->order_by('item_name', 'ASC')
            ->get_all();
    }
}


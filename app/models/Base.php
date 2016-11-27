<?php
namespace app\models;
use Kotori\Core\Model;

class Base extends Model
{
    public function __construct()
    {
        parent::__construct();
        define('DEFAULT_CATEGORY_ID', 1);
    }

    /**
     * @access public
     * @param [string] $table --The table name.
     * @param [string] $column --The target column will be calculated.
     * @param (optional)[array] $where --The WHERE clause to filter records.
     * @return [int] --The maximum number of the column.
     */
    public function max($table, $column, $where = [])
    {
        if (empty($table) || empty($column) || !is_array($where)) {
             return -1;
        }
        if (!empty($where)) {
            return $this->db->max($table, $column, $where);
        }
        return $this->db->max($table, $column);
    }

    /**
     * @access public
     * @param [string] $table --The table name.
     * @param [array] $where --The WHERE clause to filter records.
     * @return [boolean] --True of False if the target data has been founded.
     */
    public function has($table, $where)
    {
        if (empty($table) || empty($where) || !is_array($where)) {
            return -1;
        }
        return $this->db->has($table, $where);
    }

    /**
     * @access public
     * @param [string] $table --The table name.
     * @param [array] $data --The data that will be inserted into table.
     * @return [int] --The last insert id.
     */
    public function insert($table, $data)
    {
        if (empty($table) || !is_array($data)) {
            return -1;
        }
        return $this->db->insert($table, $data);
    }

    /**
     * @access public
     * @param [string] $table --The table name.
     * @param [array] $data --The data that will be modified.
     * @param (optional)[array] --The WHERE clause to filter records.
     * @return [int] The number of rows affected.
     */
    public function update($table, $data, $where = [])
    {
        if (empty($table) || empty($data) || !is_array($data) || !is_array($where)) {
            return -1;
        }
        if (!empty($where)) {
            return $this->db->update($table, $data, $where);
        }
        return $this->db->update($table, $data);
    }

    /**
     * @access public
     * @param [string] $table --The table name.
     * @param [array] $columns --The target columns of data will be fetched.
     * @param (optional)[array] --The WHERE clause to filter records.
     * @return [array]
     */
    public function select($table, $columns, $where = [])
    {
        if (empty($table) || empty($columns) || !is_array($columns) || !is_array($where)) {
            return -1;
        }
        if (!empty($where)) {
            return $this->db->select($table, $columns, $where);
        }
        return $this->db->select($table, $columns);
    }
}

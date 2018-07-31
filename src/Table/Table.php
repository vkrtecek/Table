<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 31.7.2018
 * Time: 20:24
 */

namespace Vkrtecek\Table;

/**
 * Class Table
 * @package Vkrtecek\Table
 */
class Table
{
    /**
     * Rows of table
     *
     * @var array
     */
    private $rows = [];

    /**
     * Table constructor.
     * Instance class by static method create
     */
    private function __construct() {}

    /**
     * @param array|NULL $data
     * @return Table
     */
    public static function create(array $data = NULL): Table {
        return new self;
    }

    /**
     * @param array $data
     * @return Table
     */
    public function insertData(array $data): Table {
        foreach ($data as $row) {
            $this->addRow($row);
        }
        return $this;
    }

    /**
     * @param object $row
     */
    public function addRow($row) {
        $this->rows[] = $row;
    }
}
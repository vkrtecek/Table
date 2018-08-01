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
    /** @var \Donquixote\Cellbrush\Table\Table */
    private $table;
    /** @var Column[] */
    private $cols = [];

    /**
     * Rows of table
     * @var array
     */
    private $rows = [];

    /**
     * Table constructor.
     * Instance class by static method create
     * @param array $data
     */
    private function __construct(array $data)
    {
        $this->table = \Donquixote\Cellbrush\Table\Table::create();
        $this->insertData($data);
    }

    /**
     * @param array|NULL $data
     * @return Table
     */
    public static function create(array $data = NULL): Table
    {
        return new self($data ? $data : []);
    }

    /**
     * @param string $col
     * @return Column
     */
    public function addColumn($col): Column
    {
        $column = (new Column($this, $col));
        $this->cols[$col] = $column;
        return $column;
    }




    /**
     * @param array $data
     * @return Table
     */
    public function insertData(array $data): Table
    {
        foreach ($data as $row) {
            $this->addRow($row);
        }
        return $this;
    }

    /**
     * @param object $row
     * @return Table
     */
    public function addRow($row): Table
    {
        $this->rows[] = $row;
        return $this;
    }

    /**
     * @return string HTML output
     * @throws \Exception
     */
    public function renderHTML(): string
    {
        foreach ($this->cols as $col) {
            $this->table->addColName($col->getName());
        }

        for ($i = 0; $i < count($this->rows); $i++) {
            $this->table->addRowName('row_' . $i);
            foreach ($this->cols as $col)
                $this->table->td('row_' . $i, $col->getName(), $col->getContent($this->rows[$i])/*$this->rows[$i]->{$col->getter()}()*/);
        }

        //add HEAD
        $this->table->thead()->addRowName('_head');
        foreach ($this->cols as $col)
            $this->table->thead()->th('_head', $col->getName(), $col->getName());
        $this->table->addRowStriping();

        return $this->table->render();
    }




    /**
     * @return string
     */
    public function renderCSS(): string {
        return '<style type="text/css"></style>';
    }
}
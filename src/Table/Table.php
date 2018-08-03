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
    /** @var Column[] */
    private $cols = [];
	/** @var @var Html */
    private $htmlTable;
    /** @var bool */
    private $listing = false;

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
        $this->insertData($data);
        $this->htmlTable = new Html($this);
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
     * @return Table
     */
    public function enableListing(): Table {
        $this->listing = true;
        return $this;
    }

    /**
     * @param array $config = array(
     *      'css' => [bool]
     * );
     * @return string HTML output
     * @throws \Exception
     */
    public function renderHTML(array $config = []): string
    {
        $table = \Donquixote\Cellbrush\Table\Table::create();

        //add HEAD
        $table->thead()->addRowName('_head');
        foreach ($this->cols as $col)
            $table->thead()->th('_head', $col->getName(), $this->htmlTable->printHeadForCol($col));

        $table->tbody();

        //add cols
        foreach ($this->cols as $col) {
            $table->addColName($col->getName())
                ->addColClass($col->getName(), $col->getClass());
        }

        //add rows
		$i = 0;
        $rows = $this->htmlTable->filterRows($this->rows, $this->cols);
        $rows = $this->htmlTable->getRowsFromPage($rows);
        foreach ($rows as $row) {
            $table->addRowName('row_' . $i);
            foreach ($this->cols as $col)
                $table->td('row_' . $i, $col->getName(), $col->getContent($row));
            ++$i;
        }

        
        $table->addRowStriping();

        return '<div id="users-table">' .
                (isset($config['css']) && $config['css'] ? '<style type="text/css">' . $this->renderCSS() . '</style>' : '') .
                $this->printNavigation() .
                $table->render() .
                $this->printListing() .
            '</div>';
    }



    /**
     * @return string
     */
    private function printNavigation(): string {
        return $this->htmlTable->getNavigation($this->cols);
    }

    /**
     * @return string
     */
    private function printListing(): string {
        $rows = $this->htmlTable->filterRows($this->rows, $this->cols);
        return $this->htmlTable->getListing($rows);
    }

    /**
     * @return string
     */
    public function renderCSS(): string {
        return $this->htmlTable->getCss();
    }

	/**
	 * $data = [
	 * 		'limit' => 'limit',
	 * 		'orderBy' => 'order_by',
	 * 		'order' => 'order',
	 * 		'pattern' = > 'pattern',
	 * 		'page' => 'page',
	 * 		'url' => 'url'
	 * ];
	 *
	 * @param array $data
	 * @return Table
	 */
    public function setNavigationNames(array $data): Table {
		if (isset($data['limit']) && isset($data['limit']) != "")
			$this->htmlTable->setLimit($data['limit']);

		if (isset($data['page']) && isset($data['page']) != "")
			$this->htmlTable->setPage($data['page']);

		if (isset($data['orderBy']) && isset($data['orderBy']) != "")
			$this->htmlTable->setOrderBy($data['orderBy']);

		if (isset($data['order']) && isset($data['order']) != "")
			$this->htmlTable->setOrder($data['order']);

		if (isset($data['pattern']) && isset($data['pattern']) != "")
			$this->htmlTable->setPattern($data['pattern']);

		if (isset($data['url']) && isset($data['url']) != "")
			$this->htmlTable->setUrl($data['url']);

		return $this;
	}

    /**
     * @return bool
     */
	public function hasListing(): bool {
        return $this->listing;
    }
}
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
    /** @var int */
    private $itemsCount = null;
    /** @var bool */
    private $statusBarItems = NULL;

    /**
     * Rows of table
     * @var array
     */
    private $rows = [];

    /**
     * Table constructor.
     * Instance class by static method create
     * @param array $data
     * @param int $showingRows default value for how many rows will be rendered
     * @param int $showStatusBar adds <div> with information about number of listed items like 1 - 15 of 24
     */
    private function __construct(array $data, int $showingRows, $showStatusBar = NULL)
    {
        $this->insertData($data);
        $this->htmlTable = new Html($this, $showingRows);
        $this->statusBarItems = $showStatusBar;
    }

    /**
     * @param array|NULL $data
     * @param int $showingRows default value for how many rows will be rendered
     * @param int $showStatusBarItemsCnt (1 - 20 of 243)
     * @return Table
     */
    public static function create(array $data = [], int $showingRows = 15, $showStatusBarItemsCnt = NULL): Table {
        return new self($data, $showingRows, $showStatusBarItemsCnt);
    }

    /**
     * @param string $col
     * @param string|NULL $alias
     * @return Column
     */
    public function addColumn(string $col, string $alias = NULL): Column
    {
        $column = (new Column($this, $col, $alias));
        $this->cols[$alias ?: $col] = $column;
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
     * render input to specify how many rows will appear
     * @return Table
     */
    public function enableListing(): Table {
        $this->listing = true;
        return $this;
    }

    /**
     * @param array $config = array(
     *      'css' => [bool],
     *      'defaultOrder' => [string],
     *      'defaultOrderBy' => [string],
     * );
     * @return string HTML output
     * @throws \Exception
     */
    public function renderHTML(array $config = []): string
    {
        if (isset($config['defaultOrder'])) $this->htmlTable->setDefaultOrder($config['defaultOrder']);
        if (isset($config['defaultOrderBy'])) $this->htmlTable->setDefaultOrderBy($config['defaultOrderBy']);

        $table = \Donquixote\Cellbrush\Table\Table::create();

        //add HEAD
        $table->thead()->addRowName('_head');
        $someColumnSearch = FALSE;
        foreach ($this->cols as $col) {
            $table->thead()->th('_head', $col->getName(), $this->htmlTable->printHeadForCol($col, $this->cols));
            if ($col->isSoloSearchable() || $col->isDateFromToSearchable()) {
                $someColumnSearch = TRUE;
            }
        }
        //if some column is solo or dateFromTo searchable add thead row
        if ($someColumnSearch) {
            $table->thead()->addRow('_head_searching');
            $table->thead()->addRowClass('_head_searching', '_navigation_row');
            foreach ($this->cols as $col) {
                if ($col->isSoloSearchable()) {
                    $table->thead()->th('_head_searching', $col->getName(), $this->htmlTable->generateSoloSearchCell($col));
                } else if ($col->isDateFromToSearchable()) {
                    $table->thead()->th('_head_searching', $col->getName(), $this->htmlTable->generateDateFromToSearchCell($col));
                } else {
                    $table->thead()->th('_head_searching', $col->getName(), '');
                }
            }
        }

        $table->tbody();

        //add cols
        foreach ($this->cols as $col) {
            $table->addColName($col->getName())
                ->addColClass($col->getName(), $col->getClass());
        }

        //add rows
		$i = 0;
        if ($this->itemsCount === null) {
            //itemsCount is not specified so library must filter rows
            $rows = $this->htmlTable->filterRows($this->rows, $this->cols);
            $rows = $this->htmlTable->getRowsFromPage($rows);
        } else {
            $rows = $this->rows;
        }
        foreach ($rows as $row) {
            $table->addRowName('row_' . $i);
            foreach ($this->cols as $col)
                $table->td('row_' . $i, $col->getName(), $col->getContent($row));
            ++$i;
        }

        
        $table->addRowStriping();

        return '<div id="users-table">' .
                (isset($config['css']) && $config['css'] ? '<style type="text/css">' . $this->renderCSS() . '</style>' : '') .
                $this->printNavigation($someColumnSearch) .
                $this->htmlTable->getStartOfPostForm() .
                $table->render() .
                $this->htmlTable->getEndOfPostForm() .
                $this->printListing() .
                $this->htmlTable->printScripts() .
            '</div>';
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

    /**
     * @param int $number
     * @return Table
     */
    public function setTotalItemCount(int $number): Table {
        $this->itemsCount = $number;
	    return $this;
    }



    /**
     * @param bool $someColumnSearch prints button for jQuery hide/show <tr> with navigation
     * @return string
     */
    private function printNavigation(bool $someColumnSearch = FALSE): string {
        return $this->htmlTable->getNavigation($this->cols, $someColumnSearch);
    }

    /**
     * @return string
     */
    private function printListing(): string {
        //filter rows only if is necessary and count from it number of pages
        $rows = $this->itemsCount === null
            ? $this->htmlTable->filterRows($this->rows, $this->cols)
            : $this->rows;
        $this->htmlTable->setItemsCnt($this->itemsCount ?? count($rows));
        return ($this->statusBarItems > 0 ? $this->htmlTable->getStatusBar($this->statusBarItems) : '') . $this->htmlTable->getListing($this->cols, $rows, $this->itemsCount);
    }
}
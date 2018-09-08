<?php
/**
 * Created by PhpStorm.
 * User: vkrte_000
 * Date: 3. 8. 2018
 * Time: 8:56
 */

namespace Vkrtecek\Table;


class Html
{
    /** @var Column */
    private $sortingCol;
    const PHP_EOL = "\n";
    const UNHIDDEN_LISTING_NAVIGATION_PAGES = 7;

	private $table;
	private $limit = 'limit';
	private $page = 'page';
	private $orderBy = 'sort_by';
	private $order = 'sort';
	private $pattern = 'pattern';
	private $url;
	/** @var int default number of rows */
	private $defaultLimit;

    /** @var int|null total items in table */
    private $itemsCnt = NULL;

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

	const DEFAULT_PAGE = 1;
	private $defaultOrderBy = '';
	private $defaultOrder = self::ORDER_ASC;
	/** @var string $workingOrder DESC|ASC */
	private $workingOrder;
	const DEFAULT_PATTERN = '';

	//translations
	const DEFAULT_TR = [
	    'SEARCH_BY' => 'Search by',
        'SHOW_BUTTON' => 'Show navigation row',
        'HIDE_BUTTON' => 'Hide navigation row',
        'FROM' => 'From',
        'TO' => 'To',
        'PATTERN' => 'pattern',
        'OF' => 'of',
    ];
	private $tr_Search_by;
    private $tr_Show_navigation_row;
    private $tr_Hide_navigation_row;
    private $tr_From;
    private $tr_To;
    private $tr_pattern;
    private $tr_of;


    /**
     * Html constructor.
     * @param Table $table
     * @param int $showingRows
     */
	public function __construct(Table $table, int $showingRows) {
		$this->table = $table;
		$this->defaultLimit = $showingRows > 0 ? $showingRows : 15;
		$this->url = 'http://' . $_SERVER['HTTP_HOST'];

		//translations
        $this->tr_Search_by = self::DEFAULT_TR['SEARCH_BY'];
        $this->tr_Show_navigation_row = self::DEFAULT_TR['SHOW_BUTTON'];
        $this->tr_Hide_navigation_row = self::DEFAULT_TR['HIDE_BUTTON'];
        $this->tr_From = self::DEFAULT_TR['FROM'];
        $this->tr_To = self::DEFAULT_TR['TO'];
        $this->tr_pattern = self::DEFAULT_TR['PATTERN'];
        $this->tr_of = self::DEFAULT_TR['OF'];
	}

    /**
     * @param array $tr
     * @return Html
     */
	public function setTranslations(array $tr): Html {
        $this->tr_Search_by = $tr['Search by'] ?? self::DEFAULT_TR['SEARCH_BY'];
        $this->tr_Show_navigation_row = $tr['Show navigation row'] ?? self::DEFAULT_TR['SHOW_BUTTON'];
        $this->tr_Hide_navigation_row = $tr['Hide navigation row'] ?? self::DEFAULT_TR['HIDE_BUTTON'];
        $this->tr_From = $tr['From'] ?? self::DEFAULT_TR['FROM'];
        $this->tr_To = $tr['To'] ?? self::DEFAULT_TR['TO'];
        $this->tr_pattern = $tr['pattern'] ?? self::DEFAULT_TR['PATTERN'];
        $this->tr_of = $tr['of'] ?? self::DEFAULT_TR['OF'];
	    return $this;
    }

	/**
     * @param bool $searchShowButton
	 * @param Column[] $columns
	 * @return string
	 */
	public function getNavigation(array $columns, bool $searchShowButton): string
	{
		$placeholder = $this->tr_Search_by;
		$_c = [];
		foreach ($columns as $column)
			if ($column->isSearchable())
				$_c[] = $column;
		for ($i = 0; $i < count($_c); $i++) {
			$column = $_c[$i];
			if ($i != 0 && $i != count($_c) - 1) $placeholder .= ',';
			$placeholder .= $i == count($_c) - 1 ? ($i != 0 ? ' and ' : ' ') . $column->getName() : ' ' . $column->getName();
		}
		//add inputs for additional attributes witch are searchable
        $inputsForAdditionalAttributes = $this->getInputsForAdditionalAttributes($columns);

		$patternInputType = $placeholder == $this->tr_Search_by ? 'hidden' : 'text';
		$limitInputType = $this->table->hasListing() ? 'number' : 'hidden';
		return self::PHP_EOL .
            '  <div id="table-navigation-forms">' . self::PHP_EOL .
            '    <form method="GET" action="' . $this->url . '" id="input-limit-pattern-form">' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->orderBy . '" value="' . $this->getOrderBy() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->order . '" value="' . $this->getOrder() . '" />' . self::PHP_EOL .
            '      <input type="' . $limitInputType . '" step="1" name="' . $this->limit . '" id="limit" value="' . $this->getLimit() . '">' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->page . '" value="' . $this->getPage() . '" />' . self::PHP_EOL .
            '      <input type="' . $patternInputType . '" name="' . $this->pattern . '" value="' . $this->getPattern() . '" id="pattern" placeholder="' . $placeholder . '" />' . self::PHP_EOL .
            '       <input type="hidden" name="pre_order_by" value="' . ($_GET[$this->orderBy] ?? $this->defaultOrderBy) . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="q" value="false" />' . self::PHP_EOL .
            '      <button hidden="hidden"></button>' . self::PHP_EOL .
            $inputsForAdditionalAttributes .
            '    </form>' . self::PHP_EOL .
            '  </div>' . self::PHP_EOL .

            ($searchShowButton
                ? self::PHP_EOL . '<button id="show_navigation" onclick="toggleNavigationRow(\'show\')">' . $this->tr_Show_navigation_row . '</button>' . self::PHP_EOL .
                    '<button id="hide_navigation" onclick="toggleNavigationRow(\'hide\')">' . $this->tr_Hide_navigation_row . '</button>' . self::PHP_EOL
                : ''
            ) . self::PHP_EOL;
	}

    /**
     * print javascript
     * @return string
     */
	public function printScripts(): string {
	    return '<script type="text/javascript">' . self::PHP_EOL .
            "    document.getElementById('hide_navigation').style.display = 'none';" . self::PHP_EOL .
            "    document.getElementsByClassName('_navigation_row')[0].style.display = 'none';" . self::PHP_EOL .
            "    function toggleNavigationRow(action) {" . self::PHP_EOL .
            "        var id = action === 'show' ? 'show_navigation' : 'hide_navigation';" . self::PHP_EOL .
            "        var show_id = action !== 'show' ? 'show_navigation' : 'hide_navigation';" . self::PHP_EOL .
            "        document.getElementById(id).style.display = 'none';" . self::PHP_EOL .
            "        document.getElementById(show_id).style.display = '';" . self::PHP_EOL .
            "        if (action === 'show') {" . self::PHP_EOL .
            "            document.getElementsByClassName('_navigation_row')[0].style.display = '';" . self::PHP_EOL .
            "        } else {" . self::PHP_EOL .
            "            document.getElementsByClassName('_navigation_row')[0].style.display = 'none';" . self::PHP_EOL .
            "        }" . self::PHP_EOL .
            "    }" . self::PHP_EOL .
            '</script>' . self::PHP_EOL;
    }

    /**
     * @param object[] $rows
     * @param Column[] $cols
     * @return object[]
     */
    public function filterRows(array $rows, array $cols): array
    {
        $this->setItemsCnt(count($rows));
        $result = [];
        //filter by pattern
        if (isset($_GET[$this->pattern]) && $_GET[$this->pattern] != '') {
            foreach ($rows as $row)
                foreach ($cols as $col)
                    //if entity contain in searchable column pattern, add to result
                    if ($col->isSearchable() && $this->patternMatch($this->getPattern(), $col->getContent($row)))
                        $result[] = $row;
        } else {
            $result = $rows;
        }
        //filter by additional attributes
        foreach ($cols as $col) {
            if ($col->isSoloSearchable() && $col->getSoloSearchableVal()) {
                //filter rows by this column string
                foreach ($result as $key => $row) {
                    if (!$this->patternMatch($col->getSoloSearchableVal(), $col->getContent($row)))
                        unset($result[$key]);
                }

            } else if ($col->isDateFromToSearchable()) {
                //filter rows by this column between dates
                //get only older results
                if ($col->getDateFromToSearchableVal('from')) {
                    foreach ($result as $key => $row) {
                        $a = new \DateTime($col->getContent($row));
                        $b = new \DateTime($col->getDateFromToSearchableVal('from'));
                        if ($a <= $b)
                            unset($result[$key]);
                    }
                }
                //get only younger results
                if ($col->getDateFromToSearchableVal('to')) {
                    foreach ($result as $key => $row) {
                        $a = new \DateTime($col->getContent($row));
                        $b = new \DateTime($col->getDateFromToSearchableVal('to'));
                        if ($a >= $b)
                            unset($result[$key]);
                    }
                }
            }
        }


        //sort by order_by if is specified
        if (isset($_GET[$this->orderBy]) && $_GET[$this->orderBy] != '') {
            $this->sortingCol = $cols[$_GET[$this->orderBy]];

            usort($result, function ($a, $b) {
              return
                  call_user_func($this->sortingCol->getOrderableFunction()
                      ?: function ($a, $b) {
                      return ($this->sortingCol->getContent($a) > $this->sortingCol->getContent($b) ? 1 : -1);
                    }, $a, $b)
                  * ($this->getOrder() == $this->defaultOrder ? -1 : 1);
            });
        }
        return $result;
    }

    /**
     * returns array of items for specific page
     * @param object[] $rows
     * @return object[]
     */
    public function getRowsFromPage(array $rows) {
        $ret = [];
        $offset = $this->getLimit() * ($this->getPage() - 1);
        $limit = min($this->getLimit(), count($rows) - $offset);
        //only wanted page
        foreach ($rows as $row) {
            //skip first x items
            if ($offset-- > 0)
                continue;
            //get only y items
            if ($limit-- == 0)
                break;
            $ret[] = $row;
        }

	    return $ret;
    }

    /**
     * @param Column[] $cols
     * @param object[] $rows
     * @param int|null $itemsCnt
     * @return string
     */
	public function getListing(array $cols, array $rows, int $itemsCnt = null): string {
	    $ret = '<div id="listing">' . self::PHP_EOL;
	    $listing = '';
        $itemsCnt = $itemsCnt ? $itemsCnt : count($rows);

        $lastPage = ceil($itemsCnt / $this->getLimit());
        $from = (int)$this->getPage() - ceil(self::UNHIDDEN_LISTING_NAVIGATION_PAGES / 2);
        $to = (int)$this->getPage() + ceil(self::UNHIDDEN_LISTING_NAVIGATION_PAGES / 2);

        $from_page = max(1, $from);
        $to_page = min($lastPage, $to);

        //first page always
        if ($from_page != 1) {
            $listing .= '<a href="' . $this->url .
                '?' . $this->orderBy . '=' . $this->getOrderBy() .
                '&' . $this->order . '=' . $this->getOrder() .
                '&' . $this->limit . '=' . $this->getLimit() .
                '&' . $this->page . '=' . 1 .
                '&' . $this->pattern . '=' . $this->getPattern() .
                '&pre_order_by=' . ($_GET[$this->orderBy] ?? $this->defaultOrderBy) .
                $this->getAHREFForAdditionalAttributes($cols) .
                '&q=false' . '">' .
                '    <span class="listing-page">' . 1 . '</span>' .
                '</a> . . . ' . self::PHP_EOL;
        }

	    for ($i = $from_page; $i <= $to_page; $i++) {
	        if ($i == $this->getPage())
	            $listing .=  '<span class="listing-page" id="listing-selected">' . $i . '</span>' . self::PHP_EOL;
	        else
                $listing .= '<a href="' . $this->url .
                    '?' . $this->orderBy . '=' . $this->getOrderBy() .
                    '&' . $this->order . '=' . $this->getOrder() .
                    '&' . $this->limit . '=' . $this->getLimit() .
                    '&' . $this->page . '=' . $i .
                    '&' . $this->pattern . '=' . $this->getPattern() .
                    '&pre_order_by=' . ($_GET[$this->orderBy] ?? $this->defaultOrderBy) .
                    $this->getAHREFForAdditionalAttributes($cols) .
                    '&q=false' . '">' .
                    '    <span class="listing-page">' . $i . '</span>' .
				    '</a>' . self::PHP_EOL;
        }

        //last page always
        if ($to_page != $lastPage) {
            $listing .= ' . . . <a href="' . $this->url .
                '?' . $this->orderBy . '=' . $this->getOrderBy() .
                '&' . $this->order . '=' . $this->getOrder() .
                '&' . $this->limit . '=' . $this->getLimit() .
                '&' . $this->page . '=' . $lastPage .
                '&' . $this->pattern . '=' . $this->getPattern() .
                '&pre_order_by=' . ($_GET[$this->orderBy] ?? $this->defaultOrderBy) .
                $this->getAHREFForAdditionalAttributes($cols) .
                '&q=false' . '">' .
                '    <span class="listing-page">' . $lastPage . '</span>' .
                '</a>' . self::PHP_EOL;
        }
	    return $ret . ($i == 2 ? '' : $listing) . '</div>' . self::PHP_EOL;
	}

    /**
     * @param int $totalItems
     * @return string
     */
	public function getStatusBar(int $totalItems): string {
	    $firstItem = $this->getLimit() * ($this->getPage() - 1) + 1;
	    $lastItem = min($this->getLimit() * $this->getPage(), $totalItems);
	    return
            '<div id="statusBar">' . self::PHP_EOL .
                $firstItem . ' - ' . $lastItem . ' ' . $this->tr_of . ' ' . $totalItems . self::PHP_EOL .
            '</div>' . self::PHP_EOL;
    }

    /**
     * @param Column $col
     * @param Column[] $cols
     * @return string
     */
	public function printHeadForCol(Column $col, array $cols): string {
	    return $col->isOrderable()
            ? '<a href="' . $this->url .
                '?' . $this->orderBy . '=' . ($col->getAlias() ?: $col->getName()) .
                '&' . $this->order . '=' . $this->getOrder() .
                '&' . $this->limit . '=' . $this->getLimit() .
                '&' . $this->page . '=' . $this->getPage() .
                '&' . $this->pattern . '=' . $this->getPattern() .
                '&pre_order_by=' . ($_GET[$this->orderBy] ?? $this->defaultOrderBy) .
                $this->getAHREFForAdditionalAttributes($cols) .
                '">' . $col->getName() . '</a>'
            : $col->getName();
    }

	/**
	 * @param string $limit
	 * @return Html
	 */
	public function setLimit(string $limit): Html
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @param string $page
	 * @return Html
	 */
	public function setPage(string $page): Html
	{
		$this->page = $page;
		return $this;
	}

	/**
	 * @param string $orderBy
	 * @return Html
	 */
	public function setOrderBy(string $orderBy): Html
	{
		$this->orderBy = $orderBy;
		return $this;
	}

	/**
	 * @param string $order
	 * @return Html
	 */
	public function setOrder(string $order): Html
	{
		$this->order = $order;
		return $this;
	}

	/**
	 * @param string $pattern
	 * @return Html
	 */
	public function setPattern(string $pattern): Html
	{
		$this->pattern = $pattern;
		return $this;
	}

	/**
	 * @param string $url
	 * @return Html
	 */
	public function setUrl(string $url): Html
	{
		$this->url = $url;
		return $this;
	}

    /**
     * @return string
     */
	public function getCss(): string {
		return file_get_contents(__DIR__ . '/../css/table.css');
	}


	/**
	 * @return int
	 */
	public function getLimit(): int {
        return isset($_GET[$this->limit]) && $_GET[$this->limit] > 0
            ? $_GET[$this->limit]
            : $this->defaultLimit;
	}

	/**
	 * @return string
	 */
	public function getPage(): string {
	    $page = $_GET[$this->page] ?? self::DEFAULT_PAGE;
		if ($this->itemsCnt === NULL)
		    return $page;
		else //if user is on nth page and filter, so no item will be on current nth page ---> set page to first
		    return (($page-1) * $this->getLimit() + 1 >= $this->itemsCnt) ? 1 : $page;
	}

	/**
	 * @return string
	 */
	public function getOrderBy(): string {
        return $_GET[$this->orderBy] ?? $this->defaultOrderBy;
	}

	/**
	 * @return string
	 */
	private function getOrder(): string {
	    //get default value
        if (!isset($_GET[$this->order])) {
            return strtoupper($this->defaultOrder) == self::ORDER_ASC
                ? self::ORDER_DESC
                : self::ORDER_ASC;
        }

        //order was already setted
        if (isset($this->workingOrder))
            return $this->workingOrder;

		return isset($_GET['pre_order_by'])
            ? $_GET['pre_order_by'] === $this->getOrderBy()
                ? $this->switchOrder()
                : $_GET[$this->order]
			: $this->defaultOrder;
	}

	/**
	 * @return string
	 */
	public function getPattern(): string {
        return $_GET[$this->pattern] ?? self::DEFAULT_PATTERN;
	}

    /**
     * generate two inputs for searching between two dates
     * @param Column $col
     * @return string
     */
    public function generateDateFromToSearchCell(Column $col): string {
        return
            '      <label for="date_from">' . $this->tr_From . ': </label>' . self::PHP_EOL .
            '      <input type="datetime-local" name="' . $col->getDateFromToSearchable()['from'] . '" value="' . $col->getDateFromToSearchableVal('from') . '" id="date_from" />' . self::PHP_EOL .
            '      <br />' . self::PHP_EOL .
            '      <label for="date_to">' . $this->tr_To . ': </label>' . self::PHP_EOL .
            '      <input type="datetime-local" name="' . $col->getDateFromToSearchable()['to'] . '" value="' . $col->getDateFromToSearchableVal('to') . '" id="date_to" />' . self::PHP_EOL;
    }

    /**
     * generates input for searching above one column by pattern
     * @param Column $col
     * @return string
     */
    public function generateSoloSearchCell(Column $col): string {
        return '      <input type="text" name="' . $col->getSoloSearchable() . '" value="' . $col->getSoloSearchableVal() . '" placeholder="' . $this->tr_pattern . '" />' . self::PHP_EOL;
    }

    /**
     * generate start of searching form
     * @return string
     */
    public function getStartOfPostForm(): string {
        return '<form method="GET" action="' . $this->url . '">' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->orderBy . '" value="' . $this->getOrderBy() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->order . '" value="' . $this->getOrder() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->limit . '" value="' . $this->getLimit() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->page . '" value="' . $this->getPage() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->pattern . '" value="' . $this->getPattern() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="pre_order_by" value="' . ($_GET[$this->orderBy] ?? $this->defaultOrderBy) . '" />' . self::PHP_EOL;
    }

    /**
     * generate ond of searching form
     * @return string
     */
    public function getEndOfPostForm(): string {
        return
            '      <button hidden="hidden"></button>' . self::PHP_EOL .
            '</form>' . self::PHP_EOL;
    }

    /**
     * @param int $limit
     * @return Html
     */
    public function setShowingRowsNumber(int $limit): Html {
        $this->defaultLimit = $limit;
        return $this;
    }

    /**
     * @param int $cnt
     * @return Html
     */
    public function setItemsCnt(int $cnt): Html {
        $this->itemsCnt = $cnt;
        return $this;
    }

    /**
     * @param string $value
     * @return Html
     */
    public function setDefaultOrder(string $value): Html {
        $this->defaultOrder = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return Html
     */
    public function setDefaultOrderBy(string $value): Html {
        $this->defaultOrderBy = $value;
        return $this;
    }

    /** @var bool $switchedOrder */
    private $switchedOrder = FALSE;
    /**
     * switch from DESC to ASC and back
     * @return string
     */
    private function switchOrder(): string {
        if (!isset($_GET[$this->order]))
            return self::ORDER_DESC;

        if (isset($_GET['q']))
            return $_GET[$this->order];

        if ($this->switchedOrder) {
            return $_GET[$this->order];
        }
        $this->switchedOrder = TRUE;

        $this->workingOrder = $_GET[$this->order] == self::ORDER_ASC
            ? self::ORDER_DESC
            : self::ORDER_ASC;
        return $this->workingOrder;
    }

    /**
     * @param string $pattern
     * @param string $content
     * @return bool
     */
    private function patternMatch(string $pattern, string $content): bool {
        return strpos(strtolower($content), strtolower($pattern)) !== false;
    }

    /**
     * generates hidden inputs with right values for additional attributes
     * @param Column[] $cols
     * @param Column $skip column witch will not be added
     * @return string
     */
    private function getInputsForAdditionalAttributes(array $cols, Column $skip = NULL): string {
        $inputsForAdditionalAttributes = '';
        foreach ($cols as $col) {
            if ($col == $skip)
                continue;
            if ($col->isSoloSearchable()) {
                $inputsForAdditionalAttributes .= '      <input type="hidden" name="' . $col->getSoloSearchable() . '" value="' . $col->getSoloSearchableVal() . '" />' . self::PHP_EOL;
            } else if ($col->isDateFromToSearchable()) {
                $inputsForAdditionalAttributes .= '      <input type="hidden" name="' . $col->getDateFromToSearchable()['from'] . '" value="' . $col->getDateFromToSearchableVal('from') . '" />' . self::PHP_EOL;
                $inputsForAdditionalAttributes .= '      <input type="hidden" name="' . $col->getDateFromToSearchable()['to'] . '" value="' . $col->getDateFromToSearchableVal('to') . '" />' . self::PHP_EOL;
            }
        }
        return $inputsForAdditionalAttributes;
    }

    /**
     * generates part of URL with right values for additional attributes
     * @param Column[] $cols
     * @param Column $skip column witch will not be added
     * @return string
     */
    private function getAHREFForAdditionalAttributes(array $cols, Column $skip = NULL): string {
        $URLPart = '';
        foreach ($cols as $col) {
            if ($col == $skip)
                continue;
            if ($col->isSoloSearchable()) {
                $URLPart .= '&' . $col->getSoloSearchable() . '=' . $col->getSoloSearchableVal();
            } else if ($col->isDateFromToSearchable()) {
                $URLPart .= '&' . $col->getDateFromToSearchable()['from'] . '=' . $col->getDateFromToSearchableVal('from');
                $URLPart .= '&' . $col->getDateFromToSearchable()['to'] . '=' . $col->getDateFromToSearchableVal('to');
            }
        }
        return $URLPart;
    }
}
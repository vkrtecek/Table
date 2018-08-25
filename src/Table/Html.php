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

	private $table;
	private $limit = 'limit';
	private $page = 'page';
	private $orderBy = 'sort_by';
	private $order = 'sort';
	private $pattern = 'pattern';
	private $url;
	private $defaultLimit;

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

	const DEFAULT_PAGE = 1;
	const DEFAULT_ORDER_BY = '';
	const DEFAULT_ORDER = self::ORDER_ASC;
	const DEFAULT_PATTERN = '';

    /**
     * Html constructor.
     * @param Table $table
     * @param int $showingRows
     */
	public function __construct(Table $table, int $showingRows) {
		$this->table = $table;
		$this->defaultLimit = $showingRows > 0 ? $showingRows : 15;
		$this->url = 'http://' . $_SERVER['HTTP_HOST'];
	}

	/**
     * @param bool $searchShowButton
	 * @param Column[] $columns
	 * @return string
	 */
	public function getNavigation(array $columns, bool $searchShowButton): string
	{
		$placeholder = 'Search by';
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

		$patternInputType = $placeholder == 'Search by' ? 'hidden' : 'text';
		$limitInputType = $this->table->hasListing() ? 'number' : 'hidden';
		return self::PHP_EOL .
            '  <div id="table-navigation-forms">' . self::PHP_EOL .
            '    <form method="GET" action="' . $this->url . '" id="input-limit-pattern-form">' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->orderBy . '" value="' . $this->getOrderBy() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->order . '" value="' . $this->getOrder() . '" />' . self::PHP_EOL .
            '      <input type="' . $limitInputType . '" step="1" name="' . $this->limit . '" id="limit" value="' . $this->getLimit() . '">' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->page . '" value="' . $this->getPage() . '" />' . self::PHP_EOL .
            '      <input type="' . $patternInputType . '" name="' . $this->pattern . '" value="' . $this->getPattern() . '" id="pattern" placeholder="' . $placeholder . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="q" value="false" />' . self::PHP_EOL .
            '      <button hidden="hidden"></button>' . self::PHP_EOL .
            $inputsForAdditionalAttributes .
            '    </form>' . self::PHP_EOL .
            '  </div>' . self::PHP_EOL .

            ($searchShowButton
                ? self::PHP_EOL . '<button id="show_navigation" onclick="toggleNavigationRow(\'show\')">Show navigation row</button>' . self::PHP_EOL .
                    '<button id="hide_navigation" onclick="toggleNavigationRow(\'hide\')">Hide navigation row</button>' . self::PHP_EOL
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
                  * ($this->getOrder() == self::DEFAULT_ORDER ? -1 : 1);
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
	    for ($i = 1; $i <= ceil($itemsCnt / $this->getLimit()); $i++) {
	        if ($i == $this->getPage())
	            $listing .=  '<span class="listing-page" id="listing-selected">' . $i . '</span>' . self::PHP_EOL;
	        else
                $listing .= '<a href="' . $this->url .
                    '?' . $this->orderBy . '=' . $this->getOrderBy() .
                    '&' . $this->order . '=' . $this->getOrder() .
                    '&' . $this->limit . '=' . $this->getLimit() .
                    '&' . $this->page . '=' . $i .
                    '&' . $this->pattern . '=' . $this->getPattern() .
                    $this->getAHREFForAdditionalAttributes($cols) .
                    '&q=false' . '">' .
                    '    <span class="listing-page">' . $i . '</span>' .
				    '</a>' . self::PHP_EOL;
        }
	    return $ret . ($i == 2 ? '' : $listing) . '</div>' . self::PHP_EOL;
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
		return $_GET[$this->page] ?? self::DEFAULT_PAGE;
	}

	/**
	 * @return string
	 */
	public function getOrderBy(): string {
        return $_GET[$this->orderBy] ?? self::DEFAULT_ORDER_BY;
	}

	/**
	 * @return string
	 */
	public function getOrder(): string {
		return isset($_GET[$this->order])
            ? isset($_GET[$this->orderBy]) && $_GET[$this->orderBy] != ''
                ? $this->switchOrder()
                : self::DEFAULT_ORDER
			: self::DEFAULT_ORDER;
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
     * @param Column[] $columns
     * @return string
     */
    public function generateDateFromToSearchCell(Column $col, array $columns): string {
        $form = '<form method="GET" action="' . $this->url . '">' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->orderBy . '" value="' . $this->getOrderBy() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->order . '" value="' . $this->getOrder() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->limit . '" value="' . $this->getLimit() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->page . '" value="' . $this->getPage() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->pattern . '" value="' . $this->getPattern() . '" />' . self::PHP_EOL .
            '      <label for="date_from">From: </label>' . self::PHP_EOL .
            '      <input type="datetime-local" name="' . $col->getDateFromToSearchable()['from'] . '" value="' . $col->getDateFromToSearchableVal('from') . '" id="date_from" />' . self::PHP_EOL .
            $this->getInputsForAdditionalAttributes($columns, $col) .
            '      <br />' . self::PHP_EOL .
            '      <label for="date_to">To: </label>' . self::PHP_EOL .
            '      <input type="datetime-local" name="' . $col->getDateFromToSearchable()['to'] . '" value="' . $col->getDateFromToSearchableVal('to') . '" id="date_to" />' . self::PHP_EOL .
            '      <button hidden="hidden"></button>' . self::PHP_EOL .
        '</form>' . self::PHP_EOL;
        return $form;
    }

    /**
     * generates input for searching above one column by pattern
     * @param Column $col
     * @param Column[] $columns
     * @return string
     */
    public function generateSoloSearchCell(Column $col, array $columns): string {
        $form = '<form method="GET" action="' . $this->url . '">' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->orderBy . '" value="' . $this->getOrderBy() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->order . '" value="' . $this->getOrder() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->limit . '" value="' . $this->getLimit() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->page . '" value="' . $this->getPage() . '" />' . self::PHP_EOL .
            '      <input type="hidden" name="' . $this->pattern . '" value="' . $this->getPattern() . '" />' . self::PHP_EOL .
            '      <input type="text" name="' . $col->getSoloSearchable() . '" value="' . $col->getSoloSearchableVal() . '" placeholder="pattern" />' . self::PHP_EOL .
            $this->getInputsForAdditionalAttributes($columns, $col) .
            '</form>' .  self::PHP_EOL;
        return $form;
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
     * switch from DESC to ASC and back
     * @return string
     */
    private function switchOrder(): string {
        if (!isset($_GET[$this->order]))
            return self::ORDER_DESC;

        if (isset($_GET['q']))
            return $_GET[$this->order];

        return $_GET[$this->order] == self::ORDER_ASC
            ? self::ORDER_DESC
            : self::ORDER_ASC;
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
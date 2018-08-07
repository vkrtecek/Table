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

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

	const DEFAULT_LIMIT = '15';
	const DEFAULT_PAGE = 1;
	const DEFAULT_ORDER_BY = '';
	const DEFAULT_ORDER = self::ORDER_ASC;
	const DEFAULT_PATTERN = '';

	public function __construct(Table $table) {
		$this->table = $table;
		$this->url = 'http://' . $_SERVER['HTTP_HOST'];
	}

	/**
	 * @var Column[] $columns
	 * @return string
	 */
	public function getNavigation(array $columns): string
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
            '    </form>' . self::PHP_EOL .
            '  </div>' . self::PHP_EOL;
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


        //sort by order_by if is specified
        if (isset($_GET[$this->orderBy]) && $_GET[$this->orderBy] != '') {
            $this->sortingCol = $cols[$_GET[$this->orderBy]];

            $sortingFunction = $this->sortingCol->getOrderableFunction() ?: function ($a, $b) {
                return ($this->sortingCol->getContent($a) > $this->sortingCol->getContent($b) ? 1 : -1) * ($this->getOrder() == self::DEFAULT_ORDER ? -1 : 1);
            };
            usort($result, $sortingFunction);
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
        //only wanted page
        for ($i = $this->getLimit() * ($this->getPage() - 1); $i < min($this->getLimit() * $this->getPage(), count($rows)); $i++)
            $ret[] = $rows[$i];

	    return $ret;
    }

    /**
     * @param object[] $rows
     * @param int|null $itemsCnt
     * @return string
     */
	public function getListing(array $rows, int $itemsCnt = null): string {
	    $ret = '<div id="listing">';
	    $listing = '';
        $itemsCnt = $itemsCnt ? $itemsCnt : count($rows);
	    for ($i = 1; $i <= ceil($itemsCnt / $this->getLimit()); $i++) {
	        if ($i == $this->getPage())
	            $listing .=  '<span class="listing-page" id="listing-selected">' . $i . '</span>';
	        else
                $listing .= '<a href="' . $this->url .
                    '?' . $this->orderBy . '=' . $this->getOrderBy() .
                    '&' . $this->order . '=' . $this->getOrder() .
                    '&' . $this->limit . '=' . $this->getLimit() .
                    '&' . $this->page . '=' . $i .
                    '&' . $this->pattern . '=' . $this->getPattern() .
                    '&q=false' . '">
                        <span class="listing-page">' . $i . '</span>
				    </a>';
        }
	    return $ret . ($i == 2 ? '' : $listing) . '</div>';
	}

    /**
     * @param Column $col
     * @return string
     */
	public function printHeadForCol(Column $col): string {
	    return $col->isOrderable()
            ? '<a href="' . $this->url .
                '?' . $this->orderBy . '=' . $col->getName() .
                '&' . $this->order . '=' . $this->getOrder() .
                '&' . $this->limit . '=' . $this->getLimit() .
                '&' . $this->page . '=' . $this->getPage() .
                '&' . $this->pattern . '=' . $this->getPattern() .
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
	 * @return string
	 */
	public function getLimit(): string {
		return isset($_GET[$this->limit])
			? $_GET[$this->limit]
			: self::DEFAULT_LIMIT;
	}

	/**
	 * @return string
	 */
	public function getPage(): string {
		return isset($_GET[$this->page])
			? $_GET[$this->page]
			: self::DEFAULT_PAGE;
	}

	/**
	 * @return string
	 */
	public function getOrderBy(): string {
		return isset($_GET[$this->orderBy])
			? $_GET[$this->orderBy]
			: self::DEFAULT_ORDER_BY;
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
		return isset($_GET[$this->pattern])
			? $_GET[$this->pattern]
			: self::DEFAULT_PATTERN;
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
}
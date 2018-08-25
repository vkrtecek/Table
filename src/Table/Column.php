<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 1.8.2018
 * Time: 18:01
 */

namespace Vkrtecek\Table;


class Column
{
    /** @var Table */
    private $table;
    /** @var string */
    private $name;
    /** @var string */
    private $alias;
    /** @var string */
    private $property = NULL;
    /** @var string */
    private $html_class = '';
    /** @var callable|null */
    private $func = NULL;
	/** @var bool */
	private $searchable = false;
	/** @var bool */
	private $orderable = false;
	/** @var callable */
	private $orderFunction = null;
	/** @var string */
	private $soloSearchable = null;
	/** @var array */
	private $dateFromToSearchable = null;

    /**
     * Column constructor.
     * @param Table $table
     * @param string $name
     * @param string|NULL $alias
     */
    public function __construct(Table $table, string $name, string $alias = NULL) {
        $this->table = $table;
        $this->name = $name;
        $this->alias = $alias;
    }

    /** @return string */
    public function getName(): string {
        return $this->name;
    }

    /** @return string|NULL */
    public function getAlias() {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getProperty(): string {
        return $this->property;
    }
    /**
     * @param string $prop
     * @return Table
     * @deprecated
     */
    public function setProperty(string $prop): Table {
        $this->property = $prop;
        return $this->table;
    }

    /**
     * @param \Closure|string $content
     * @return Table
     */
    public function setContent($content): Table {
        if ($content instanceof \Closure)
            $this->func = $content;
        else if (is_string($content))
            $this->property = $content;
        return $this->table;
    }

    /**
    * @return string
    */
    public function getClass(): string {
        return $this->html_class;
    }
    /**
     * @var string $className
     * @return Column
     */
    public function setClass(string $className): Column {
        $this->html_class = $className;
        return $this;
    }

    /**
     * @param mixed $entity
     * @return string
     */
    public function getContent($entity): string {
        if ($this->func)
            return call_user_func($this->func, $entity);
        return $entity->{$this->getter()}();
    }

	/**
	 * @return Column
	 */
	public function setSearchable(): Column {
		$this->searchable = true;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSearchable(): bool {
		return $this->searchable;
	}

	/**
     * @param callable|null $function
	 * @return Column
	 */
	public function setOrderable(callable $function = null): Column {
	    $this->orderFunction = $function;
		$this->orderable = true;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isOrderable(): bool {
		return $this->orderable;
	}

    /**
     * @return callable|null
     */
    public function getOrderableFunction() {
	    return $this->orderFunction;
    }

    /**
     * @return bool
     */
    public function isDateFromToSearchable(): bool {
        return $this->dateFromToSearchable !== NULL;
    }
    /**
     * @return array|NULL
     */
    public function getDateFromToSearchable(): array {
        return $this->dateFromToSearchable;
    }
    /**
     * @param string $type from or to
     * @return string
     */
    public function getDateFromToSearchableVal(string $type): string {
        return $_GET[$this->getDateFromToSearchable()[$type]] ?? '';
    }
    /**
     * @param string $urlAttrFrom
     * @param string $urlAttrTo
     * @return Column
     */
    public function setDateFromToSearchable(string $urlAttrFrom, string $urlAttrTo): Column {
        $this->dateFromToSearchable = ['from' => $urlAttrFrom, 'to' => $urlAttrTo, ];
        return $this;
    }


    /**
     * @return bool
     */
    public function isSoloSearchable(): bool {
        return $this->soloSearchable !== NULL;
    }
    /**
     * @return string|NULL
     */
    public function getSoloSearchable(): string {
        return $this->soloSearchable;
    }
    /**
     * @return string
     */
    public function getSoloSearchableVal(): string {
        return $_GET[$this->getSoloSearchable()] ?? '';
    }
    /**
     * @param string $urlAttr
     * @return Column
     */
    public function setSoloSearchable(string $urlAttr): Column {
        $this->soloSearchable = $urlAttr;
        return $this;
    }

    /**
     * if property is age, so function returns getAge
     * @return string
     */
    private function getter(): string {
        return $this->property
            ? 'get' . ucfirst($this->property)
            : '';
    }
}
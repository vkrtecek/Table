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
    private $property = NULL;
    /** @var string */
    private $html_class = '';
    /** @var callable|null */
    private $func = NULL;
	/** @var bool */
	private $searchable = false;
	/** @var bool */
	private $orderable = false;

    /**
     * Column constructor.
     * @param Table $table
     * @param string $name
     */
    public function __construct(Table $table, string $name) {
        $this->table = $table;
        $this->name = $name;
    }

    /** @return string */
    public function getName(): string {
        return $this->name;
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
     */
    public function setProperty(string $prop): Table {
        $this->property = $prop;
        return $this->table;
    }

    /**
     * @param callable $function
     * @return Table
     */
    public function setContent(callable $function): Table {
        $this->func = $function;
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
     * if property is age, so function returns getAge
     * @return string
     */
    private function getter(): string {
        return $this->property
            ? 'get' . ucfirst($this->property)
            : '';
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
	 * @return Column
	 */
	public function setOrderable(): Column {
		$this->orderable = true;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isOrderable(): bool {
		return $this->orderable;
	}


}
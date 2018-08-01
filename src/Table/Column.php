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
    private $property;
    /** @var callable|null */
    private $func = NULL;

    /**
     * Column constructor.
     * @param Table $table
     * @param string $name
     */
    public function __construct(Table $table, $name) {
        $this->table = $table;
        $this->name = $name;
    }

    /** @return string */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $prop
     * @return Table
     */
    public function setProperty($prop): Table {
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
    public function getter(): string {
        return 'get' . ucfirst($this->property);
    }
}
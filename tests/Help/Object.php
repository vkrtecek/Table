<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 31.7.2018
 * Time: 21:36
 */

namespace Test\Help;


class Object
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $surname;
    /** @var int */
    private $age;
    /** @var string */
    private $language;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Object
     */
    public function setId(int $id): Object
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Object
     */
    public function setName(string $name): Object
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     * @return Object
     */
    public function setSurname(string $surname): Object
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     * @return Object
     */
    public function setAge(int $age): Object
    {
        $this->age = $age;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return Object
     */
    public function setLanguage(string $language): Object
    {
        $this->language = $language;
        return $this;
    }


}
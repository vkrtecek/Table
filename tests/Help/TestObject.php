<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 31.7.2018
 * Time: 21:36
 */

namespace Test\Help;


class TestObject
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
     * @param int $id
     * @param string $name
     * @param string $surname
     * @param int $age
     * @param string $language
     * @return TestObject
     */
    public static function create(int $id, string $name, string $surname, int $age, string $language): self {
        return (new self)->setId($id)->setName($name)->setSurname($surname)->setAge($age)->setLanguage($language);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return TestObject
     */
    public function setId(int $id): TestObject
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
     * @return TestObject
     */
    public function setName(string $name): TestObject
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
     * @return TestObject
     */
    public function setSurname(string $surname): TestObject
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
     * @return TestObject
     */
    public function setAge(int $age): TestObject
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
     * @return TestObject
     */
    public function setLanguage(string $language): TestObject
    {
        $this->language = $language;
        return $this;
    }


}
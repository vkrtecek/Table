<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 31.7.2018
 * Time: 21:19
 */

namespace Test;
use PHPUnit\Framework\TestCase;
use Test\Help\Object;
use Vkrtecek\Table\Table;

class CreateTableTest extends TestCase
{
    /** @var array */
    protected $data = [];

    protected function prepareData() {
        $this->data = [
            (new Object())->setId(1)->setName('John')->setSurname('Doe')->setAge(15)->setLanguage('CZK'),
            (new Object())->setId(1)->setName('John')->setSurname('Doe')->setAge(15)->setLanguage('CZK'),
            (new Object())->setId(1)->setName('John')->setSurname('Doe')->setAge(15)->setLanguage('CZK'),
        ];
    }

    public function testCreate() {
        $this->prepareData();
        $table = Table::create($this->data);
        $this->assertTrue($table instanceof Table);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 31.7.2018
 * Time: 21:19
 */

namespace Test;
use PHPUnit\Framework\TestCase;
use Test\Help\TestObject;
use Vkrtecek\Table\Table;

class CreateTableTest extends TestCase
{
    /** @var array */
    protected $data = [];
    const PHP_EOL = "\n";

    protected function prepareData() {
        $i = 0;
        $this->data = [
            (new TestObject())->setId(++$i)->setName('John')->setSurname('McCaul')->setAge(38)->setLanguage('ENG'),
            (new TestObject())->setId(++$i)->setName('Susane')->setSurname('McCaul')->setAge(35)->setLanguage('CZK'),
            (new TestObject())->setId(++$i)->setName('Paul')->setSurname('Smith')->setAge(13)->setLanguage('ENG'),
        ];
    }

    public function testCreate() {
        $this->prepareData();
        $table = Table::create($this->data);
        $this->assertTrue($table instanceof Table);
    }

    /**
     * @depends testCreate
     */
    public function testContent() {
        $this->prepareData();
        $table = Table::create($this->data)->addColumn('ID')->setContent(function(TestObject $obj) {
            return $obj->getId();
        })
            ->addColumn('Name')->setProperty('name')
            ->addColumn('Age')->setProperty('age');

        $expected =
'<table>' . self::PHP_EOL .
'  <thead>' . self::PHP_EOL .
'    <tr><th>ID</th><th>Name</th><th>Age</th></tr>' . self::PHP_EOL .
'  </thead>' . self::PHP_EOL .
'  <tbody>' . self::PHP_EOL .
'    <tr class="odd"><td>1</td><td>John</td><td>38</td></tr>' . self::PHP_EOL .
'    <tr class="even"><td>2</td><td>Susane</td><td>35</td></tr>' . self::PHP_EOL .
'    <tr class="odd"><td>3</td><td>Paul</td><td>13</td></tr>' . self::PHP_EOL .
'  </tbody>' . self::PHP_EOL .
'</table>' . self::PHP_EOL;
        
        $this->assertEquals($expected, $table->renderHTML());
    }
}
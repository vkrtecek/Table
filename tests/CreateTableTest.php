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
        $_SERVER['HTTP_HOST'] = 'localhost';
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
            '<div id="users-table">' . self::PHP_EOL .
            '  <div id="table-navigation-forms">' . self::PHP_EOL .
            '    <form method="GET" action="http://localhost" id="input-limit-pattern-form">' . self::PHP_EOL .
            '      <input type="hidden" name="sort_by" value="" />' . self::PHP_EOL .
            '      <input type="hidden" name="sort" value="ASC" />' . self::PHP_EOL .
            '      <input type="hidden" step="1" name="limit" id="limit" value="15">' . self::PHP_EOL .
            '      <input type="hidden" name="page" value="1" />' . self::PHP_EOL .
            '      <input type="hidden" name="pattern" value="" id="pattern" placeholder="Search by" />' . self::PHP_EOL .
            '      <input type="hidden" name="q" value="false" />' . self::PHP_EOL .
            '      <button hidden="hidden"></button>' . self::PHP_EOL .
            '    </form>' . self::PHP_EOL .
            '  </div>' . self::PHP_EOL .
            '<table>' . self::PHP_EOL .
            '  <thead>' . self::PHP_EOL .
            '    <tr><th class="">ID</th><th class="">Name</th><th class="">Age</th></tr>' . self::PHP_EOL .
            '  </thead>' . self::PHP_EOL .
            '  <tbody>' . self::PHP_EOL .
            '    <tr class="odd"><td class="">1</td><td class="">John</td><td class="">38</td></tr>' . self::PHP_EOL .
            '    <tr class="even"><td class="">2</td><td class="">Susane</td><td class="">35</td></tr>' . self::PHP_EOL .
            '    <tr class="odd"><td class="">3</td><td class="">Paul</td><td class="">13</td></tr>' . self::PHP_EOL .
            '  </tbody>' . self::PHP_EOL .
            '</table>' . self::PHP_EOL .
            '<div id="listing"></div></div>';
        
        $this->assertEquals($expected, $table->renderHTML());
    }
}
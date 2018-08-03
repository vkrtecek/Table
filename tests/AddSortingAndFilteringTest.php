<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 3.8.2018
 * Time: 23:20
 */

namespace Test;


use Vkrtecek\Table\Table;

class AddSortingAndFilteringTest extends CreateTableTest
{
    public function testAddSearchField()
    {
        $this->prepareData();
        $table = Table::create($this->data)
            ->addColumn('ID')->setProperty('id')
            ->addColumn('Name')->setSearchable()->setProperty('name')
            ->setNavigationNames([
                'limit' => 'počet_prvků',
                'orderBy' => 'řadit_podle',
                'order' => 'řadit',
                'page' => 'stránka',
                'pattern' => 'vzor',
                'url' => 'http://skeleton.com',
            ]);

        $expected =
            '<div id="users-table">' . self::PHP_EOL .
            '  <div id="table-navigation-forms">' . self::PHP_EOL .
            '    <form method="GET" action="http://skeleton.com" id="input-limit-pattern-form">' . self::PHP_EOL .
            '      <input type="hidden" name="řadit_podle" value="" />' . self::PHP_EOL .
            '      <input type="hidden" name="řadit" value="ASC" />' . self::PHP_EOL .
            '      <input type="hidden" step="1" name="počet_prvků" id="limit" value="15">' . self::PHP_EOL .
            '      <input type="hidden" name="stránka" value="1" />' . self::PHP_EOL .
            '      <input type="text" name="vzor" value="" id="pattern" placeholder="Search by Name" />' . self::PHP_EOL .
            '      <input type="hidden" name="q" value="false" />' . self::PHP_EOL .
            '      <button hidden="hidden"></button>' . self::PHP_EOL .
            '    </form>' . self::PHP_EOL .
            '  </div>' . self::PHP_EOL .
            '<table>' . self::PHP_EOL .
            '  <thead>' . self::PHP_EOL .
            '    <tr><th class="">ID</th><th class="">Name</th></tr>' . self::PHP_EOL .
            '  </thead>' . self::PHP_EOL .
            '  <tbody>' . self::PHP_EOL .
            '    <tr class="odd"><td class="">1</td><td class="">John</td></tr>' . self::PHP_EOL .
            '    <tr class="even"><td class="">2</td><td class="">Susane</td></tr>' . self::PHP_EOL .
            '    <tr class="odd"><td class="">3</td><td class="">Paul</td></tr>' . self::PHP_EOL .
            '  </tbody>' . self::PHP_EOL .
            '</table>' . self::PHP_EOL .
            '<div id="listing"></div></div>';

        $this->assertEquals($expected, $table->renderHTML());




        //add another searchable column
        $table->addColumn('Age')->setSearchable()->setProperty('age');

        $expected =
            '<div id="users-table">' . self::PHP_EOL .
            '  <div id="table-navigation-forms">' . self::PHP_EOL .
            '    <form method="GET" action="http://skeleton.com" id="input-limit-pattern-form">' . self::PHP_EOL .
            '      <input type="hidden" name="řadit_podle" value="" />' . self::PHP_EOL .
            '      <input type="hidden" name="řadit" value="ASC" />' . self::PHP_EOL .
            '      <input type="hidden" step="1" name="počet_prvků" id="limit" value="15">' . self::PHP_EOL .
            '      <input type="hidden" name="stránka" value="1" />' . self::PHP_EOL .
            '      <input type="text" name="vzor" value="" id="pattern" placeholder="Search by Name and Age" />' . self::PHP_EOL .
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
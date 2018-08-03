<?php
/**
 * Created by PhpStorm.
 * User: Krtek
 * Date: 3.8.2018
 * Time: 19:32
 */

namespace Test;


use Vkrtecek\Table\Table;

class ModifyURITest extends CreateTableTest
{
    public function testModifyURI() {
        $this->prepareData();
        $table = Table::create($this->data)
            ->addColumn('ID')->setProperty('id')
            ->addColumn('Name')->setProperty('name')
            ->addColumn('Age')->setSearchable()->setProperty('age')
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
            '      <input type="number" step="1" name="počet_prvků" id="limit" value="15">' . self::PHP_EOL .
            '      <input type="hidden" name="stránka" value="1" />' . self::PHP_EOL .
            '      <input type="text" name="vzor" value="" id="pattern" placeholder="Search by Age" />' . self::PHP_EOL .
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
            '<div id="listing"><span class="listing-page" id="listing-selected">1</span></div></div>';

        $this->assertEquals($expected, $table->renderHTML());
    }
}
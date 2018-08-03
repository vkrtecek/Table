# Easy HTML Table generator

<p>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

The library focuses on easy table rendering of arrays of objects. No connections to database, only insert array.

## License

The Table library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

## Instalation

```bash
composer require vkrtecek/table
```

## Examples

Let's say we have for all examples testing class like below:

```php
class TestObject
{
    private $id;
    private $name;
    private $age;
    
    public function __construct($id, $name, $age) {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
    }
    
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getAge() { return $this->age; }
}
```

### Basic usage

If we want to render table with collection of TestObject, do:

```php
$data = [
    new TestObject(1, 'John', 38),
    new TestObject(2, 'Susane', 35),
    new TestObject(3, 'Paul', 13),
    new TestObject(4, 'Joe', 25),
    new TestObject(5, 'Lucia', 80),
    new TestObject(6, 'Štěpán', 29),
];

$table = \Vkrtecek\Table\Table::create($data)
   ->addColumn('ID')->setProperty('id')
   ->addColumn('Name')->setProperty('name')
   ->addColumn('Age')->setProperty('age');
                
```

The string passed by ``` setProperty() ``` must be the same string as signature of property's getter without ```get```. So for example if the TestObject has method ```getAge()```, ```setProperty()``` must pass string ```'age'``` or ```'Age'```.

And in your blade.php call:

```php
<style>
    $table->renderCSS();
</style>
.
.
.
<body>
    $table->renderHTML();
</body>
```

or
```php
$table->renderHTML(['css' => true]);
```

so the result will look like 

<table>
  <thead>
    <tr><th>ID of person</th><th>Name</th><th>Age</th></tr>
  </thead>
  <tbody>
    <tr class="odd"><td>1</td><td>John</td><td>38</td></tr>
    <tr class="even"><td>2</td><td>Susane</td><td>35</td></tr>
    <tr class="odd"><td>3</td><td>Paul</td><td>13</td></tr>
    <tr class="even"><td>4</td><td>Joe</td><td>25</td></tr>
    <tr class="odd"><td>5</td><td>Lucia</td><td>80</td></tr>
    <tr class="even"><td>6</td><td>Štěpán</td><td>29</td></tr>
  </tbody>
</table>

### Advanced

#### Callbacks
We can specify a callback function instead of property:
```php
$table->addColumn('Name')->setContent(function (TestObject $obj) {
    return '<em class="red">' . $obj->getName() . '</em>';
});
``` 
<table>
  <thead>
    <tr><th>ID of person</th><th>Name</th><th>Age</th></tr>
  </thead>
  <tbody>
    <tr class="odd"><td>1</td><td><em class="red">John</em></td><td>38</td></tr>
    <tr class="even"><td>2</td><td><em class="red">Susane</em></td><td>35</td></tr>
    <tr class="odd"><td>3</td><td><em class="red">Paul</em></td><td>13</td></tr>
    <tr class="even"><td>4</td><td><em class="red">John</em></td><td>38</td></tr>
    <tr class="odd"><td>5</td><td><em class="red">Susane</em></td><td>35</td></tr>
    <tr class="even"><td>6</td><td><em class="red">Paul</em></td><td>13</td></tr>
  </tbody>
</table>

But on of the ```setProperty()``` and ```setContent()``` must be specified. 

#### Sorting and filtering table data

Sometimes we need sort data by some attribute:
```php
$table->addColumn('Name')->setOrderable()->setProperty('name');
```
and now by clicking on the table column header, we can sort the rows by this column.

By typing code below the field for filtering of specific column data will appear:
```php
$table->addColumn('Name')->setSearchable()->setProperty('name');
```

If we don't want to show all rows and enable paging, which will render input for number of rows:
```php
$table->enableListing();
```


#### Column class

Column can has it's own HTML class

```php
$table->addColumn('ColName')->setClass('red');
```

#### Additional
Or if is need to customize URL, use method ```setNavigationNames``` like below:
```php
$table->->setNavigationNames([
    'limit' => 'cust_limit',
    'orderBy' => 'cust_order_by',
    'order' => 'cust_order',
    'page' => 'cust_page',
    'pattern' => 'cust_pattern',
    'url' => 'my_Server'
])
```
will cause the URL will look after some table click action like 
```http:/my_server/?custom_sort_by=Name&custom_sort=ASC&custom_limit=5&custom_page=1&custom_pattern=```
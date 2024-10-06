--TEST--
7.phpt: 3 row 3 column, getCellAttributes / getRowAttributes
--FILE--
<?php
// $Id: 7.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table('width="400"');

$data[0][] = 'Foo';
$data[0][] = 'Bar';
$data[0][] = 'Test';
$data[1][] = 'Foo';
$data[1][] = 'Bar';
$data[1][] = 'Test';
$data[2][] = 'Foo';
$data[2][] = 'Bar';
$data[2][] = 'Test';

foreach($data as $key => $value) {
    $table->addRow($value, 'bgcolor = "yellow" align = "right"');
}

// This overwrites attrs thus removing align="right"
$table->setColAttributes(0, 'bgcolor = "purple"');
// This updates attrs, change bgcolor, but in tr
$table->updateRowAttributes(2, 'bgcolor = "blue"', true);

// output
// row 2, cell 2
var_dump($table->getCellAttributes(2, 2));
var_dump($table->getRowAttributes(2));
?>
--EXPECT--
array(2) {
  ["bgcolor"]=>
  string(6) "yellow"
  ["align"]=>
  string(5) "right"
}
array(1) {
  ["bgcolor"]=>
  string(4) "blue"
}
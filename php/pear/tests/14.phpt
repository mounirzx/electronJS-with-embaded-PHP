--TEST--
14.phpt: 3 row 3 column, setColCount / getColCount / setRowCount / getRowCount
--FILE--
<?php
// $Id: 14.phpt 297540 2010-04-05 19:58:39Z wiesemann $
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
    $table->addRow($value, 'bgcolor = "yellow"');
}

echo $table->getRowCount() . "\n";
echo $table->getColCount() . "\n";

echo $table->setRowCount(2);
echo $table->setColCount(2);

echo $table->getRowCount() . "\n";
echo $table->getColCount() . "\n";

// output
echo $table->toHTML();
?>
--EXPECT--
3
3
2
2
<table width="400">
	<tr>
		<td bgcolor="yellow">Foo</td>
		<td bgcolor="yellow">Bar</td>
	</tr>
	<tr>
		<td bgcolor="yellow">Foo</td>
		<td bgcolor="yellow">Bar</td>
	</tr>
</table>
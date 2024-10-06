--TEST--
10.phpt: 6 row 3 column, setAllAttributes
--FILE--
<?php
// $Id: 10.phpt 297540 2010-04-05 19:58:39Z wiesemann $
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
$data[3][] = 'Foo';
$data[3][] = 'Bar';
$data[3][] = 'Test';
$data[4][] = 'Foo';
$data[4][] = 'Bar';
$data[4][] = 'Test';
$data[5][] = 'Foo';
$data[5][] = 'Bar';
$data[5][] = 'Test';

foreach($data as $key => $value) {
    $table->addRow($value, 'bgcolor = "yellow" align = "right"');
}

// This overwrites attrs thus removing align="right"
$table->setColAttributes(0, 'bgcolor = "pink"');
// This updates attrs thus keeping aligh="right" but change bgcolor
$table->updateColAttributes(2, array('bgcolor = "blue"', 'bgcolor="green"', 'bgcolor="red"'));

// this overwrites
$table->setAllAttributes('bgcolor="pink"');

// output
echo $table->toHTML();
?>
--EXPECT--
<table width="400">
	<tr>
		<td bgcolor="pink">Foo</td>
		<td bgcolor="pink">Bar</td>
		<td bgcolor="pink">Test</td>
	</tr>
	<tr>
		<td bgcolor="pink">Foo</td>
		<td bgcolor="pink">Bar</td>
		<td bgcolor="pink">Test</td>
	</tr>
	<tr>
		<td bgcolor="pink">Foo</td>
		<td bgcolor="pink">Bar</td>
		<td bgcolor="pink">Test</td>
	</tr>
	<tr>
		<td bgcolor="pink">Foo</td>
		<td bgcolor="pink">Bar</td>
		<td bgcolor="pink">Test</td>
	</tr>
	<tr>
		<td bgcolor="pink">Foo</td>
		<td bgcolor="pink">Bar</td>
		<td bgcolor="pink">Test</td>
	</tr>
	<tr>
		<td bgcolor="pink">Foo</td>
		<td bgcolor="pink">Bar</td>
		<td bgcolor="pink">Test</td>
	</tr>
</table>
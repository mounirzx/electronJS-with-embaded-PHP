--TEST--
5.phpt: 3 row 3 column, setColAttributes / updateColAttributes
--FILE--
<?php
// $Id: 5.phpt 297540 2010-04-05 19:58:39Z wiesemann $
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
// This updates attrs thus keeping aligh="right" but change bgcolor
$table->updateColAttributes(2, 'bgcolor = "blue"');

// output
echo $table->toHTML();
?>
--EXPECT--
<table width="400">
	<tr>
		<td bgcolor="purple">Foo</td>
		<td bgcolor="yellow" align="right">Bar</td>
		<td bgcolor="blue" align="right">Test</td>
	</tr>
	<tr>
		<td bgcolor="purple">Foo</td>
		<td bgcolor="yellow" align="right">Bar</td>
		<td bgcolor="blue" align="right">Test</td>
	</tr>
	<tr>
		<td bgcolor="purple">Foo</td>
		<td bgcolor="yellow" align="right">Bar</td>
		<td bgcolor="blue" align="right">Test</td>
	</tr>
</table>
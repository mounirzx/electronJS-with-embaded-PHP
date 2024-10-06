--TEST--
30.phpt: colgroup usage with col tags
--FILE--
<?php
// $Id: 30.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table();

$colgroup = array('style="font-size: 120%;"', 'class="col2"', 'align="right"');
$attributes = 'span="3" class="group1"';
$table->setColGroup($colgroup, $attributes);

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
    $table->addRow($value);
}

echo $table->toHTML();
?>
--EXPECT--
<table>
	<colgroup span="3" class="group1">
		<col style="font-size: 120%;" />
		<col class="col2" />
		<col align="right" />
	</colgroup>
	<tr>
		<td>Foo</td>
		<td>Bar</td>
		<td>Test</td>
	</tr>
	<tr>
		<td>Foo</td>
		<td>Bar</td>
		<td>Test</td>
	</tr>
	<tr>
		<td>Foo</td>
		<td>Bar</td>
		<td>Test</td>
	</tr>
	<tr>
		<td>Foo</td>
		<td>Bar</td>
		<td>Test</td>
	</tr>
	<tr>
		<td>Foo</td>
		<td>Bar</td>
		<td>Test</td>
	</tr>
	<tr>
		<td>Foo</td>
		<td>Bar</td>
		<td>Test</td>
	</tr>
</table>
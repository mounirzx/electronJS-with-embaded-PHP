--TEST--
24.phpt: thead, tfoot, 2 tbodies (with ids) and addRow
--FILE--
<?php
// $Id: 24.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table();

$thead =& $table->getHeader();
$tfoot =& $table->getFooter();

$tbody1 =& $table->getBody(1);
$tbody1->setAttribute('id', 'tbody1');

$tbody2 =& $table->getBody(2);
$tbody2->setAttribute('id', 'tbody2');

$data[0][] = 'Test';
$data[1][] = 'Test';

foreach($data as $key => $value) {
    $thead->addRow($value);
    $tfoot->addRow($value);
    $tbody1->addRow($value);
    $tbody2->addRow($value);
}

// output
echo $table->toHTML();
?>
--EXPECT--
<table>
	<thead>
		<tr>
			<td>Test</td>
		</tr>
		<tr>
			<td>Test</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td>Test</td>
		</tr>
		<tr>
			<td>Test</td>
		</tr>
	</tfoot>
	<tbody id="tbody1">
		<tr>
			<td>Test</td>
		</tr>
		<tr>
			<td>Test</td>
		</tr>
	</tbody>
	<tbody id="tbody2">
		<tr>
			<td>Test</td>
		</tr>
		<tr>
			<td>Test</td>
		</tr>
	</tbody>
</table>
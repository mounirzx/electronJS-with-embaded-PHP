--TEST--
17.phpt: 3 row 2 column, setCaption
--FILE--
<?php
// $Id: 17.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table('width="400"');

$data[0][] = 'Test';
$data[0][] = 'Test';
$data[1][] = 'Test';
$data[1][] = 'Test';
$data[2][] = 'Test';
$data[2][] = 'Test';

foreach($data as $key => $value) {
    $table->addRow($value, 'bgcolor = "yellow" align = "right"');
}

$table->setColType(0, 'TH');
$table->setRowType(1, 'TD');
$table->setRowType(2, 'TH');

// output
echo $table->toHTML();
?>
--EXPECT--
<table width="400">
	<tr>
		<th bgcolor="yellow" align="right">Test</th>
		<td bgcolor="yellow" align="right">Test</td>
	</tr>
	<tr>
		<td bgcolor="yellow" align="right">Test</td>
		<td bgcolor="yellow" align="right">Test</td>
	</tr>
	<tr>
		<th bgcolor="yellow" align="right">Test</th>
		<th bgcolor="yellow" align="right">Test</th>
	</tr>
</table>
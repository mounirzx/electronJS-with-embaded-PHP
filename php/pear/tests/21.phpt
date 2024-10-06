--TEST--
21.phpt: addCol with indexed rows (row 2, col 1; row 3, col 0)
--FILE--
<?php
// $Id: 21.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table();

$data[0][3] = 'Test';
$data[1][2] = 'Test';

foreach($data as $key => $value) {
    $table->addCol($value);
}

// output
echo $table->toHTML();
?>
--EXPECT--
<table>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Test</td>
	</tr>
	<tr>
		<td>Test</td>
		<td>&nbsp;</td>
	</tr>
</table>
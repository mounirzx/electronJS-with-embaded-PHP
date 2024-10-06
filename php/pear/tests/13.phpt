--TEST--
13.phpt: 2 row 1 column, setCellContents / getCellContents
--FILE--
<?php
// $Id: 13.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table();

$data[0][] = 'Test';
$data[1][] = '';

foreach($data as $key => $value) {
    $table->addRow($value);
}

echo $table->getCellContents(0, 0) . "\n";
$table->setCellContents(0, 0, 'FOOBAR');
echo $table->getCellContents(0, 0) . "\n";
// output
echo $table->toHTML();
?>
--EXPECT--
Test
FOOBAR
<table>
	<tr>
		<td>FOOBAR</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>
--TEST--
12.phpt: 2 row 2 column, setAutoGrow / getAutoGrow
--FILE--
<?php
// $Id: 12.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table();

$table->setAutoGrow(true);

$data[0][] = 'Test';
$data[1][] = '';
$data[1][] = 'Test';

foreach($data as $key => $value) {
    $table->addRow($value);
}

var_dump($table->getAutoGrow());

// output
echo $table->toHTML();
?>
--EXPECT--
bool(true)
<table>
	<tr>
		<td>Test</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Test</td>
	</tr>
</table>
--TEST--
11.phpt: 2 row 1 column, setAutoFill / getAutoFill
--FILE--
<?php
// $Id: 11.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table();

$table->setAutoFill('N/A');

$data[0][] = 'Test';
$data[1][] = '';

foreach($data as $key => $value) {
    $table->addRow($value);
}

echo $table->getAutoFill() . "\n";

// output
echo $table->toHTML();
?>
--EXPECT--
N/A
<table>
	<tr>
		<td>Test</td>
	</tr>
	<tr>
		<td>N/A</td>
	</tr>
</table>
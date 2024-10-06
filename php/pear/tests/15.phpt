--TEST--
15.phpt: 2 row 2 column, setHeaderContents
--FILE--
<?php
// $Id: 15.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table('width="400"');

$data[0][] = 'Test';
$data[0][] = 'Test';
$data[1][] = 'Test';
$data[1][] = 'Test';

foreach($data as $key => $value) {
    $table->addRow($value, 'bgcolor = "yellow" align = "right"');
}

$table->setHeaderContents(0, 0, 'Header', 'bgcolor="blue"');

// output
echo $table->toHTML();
?>
--EXPECT--
<table width="400">
	<tr>
		<th bgcolor="blue" align="right">Header</th>
		<td bgcolor="yellow" align="right">Test</td>
	</tr>
	<tr>
		<td bgcolor="yellow" align="right">Test</td>
		<td bgcolor="yellow" align="right">Test</td>
	</tr>
</table>
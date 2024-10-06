--TEST--
8.phpt: testing taboffset
--FILE--
<?php
// $Id: 8.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table('', 1);

$data[0][] = 'Test';

foreach($data as $key => $value) {
    $table->addRow($value);
}

// output
echo $table->toHTML();
?>
--EXPECT--
	<table>
		<tr>
			<td>Test</td>
		</tr>
	</table>
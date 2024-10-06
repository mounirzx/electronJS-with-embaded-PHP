--TEST--
25.phpt: tbody, tfoot and addRow (thead not in output)
--FILE--
<?php
// $Id: 25.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table();

$tbody =& $table->getBody();
$tfoot =& $table->getFooter();

$data[0][] = 'Test';
$data[1][] = 'Test';

foreach($data as $key => $value) {
    $tbody->addRow($value);
    $tfoot->addRow($value);
}

// output
echo $table->toHTML();
?>
--EXPECT--
<table>
	<tfoot>
		<tr>
			<td>Test</td>
		</tr>
		<tr>
			<td>Test</td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td>Test</td>
		</tr>
		<tr>
			<td>Test</td>
		</tr>
	</tbody>
</table>
--TEST--
26.phpt: thead, tfoot, tbody with mixed function calls
--FILE--
<?php
// $Id: 26.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table('width="400"');

$thead =& $table->getHeader();
$tfoot =& $table->getFooter();
$tbody =& $table->getBody();

$thead->setHeaderContents(2, 2, 'some th content', 'bgcolor="red"');
$tfoot->setHeaderContents(1, 1, 'another th content', 'bgcolor="yellow"');

$data[0][] = 'Test';
$data[1][] = 'Test';

foreach($data as $key => $value) {
    $tbody->addRow($value, 'bgcolor="darkblue"');
}

// output
echo $table->toHTML();
?>
--EXPECT--
<table width="400">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<th bgcolor="red">some th content</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<th bgcolor="yellow">another th content</th>
			<td>&nbsp;</td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td bgcolor="darkblue">Test</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td bgcolor="darkblue">Test</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>
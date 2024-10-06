--TEST--
28.phpt: thead, tfoot, tbody with array functionality of setCellContents()
--FILE--
<?php
// $Id: 28.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table(null, null, false);

$thead =& $table->getHeader();
$tfoot =& $table->getFooter();
$tbody =& $table->getBody();

$thead->setAutoFill('foo');
$tfoot->setAutoFill('bar');
$table->setAutoFill('[empty]');

$data = array(1 => 'row1', 'row2', 'row3');

foreach($data as $key => $value) {
    $thead->setCellContents($key, 0, $value);
    $tfoot->setCellContents($key, 1, $value);
    $tbody->setCellContents($key, 2, $value);
}

// output
echo $table->toHTML();
?>
--EXPECT--
<table>
	<thead>
		<tr>
			<td>foo</td>
			<td>foo</td>
			<td>foo</td>
		</tr>
		<tr>
			<td>row1</td>
			<td>foo</td>
			<td>foo</td>
		</tr>
		<tr>
			<td>row2</td>
			<td>foo</td>
			<td>foo</td>
		</tr>
		<tr>
			<td>row3</td>
			<td>foo</td>
			<td>foo</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td>bar</td>
			<td>bar</td>
			<td>bar</td>
		</tr>
		<tr>
			<td>bar</td>
			<td>row1</td>
			<td>bar</td>
		</tr>
		<tr>
			<td>bar</td>
			<td>row2</td>
			<td>bar</td>
		</tr>
		<tr>
			<td>bar</td>
			<td>row3</td>
			<td>bar</td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td>[empty]</td>
			<td>[empty]</td>
			<td>[empty]</td>
		</tr>
		<tr>
			<td>[empty]</td>
			<td>[empty]</td>
			<td>row1</td>
		</tr>
		<tr>
			<td>[empty]</td>
			<td>[empty]</td>
			<td>row2</td>
		</tr>
		<tr>
			<td>[empty]</td>
			<td>[empty]</td>
			<td>row3</td>
		</tr>
	</tbody>
</table>
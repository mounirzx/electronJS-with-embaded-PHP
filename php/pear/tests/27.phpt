--TEST--
27.phpt: thead, tfoot, tbody with mixed function calls
--FILE--
<?php
// $Id: 27.phpt 297540 2010-04-05 19:58:39Z wiesemann $
require_once 'HTML/Table.php';
$table = new HTML_Table(null, null, false);

$thead =& $table->getHeader();
$tfoot =& $table->getFooter();
$tbody =& $table->getBody();

$thead->setAutoFill('foo');
$tfoot->setAutoFill('bar');

$data[0][] = 'Test';
$data[1][] = 'Test';
$data[2][] = 'Test';

foreach($data as $key => $value) {
    $thead->setCellAttributes($key, $key, array('style' => 'border: 1px solid purple;'));
    $tfoot->setCellContents($key, $key, 'some content', 'TH');
    $tbody->addRow($value, 'bgcolor="darkblue"');
}

// output
echo $table->toHTML();
?>
--EXPECT--
<table>
	<thead>
		<tr>
			<td style="border: 1px solid purple;">foo</td>
			<td>foo</td>
			<td>foo</td>
		</tr>
		<tr>
			<td>foo</td>
			<td style="border: 1px solid purple;">foo</td>
			<td>foo</td>
		</tr>
		<tr>
			<td>foo</td>
			<td>foo</td>
			<td style="border: 1px solid purple;">foo</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th>some content</th>
			<td>bar</td>
			<td>bar</td>
		</tr>
		<tr>
			<td>bar</td>
			<th>some content</th>
			<td>bar</td>
		</tr>
		<tr>
			<td>bar</td>
			<td>bar</td>
			<th>some content</th>
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
		<tr>
			<td bgcolor="darkblue">Test</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>
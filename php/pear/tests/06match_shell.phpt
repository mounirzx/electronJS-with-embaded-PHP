--TEST--
File_Find::test 'shell' mode patterns
--SKIPIF--
<?php 
include(dirname(__FILE__).'/setup.php');
print $status; 
?>
--FILE--
<?php 
require_once(dirname(__FILE__).'/setup.php');

// *.php -> .*\.php$
$result[0] = &File_Find::search('*.*', 'File_Find/dir/', 'shell', false);
$result[1] = &File_Find::search('*.bak', 'File_Find/dir/', 'shell', false);
$result[2] = &File_Find::search('*3*', 'File_Find/', 'shell', false, 'both');
$result[3] = &File_Find::glob('', 'File_Find/dir', 'shell');

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    foreach($result as $k => $r) {
        $result[$k] = str_replace("\\", '/', $result[$k]);
    }
}

print_r($result[0]);
print_r($result[1]);
print_r($result[2]);
print_r($result[3]);

$tcases[] = array("*scope", "msscope");
$tcases[] = array("some*", "something");
$tcases[] = array("some*", "some");
$tcases[] = array("some", "something.wrong");
$tcases[] = array("*som?e*", "som_exx.dll");
$tcases[] = array("*.dll", "som_exx.dll");
$tcases[] = array("*.dll", "som_exx.dll.bak");
$tcases[] = array("*.", "som_exx.dll.bak");
$tcases[] = array("*.", "som_exx");
$tcases[] = array("*.", ".exx");
$tcases[] = array("some file.xml.*", "some file.xml");
$tcases[] = array("some file.*", "some file");
$tcases[] = array("some.*", "some.xml");
$tcases[] = array("some.*", "somexml");

foreach($tcases as $tc) {
    list($tm, $tf) = $tc;
    echo ( File_Find_match_shell($tm, $tf) ) ? "TRUE  " : "FALSE ";
    echo "$tm \t$tf\n";
}

?>
--GET--
--POST--
--EXPECT--
Array
(
    [0] => File_Find/dir/1.txt
    [1] => File_Find/dir/2.txt
    [2] => File_Find/dir/dir2/3.bak
    [3] => File_Find/dir/dir2/3.txt
    [4] => File_Find/dir/dir3/4.bak
    [5] => File_Find/dir/dir3/4.txt
    [6] => File_Find/dir/txtdir/5.txt
)
Array
(
    [0] => File_Find/dir/dir2/3.bak
    [1] => File_Find/dir/dir3/4.bak
)
Array
(
    [0] => File_Find/dir/dir2/3.bak
    [1] => File_Find/dir/dir2/3.txt
    [2] => File_Find/dir/dir3
)
Array
(
    [0] => 1.txt
    [1] => 2.txt
    [2] => dir2
    [3] => dir3
    [4] => txtdir
)
TRUE  *scope 	msscope
TRUE  some* 	something
TRUE  some* 	some
TRUE  some 	something.wrong
TRUE  *som?e* 	som_exx.dll
TRUE  *.dll 	som_exx.dll
FALSE *.dll 	som_exx.dll.bak
FALSE *. 	som_exx.dll.bak
TRUE  *. 	som_exx
FALSE *. 	.exx
TRUE  some file.xml.* 	some file.xml
TRUE  some file.* 	some file
TRUE  some.* 	some.xml
FALSE some.* 	somexml

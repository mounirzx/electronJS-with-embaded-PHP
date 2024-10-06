--TEST--
File_Find::mapTree()
--SKIPIF--
<?php 
include(dirname(__FILE__).'/setup.php');
print $status; 
?>
--FILE--
<?php 
require_once(dirname(__FILE__).'/setup.php');

$ff = new File_Find();
$result[0]  = $ff->mapTree('File_Find/dir/') ;
$result[1]  = $ff->mapTree('File_Find/dir') ;
$result[2] = File_Find::mapTree('File_Find/dir') ;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    foreach($result as $k => $r) {
        $result[$k][0] = str_replace("\\", '/', $result[$k][0]);
        $result[$k][1] = str_replace("\\", '/', $result[$k][1]);
    }
}

print_r($result[0]);
print_r($result[1]);
print_r($result[2]);

?>
--GET--
--POST--
--EXPECT--
Array
(
    [0] => Array
        (
            [0] => File_Find/dir
            [1] => File_Find/dir/dir2
            [2] => File_Find/dir/dir3
            [3] => File_Find/dir/txtdir
        )

    [1] => Array
        (
            [0] => File_Find/dir/1.txt
            [1] => File_Find/dir/2.txt
            [2] => File_Find/dir/dir2/3.bak
            [3] => File_Find/dir/dir2/3.txt
            [4] => File_Find/dir/dir3/4.bak
            [5] => File_Find/dir/dir3/4.txt
            [6] => File_Find/dir/txtdir/5.txt
        )

)
Array
(
    [0] => Array
        (
            [0] => File_Find/dir
            [1] => File_Find/dir/dir2
            [2] => File_Find/dir/dir3
            [3] => File_Find/dir/txtdir
        )

    [1] => Array
        (
            [0] => File_Find/dir/1.txt
            [1] => File_Find/dir/2.txt
            [2] => File_Find/dir/dir2/3.bak
            [3] => File_Find/dir/dir2/3.txt
            [4] => File_Find/dir/dir3/4.bak
            [5] => File_Find/dir/dir3/4.txt
            [6] => File_Find/dir/txtdir/5.txt
        )

)
Array
(
    [0] => Array
        (
            [0] => File_Find/dir
            [1] => File_Find/dir/dir2
            [2] => File_Find/dir/dir3
            [3] => File_Find/dir/txtdir
        )

    [1] => Array
        (
            [0] => File_Find/dir/1.txt
            [1] => File_Find/dir/2.txt
            [2] => File_Find/dir/dir2/3.bak
            [3] => File_Find/dir/dir2/3.txt
            [4] => File_Find/dir/dir3/4.bak
            [5] => File_Find/dir/dir3/4.txt
            [6] => File_Find/dir/txtdir/5.txt
        )

)

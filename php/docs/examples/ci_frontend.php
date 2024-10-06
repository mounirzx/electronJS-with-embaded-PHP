<?php
/**
 * PEAR::CompatInfo Web frontend
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: ci_frontend.php,v 1.6 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 */

require_once 'PEAR/Registry.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/advmultiselect.php';

if (version_compare(phpversion(), '5.0.0', '<')) {
    include_once 'PHP/Compat.php';
    PHP_Compat::loadFunction('array_combine');
}

session_start();
$sess =& $_SESSION;

$config = new PEAR_Config();
$pear_install_dir = $config->get('php_dir');
$reg = new PEAR_Registry($pear_install_dir);

if (count($sess) == 0) {

    // PEAR packages installed from each channel
    $allpackages = $reg->listAllPackages();
    foreach ($allpackages as $channel => $packages) {
        if ($packages) {
            sort($packages, SORT_ASC);
            foreach ($packages as $package) {
                $info = &$reg->getPackage($package, $channel);
                if (is_object($info)) {
                    $name          = $info->getPackage();
                    $version       = $info->getVersion();
                    $release_state = $info->getState();
                } else {
                    $name          = $info['package'];
                    $version       = $info['version'];
                    $release_state = $info['state'];
                }
                $sess['packages'][$channel][] = "$name $version ($release_state)";
            }
        } else {
            $sess['packages'][$channel] = array();
        }
    }

    // channels
    $channels = array_keys($sess['packages']);
    array_unshift($channels, '');
    $sess['channels'] = array_combine($channels, $channels);

    // packages
    $names[''] = array();
    foreach ($sess['packages'] as $c => $p) {
        if (count($p)) {
            $l = array();
            foreach ($p as $k) {
                list($n, $v, $s) = explode(' ', $k);
                $l[] = $n;
            }
            $names[$c] = array_combine($l, $p);
        } else {
            $names[$c] = array();
        }
    }
    $sess['pkgnames'] = $names;

    // PHP internal functions
    $func = get_defined_functions();
    sort($func['internal']);
    $sess['phpfunctions'] = $func['internal'];
}

// ignore functions
$ignore_functions = array_combine($sess['phpfunctions'], $sess['phpfunctions']);

// web frontend
$form = new HTML_QuickForm('cife');
$form->removeAttribute('name');        // XHTML compliance

// header
$form->addElement('header', 'cife_hdr', 'CompatInfo :: Web frontend');

// ignore functions
$ams1 =& $form->addElement('advmultiselect', 'ignore_functions', null,
    $ignore_functions,
    array('size' => 5, 'style' => 'width:250px;', 'class' => 'pool'));
$ams1->setLabel(array('PHP functions:', 'available', 'ignore'));

// packages installed
$pkgInstalled =& $form->addElement('hierselect', 'package', null, array('class' => 'flat'), '&nbsp;');
$pkgInstalled->setLabel('Packages installed:');
$pkgInstalled->setOptions(array($sess['channels'], $sess['pkgnames']));
$form->addElement('submit', 'filelist', 'File List');

// ignore files
$safe = $form->getSubmitValues();

if (isset($safe['filelist'])) {
    $package = &$reg->getPackage($safe['package'][1], $safe['package'][0]);
    $files   = array();

    $filelist = $package->getFilelist();
    foreach ($filelist as $name => $atts) {
        if (isset($atts['role']) && $atts['role'] != 'php') {
            continue;
        }
        if (!preg_match('/\.php$/', $name)) {
            continue;
        }
        $files[] = $atts['installed_as'];
    }
    $sess['phpfiles'] = $files;
    $labels = str_replace($pear_install_dir . DIRECTORY_SEPARATOR, '', $files);
    $ignore_files = array_combine($files, $labels);

} else {
    $ignore_files = array();
}

$ams2 =& $form->addElement('advmultiselect', 'ignore_files', null,
    $ignore_files,
    array('size' => 5, 'style' => 'width:300px;', 'class' => 'pool'));
$ams2->setLabel(array('Package files (role=php):', 'available', 'ignore'));

// dump options
$dump =& $form->addElement('checkbox', 'dump');
$dump->setLabel('Dump:');
$dump->setText('PHP min version only');

$dbg =& $form->addElement('checkbox', 'dbg');
$dbg->setLabel('Debug:');
$dbg->setText('Extra output');

// commands
$form->addElement('submit', 'process', 'Process');
$form->addElement('submit', 'abort', 'Abort');

// initial values
$form->setDefaults(array(
    'ignore_functions' => array(),
    'ignore_files' => array(),
    'dump' => true
    ));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3c.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>PEAR::PHP_CompatInfo Web frontend </title>
<style type="text/css">
<!--
body {
  background-color: #FFF;
  font-family: Verdana, Arial, helvetica;
  font-size: 10pt;
}

.error {
  color: red;
  font-weight: bold;
}

.dump {
  background-color: #EEE;
  color: black;
}

table.pool {
  border: 0;
  background-color: #339900;
  width:450px;
}
table.pool th {
  font-size: 80%;
  font-style: italic;
  text-align: left;
}
table.pool select {
  color: white;
  background-color: #006600;
}

.inputCommand {
    background-color: #d0d0d0;
    border: 1px solid #7B7B88;
    width: 7em;
    margin-bottom: 2px;
}
 -->
</style>
<script type="text/javascript">
//<![CDATA[
<?php
echo $ams1->getElementJs();

echo $ams2->getElementJs();
?>
//]]>
</script>
</head>
<body>
<?php
if ($form->validate()) {
    $safe = $form->getSubmitValues();

    if (isset($safe['ignore_files'])) {
        $ignore_files = $safe['ignore_files'];
    } else {
        $ignore_files = array();
    }
    if (isset($safe['ignore_functions'])) {
        $ignore_functions = $safe['ignore_functions'];
    } else {
        $ignore_functions = array();
    }

    if (!isset($sess['phpfiles']) && !isset($safe['abort'])) {
        echo '<p class="error">Please get file list before to process.</p>';
    } else {
        if (isset($safe['process'])) {

            include_once 'PHP/CompatInfo.php';

            $info = new PHP_CompatInfo();

            $options = array(
                'debug' => (isset($safe['dbg'])),
                'ignore_files' => $ignore_files,
                'ignore_functions' => $ignore_functions
                );

            $res = $info->parseArray($sess['phpfiles'], $options);

            $php = $res['version'];

            echo "<h1>CompatInfo for package {$safe['package'][1]}</h1>";
            echo "<h2>PHP $php min is required</h2>";

            if (!isset($safe['dump'])) {
                echo '<pre class="dump">';
                var_dump($res);
                echo '</pre>';
            }
        }

        if (isset($safe['process']) || isset($safe['abort'])) {
            // cleansweep before quit
            $_SESSION = array();
            session_destroy();
            if (isset($safe['abort'])) {
                echo '<h1>Task was aborted !</h1>';
            }
            exit();
        }
    }
}

$form->display();
?>
</body>
</html>
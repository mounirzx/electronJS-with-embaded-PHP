<?php
/**
 * Parse a folder and wait with an HTML Progress bar
 *
 * This example show the new options|features available with API 1.8.0
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsefolder_tohtml.php,v 1.2 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.8.0b4 (2008-06-18)
 * @ignore
 */

require_once 'HTML/Progress2.php';
require_once 'PHP/CompatInfo.php';

/**
 * @ignore
 */
class myClass
{
    function doSomething()
    {
       global $bar;

       // You may also use, all others renderer available
       $driverType    = 'html';
       $driverOptions = array('args' => array('output-level' => 18));

       $info = new PHP_CompatInfo($driverType, $driverOptions);
       $info->addListener(array(&$bar, 'notify'));
       $dir  = 'C:\Temp\beehiveforum082\forum';
       $opt  = array();

       // You may use the new unified method parseData(), parseDir() became an alias
       $info->parseData($dir, $opt);
    }
}

/**
 * @ignore
 */
class myBar extends HTML_Progress2
{
    // number of file to parse
    var $_fileCount;

    function myBar()
    {
        parent::HTML_Progress2();

        $this->addLabel(HTML_PROGRESS2_LABEL_TEXT, 'txt1');
        $this->setLabelAttributes('txt1', array(
            'valign' => 'top',
            'left' => 0,
            ));
        $this->addLabel(HTML_PROGRESS2_LABEL_TEXT, 'txt2');
        $this->setLabelAttributes('txt2', array(
            'valign' => 'bottom',
            'left' => 0,
            ));
    }

    function notify(&$notification)
    {
        $notifyName = $notification->getNotificationName();
        $notifyInfo = $notification->getNotificationInfo();

        switch ($notifyName) {
        case PHP_COMPATINFO_EVENT_AUDITSTARTED :
            $this->_fileCount = $notifyInfo['dataCount'];
            // to keep the good proportion with default increment (+1)
            $this->setMaximum($this->_fileCount);
            break;
        case PHP_COMPATINFO_EVENT_FILESTARTED :
            $current = $notifyInfo['fileindex'];
            $max     = $this->_fileCount;
            $file    = basename($notifyInfo['filename']);

            $this->setLabelAttributes('txt1',
                                      array('value' => $current.'/'.$max.' files'));
            $this->setLabelAttributes('txt2',
                                      array('value' => $file));
            break;
        case PHP_COMPATINFO_EVENT_FILEFINISHED :
            $this->moveNext();
            break;
        case PHP_COMPATINFO_EVENT_AUDITFINISHED :
            $this->hide();  // progress bar hidden
            break;
        }
    }
}

set_time_limit(0); // because parsing a huge folder may exceed 30 seconds
$bar = new myBar();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3c.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Parse a folder and wait with an HTML progress bar</title>
<style type="text/css">
<!--
<?php echo $bar->getStyle(); ?>

body {
    background-color: #FFFFFF;
}
 -->
</style>
<?php echo $bar->getScript(false); ?>
</head>
<body>

<?php
$bar->display();

$process = new myClass();
$process->doSomething();
?>

</body>
</html>
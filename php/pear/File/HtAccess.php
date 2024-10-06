<?php
/* vim: set ts=4 sw=4: */

/*
+-----------------------------------------------------------------------+
| Copyright (c) 2002-2007 Mika Tuupola                                  |
| All rights reserved.                                                  |
|                                                                       |
| Redistribution and use in source and binary forms, with or without    |
| modification, are permitted provided that the following conditions    |
| are met:                                                              |
|                                                                       |
| o Redistributions of source code must retain the above copyright      |
|   notice, this list of conditions and the following disclaimer.       |
| o Redistributions in binary form must reproduce the above copyright   |
|   notice, this list of conditions and the following disclaimer in the |
|   documentation and/or other materials provided with the distribution.|
|                                                                       |
| THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
| "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
| LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
| A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
| OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
| SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
| LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
| DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
| THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
| (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
| OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Mika Tuupola <tuupola@appelsiini.net>                         |
+-----------------------------------------------------------------------+
*/

/* $Id: HtAccess.php,v 1.17 2007/08/01 08:04:30 tuupola Exp $ */

require_once 'PEAR.php' ;

/**
* Class for manipulating .htaccess files
*
* A class which provided common methods to manipulate Apache / NCSA
* style .htaccess files.
*
* Example 1 (modifying existing file):
*
* $h = new File_HtAccess('.htaccess');
* $h->load();
* $h->setRequire('valid-user');
* $h->save();
*
* Example 2 (modifying existing file):
*
* $h = new File_HtAccess('.htaccess');
* $h->load();
* $h->addRequire('newuser');
* $h->save();
*
* Example 3 (creating a new file):
*
* $params['authname']      = 'Private';
* $params['authtype']      = 'Basic';
* $params['authuserfile']  = '/path/to/.htpasswd';
* $params['authgroupfile'] = '/path/to/.htgroup';
* $params['require']       = array('group', 'admins');
*
* $h = new File_HtAccess('/path/to/.htaccess', $params);
* $h->save();
*
* @author  Mika Tuupola <tuupola@appelsiini.net>
* @access  public
* @version 1.0.0
* @package File_HtAccess
* @category File
*/

class File_HtAccess {

    var $file;
    var $authname;
    var $authtype;
    var $authuserfile;
    var $authgroupfile;
    var $authdigestfile;
    var $authdigestgroupfile;
    var $require    = array();
    var $additional = array();

    /**
    * Constructor
    *
    * @access public
    * @param  string $file
    * @param  array  $params 
    * @return object File_HtAccess
    */
       
    function File_HtAccess($file='.htaccess', $params='') {

        $this->file = $file;
        $this->setProperties($params);

    }
    
    /**
    * Load the given .htaccess file
    *
    * @access public
    * @return mixed   true on success, PEAR_Error on failure
    */

    function load() {

        $retval = true;
        
        $fd = @fopen($this->getFile(), 'r');
        if ($fd) {
            while ($buffer = fgets($fd, 4096)) {
                $buffer = trim($buffer);
                if ($buffer) {
                    $data = split(' ', $buffer, 2);
                    if (preg_match('/AuthName/i', $data[0])) {
                       $this->setAuthName($data[1]);

                    } elseif (preg_match('/AuthType/i', $data[0])) {
                       $this->setAuthType($data[1]);                
                    
                    } elseif (preg_match('/AuthUserFile/i', $data[0])) {
                       $this->setAuthUserFile($data[1]);            
                           
                    } elseif (preg_match('/AuthGroupFile/i', $data[0])) {
                       $this->setAuthGroupFile($data[1]);

                    } elseif (preg_match('/AuthDigestFile/i', $data[0])) {
                       $this->setAuthDigestFile($data[1]);

                    } elseif (preg_match('/AuthDigestGroupFile/i', $data[0])) {
                       $this->setAuthDigestGroupFile($data[1]);

                    } elseif (preg_match('/Require/i', $buffer)) {
                       $require = split(' ', $data[1]);
                       $this->addRequire($require);
                    } elseif (trim($buffer)) {
                       $this->addAdditional($buffer);
                    }
                }
            }
            fclose($fd);

        } else {
            $retval = PEAR::raiseError('Could not open ' . $this->getFile() . 
                                       ' for reading.');
        }

        return($retval);

    }

    /**
    * Set class properties
    *
    * @access public
    * @param  array  $params
    */

    function setProperties($params) {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $method = 'set' . $key;
                $this->$method($value);
            }
        }
    }
    
    /**
    * Set the value of authname property
    * 
    * @access public
    * @param  string $name
    */

    function setAuthName($name='Restricted') {
        $this->authname = $name;
    }

    /**
    * Set the value of authtype propery
    *
    * @access public
    * @param  string $type
    */

    function setAuthType($type='Basic') {
        $this->authtype = $type;
    }

    /**
    * Set the value of authuserfile propery
    *
    * @access public
    * @param  string $file
    */

    function setAuthUserFile($file='') {
        $this->authuserfile = $file;
    }

    /**
    * Set the value of authgroupfile property
    *
    * @access public
    * @param  string $file
    */

    function setAuthGroupFile($file='') {
        $this->authgroupfile = $file;
    }

    /**
    * Set the value of authdigestfile property
    *
    * @access public
    * @param  string $file
    */

    function setAuthDigestFile($file='') {
        $this->authdigestfile = $file;
    }

    /**
    * Set the value of authdigestgroupfile property
    *
    * @access public
    * @param  string $file
    */

    function setAuthDigestGroupFile($file='') {
        $this->authdigestgroupfile = $file;
    }

    /**
    * Set the value of require property
    *
    * Parameter can be given as an array or string. If given as a string
    * the value will be exploded in to an array by using space as a 
    * separator.
    *
    * @access public
    * @param  mixed $require
    */

    function setRequire($require='') {
        if (is_array($require)) {
            $this->require = $require;
        } else {
            $this->require = explode(' ', $require);
        }
    }

    /**
    * Add a value to require property
    *
    * @access public
    * @param  string $require
    */
    function addRequire($require) {
        if (is_array($require)) {
            $merge = array_merge($this->getRequire(), $require);
            $merge = array_unique($merge);
            $merge = array_merge($merge);
            $this->setRequire($merge);
        } else {
            $this->require[] = $require;
        }

    }

    /**
    * Delete a value from require property
    *
    * @access public
    * @param  string $require
    */
    function delRequire($require) {
        $pos = array_search($require, $this->require);
        unset($this->require[$pos]);
    }

    /**
    * Set the value of additional property
    *
    * Additional property is used for all the extra things in .htaccess
    * file which don't have specific accessor method for them. 
    *
    * @access public
    * @param  array  $additional
    */

    function setAdditional($additional='') {
        $this->additional = (array)$additional;
    }

    /**
    * Add a value to additional property
    *
    * @access public
    * @param  string $additional
    */

    function addAdditional($additional='') {
        $this->additional[] = $additional;
    }
    
    /**
    * Set the value of file property
    *
    * @access public
    * @param  file   $file
    */

    function setFile($file) {
        $this->file = $file;
    }
    
    /**
    * Get the value of authname property
    *
    * @access public
    * @return string  
    */

    function getAuthName() {
        return($this->authname);
    }

    /**
    * Get the value of authtype property
    *
    * @access public
    * @return string  
    */

    function getAuthType() {
        return($this->authtype);
    }
    
    /**
    * Get the value of authuserfile property
    *
    * @access public
    * @return string  
    */

    function getAuthUserFile() {
        return($this->authuserfile);
    }

    /**
    * Get the value of authgroupfile property
    *
    * @access public
    * @return string  
    */


    function getAuthGroupFile() {
        return($this->authgroupfile);
    }

    /**
    * Get the value of authdigestfile property
    *
    * @access public
    * @return string  
    */

    function getAuthDigestFile() {
        return($this->authdigestfile);
    }

    /**
    * Get the value of authdigestgroupfile property
    *
    * @access public
    * @return string  
    */

    function getAuthDigestGroupFile() {
        return($this->authdigestgroupfile);
    }

    /**
    * Get the value(s) of require property
    *
    * @access public
    * @param  string $type whether to return an array or string
    * @return mixed  string or array, defaults to an array  
    */
 
    function getRequire($type='') {
        $retval = $this->require;

        if ($type == 'string') {
            $retval = implode($retval, ' ');
        }
        return($retval);
    }

    /**
    * Get the value(s) of additional property
    *
    * @access public
    * @param  string $type whether to return an array or string
    * @return mixed  string or array, defaults to an array  
    */

    function getAdditional($type='') {
        $retval = $this->additional;

        if ($type == 'string') {
            $retval = implode($retval, "\n");
        }
        return($retval);
    }

    /**
    * Get the value of file property
    *
    * @access public
    * @return string  
    */

    function getFile() {
        return($this->file);
    }

    /**
    * Save the .htaccess file
    *
    * @access public
    * @return mixed      true on success, PEAR_Error on failure
    * @see    PEAR_Error
    */

    function save() {

        $retval = true;

        $str  = $this->getContent();
        
        $fd = @fopen($this->getFile(), 'w');
        if ($fd) {
            fwrite($fd, $str, strlen($str));
        } else {
            $retval = PEAR::raiseError('Could not open ' . $this->getFile() . 
                                       ' for writing.');

        }

        return($retval);
        
    }

    /**
    * Returns the .htaccess File content
    *
    * @access public
    * @return string
    */

    function getContent() {

        $retval  = '';
        
        if ($this->getAuthName()) {
            $retval .= 'AuthName '     . $this->getAuthName() . "\n";
        }
        if ($this->getAuthType()) {
            $retval .= 'AuthType '     . $this->getAuthType() . "\n";
        }
        if ('basic' == strtolower($this->getAuthType())) {
            $retval .= 'AuthUserFile ' . $this->getAuthUserFile() . "\n";
            if (trim($this->getAuthGroupFile())) {
                $retval .= 'AuthGroupFile ' . $this->getAuthGroupFile() . "\n";   
            }
        } elseif ('digest' == strtolower($this->getAuthType())) {
            $retval .= 'AuthDigestFile ' . $this->getAuthDigestFile() . "\n";
            if (trim($this->getAuthDigestGroupFile())) {
                $retval .= 'AuthDigestGroupFile ' . $this->getAuthDigestGroupFile() . "\n";   
            }
        }
        if (trim($this->getRequire('string'))) {
            $retval .= 'Require ' . $this->getRequire('string') . "\n";
        }
        $retval .= $this->getAdditional('string') . "\n";
        
        return($retval);
    }

}

?>

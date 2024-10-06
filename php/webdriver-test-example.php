<?php
/*
Copyright 2011 3e software house & interactive agency

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

// XAMPP NOTE: First please download Selenium RC (*.jar) from here : http://seleniumhq.org/download/
// XAMPP NOTE: Start the Selenium RC Server via cmd with: java -jar selenium-server-standalone-2.25.0.jar 
// XAMPP NOTE: Then start this script with the cmd from here: .\php.exe  -f webdriver-test-example.php  
// XAMPP NOTE: This example uses php-webdriver-bindings-0.9.0 (see the folder xampp\pear\phpwebdriver)

require_once "phpwebdriver/WebDriver.php";
require_once("phpwebdriver/LocatorStrategy.php");

$webdriver = new WebDriver("localhost", "4444");
$webdriver->connect("firefox");                            
$webdriver->get("http://google.com");
$element = $webdriver->findElementBy(LocatorStrategy::name, "q");
$element->sendKeys(array("selenium google code" ) );
$element->submit();

$webdriver->close();
?>
<?php
session_start ();
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Detail.php';
/*$_POST['class'][1] = array ('name' => 'elements-box', 'title' => 'articlecontent');
 $_POST['class'][2] = array ('name' => 'page-title', 'title' => 'also');*/
$detail = new Detail();
$detail->getDetails();
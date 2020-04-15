<?php
require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Filedata.php';

/*$_POST['class'][1] = array ('name' => 'elements-box', 'title' => 'articlecontent');
$_POST['class'][2] = array ('name' => 'page-title', 'title' => 'also');*/


$webCrawler = new WebCrawler();
$webCrawler->mainPage();
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
require_once 'Main.php';

set_time_limit (500000);

$Mainpage = new MainPage();
$Mainpage->getPage();

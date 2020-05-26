<?php
session_start ();
session_write_close();
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Detail.php';
require_once 'research.php';
set_time_limit (10000);

/*class ButtonsAjax {
    
    
    public function __construct()
    {
        $this->MySql = new DbMysql();
    }
    
    public function stopCrawler()
    {
        $this->stopStatusDb();
    }
    
    private function stopStatusDb()
    {
       var_dump($this->MySql->checkStop());
        
    }
    
    
    
    
    
}

$ajaxProcess = new ButtonsAjax();

    $ajaxProcess->stopCrawler();
*/


$researchProcess = new ResearchProcess();
$researchProcess->urlId = 8;
$researchProcess->process();
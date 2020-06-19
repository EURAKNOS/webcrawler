<?php
    require_once 'config.php';
    require_once 'MySQL.php';
    require_once 'Log.php';
    require_once 'ajax.php';
    set_time_limit (10000);
    
    class ContinueCrawler {
        
        public $urlId;
        
        public function __construct()
        {
            $this->MySql = new DbMysql();
            $this->getCrawlerById();
            $crawler = new AjaxProcess();
            $crawler->urlId = $_GET['id'];
            $crawler->continueCrawler();
        }
        
        public function process()
        {
            $log = new WLog();
            $log->m_log('Load WebCrawler page');
            $this->changePost();
        }
        
        private function getCrawlerById()
        {
            $this->MySql->getCrawlingData($_GET['id']);
            $_POST = unserialize($this->MySql->result['post_data']);
            $_POST['processFunction'] = 'continue';
        }        
    }
    
$continueCrawler = new ContinueCrawler();
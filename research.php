<?php
set_time_limit (10000);
ini_set('memory_limit', '-1');
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Puphpeteer\Resources\ElementHandle;
use Sunra\PhpSimple\HtmlDomParser;

class ResearchProcess {
    
    public $urlId;
    
    public function __construct()
    {
        $this->MySql = new DbMysql();
        
        $this->fileTypes = array('page','jpg','png','pdf','docx','xlsx','pptx','epub','swf','youtube_video','vimeo_video','google_map','mp4','zip');
    }
    
    public function process()
    {
        $log = new WLog();
        $log->m_log('Load WebCrawler page');
        $this->startCrawler();
     }
     
    /**
     * After receiving the starting url, it starts processing the first page. Processing the first page is also storing the found urls.
     * In the next round, you will start queuing the urls already stored in the database.
     */
    private function startCrawler()
    {
        /*$req = new HTTP_Request("http://example.com/");
         $req->setProxy("192.168.5.254", 3128);*/
        $log = new WLog();
        $log->m_log('Start Research');
        
        //dinamic
        $puppeteer = new Puppeteer([
            'idle_timeout' => 300,
            'read_timeout' => 300,
            'args' => ['--disable-dev-shm-usage']
        ]);
        $browser = $puppeteer->launch([
            'args' => [
                '--no-sandbox',
                '--disable-setuid-sandbox',
            ]
        ]);
        
        // Loop through all pages on site.
        $this->MySql->urlId = $this->urlId;
        while (1) {
            $counter = 0;
            $rowCount = $this->MySql->getLinksUnSuccess();
            if ($rowCount) {
                for ($i = 0; $i < $rowCount; $i ++) {
                    if($this->MySql->checkStop() === true)  break;
                    $row = $this->MySql->getLinkRowUnSuccess();
                    if ($row !== false) {
                        $parsePage = new ParsePage();
                        $parsePage->urlId = $this->urlId;
                       
                        $parsePage->target = $row['path'];
                        $parsePage->referer = '';
                        $parsePage->path = $row['path'];
                        
                        
                        $parsePage->browser = $browser;
                        if ($parsePage->parsePage(false, true)) {
                            $counter ++;
                        }
                        sleep(1);
                    }
                }
            } else {
                //die("Unable to select un-downloaded pages\n");
            }
            if ($counter == 0) {
                break;
            }
        }
    }
}
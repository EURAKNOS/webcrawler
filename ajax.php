<?php
session_name('crawler');
/*session_start ();
session_write_close();*/

ini_set('session.use_only_cookies', false);
ini_set('session.use_cookies', false);
ini_set('session.use_trans_sid', false);
ini_set('session.cache_limiter', null);

if(array_key_exists('PHPSESSID', $_COOKIE))
    session_id($_COOKIE['PHPSESSID']);
    else {
        session_start();
        setcookie('PHPSESSID', session_id());
        session_write_close();
    }
    

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'research.php';
set_time_limit (10000);

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Puphpeteer\Resources\ElementHandle;
use Sunra\PhpSimple\HtmlDomParser;
/**
 * System AJAX operations. This includes functions for occasional page refresh. Monitoring while the webrawler is running.
 */
class AjaxProcess {
    /*URL ID*/
    public $urlId;
    
    private $stop = false;
    
    public function __construct()
    {
        $this->MySql = new DbMysql();
        
        $this->fileTypes = array('page','jpg','bmp','png','pdf','docx','xlsx','pptx','epub','swf','youtube_video','vimeo_video','google_map','mp4','zip');
        $this->firstCheck = 0;
    }
    /**
     * Function to run Web Crawler. It sets the values and starts downloading the website. 
     */
    public function process()
    {
        $log = new WLog();
        $log->m_log('Load WebCrawler page');
        $this->changePost();
        $_SESSION["processing"] = 1;    // Started the download sets the status
        if($this->startCrawler()) {
            echo json_encode(array('status' => 1) );
        } else {
            echo json_encode(array('status' => 0) );
        }
        // If external links download is enabled and is not stopped running then the process will run
        if (isset($_POST['external']) && $this->stop !== true) {
            $researchProcess = new ResearchProcess();
            $researchProcess->urlId = $this->urlId;
            $researchProcess->process();
        }
        if ($this->stop !== true) {
            $this->MySql->endDownloadUrl($this->urlId);
        }
     }
     /** 
      * Continue downloading
      * Rechecks or sets the required items.
      */
     public function continueCrawler()
     {
         $log = new WLog();
         $log->m_log('Load WebCrawler page');
         $_SESSION["processing"] = 1;
         
         $this->MySql->startStatus($this->urlId);
         
         $this->startCrawler(true);
         if (isset($_POST['external'])) {
             $researchProcess = new ResearchProcess();
             $researchProcess->urlId = $this->urlId;
             $researchProcess->process();
         }
         if ($this->stop !== true) {
             $this->MySql->endDownloadUrl($this->urlId);
         }
     }

     /**
      * It prepares the data received in the POST according to the use
      */
     private function changePost()
     {    
         $temp = $_POST['formdata'];
         $cnt = 1;
         foreach ($temp as $item) {
             if (strpos($item['name'], 'class[') === false) {
                 $formdata[$item['name']] = $item['value'];
             } else {
                 if (strpos($item['name'], '[name]') !== false) {
                     $formdata['class'][$cnt]['name'] = $item['value'];
                 } else {
                     $formdata['class'][$cnt]['title'] = $item['value'];
                     $cnt++;
                 }
             }
              
         }
         $_POST = $formdata;
         return;
     }
     
    /**
     * After receiving the starting url, it starts processing the first page. Processing the first page is also storing the found urls.
     * In the next round, you will start queuing the urls already stored in the database.
     */
    private function startCrawler($continueCrawler = false)
    {
        /*$req = new HTTP_Request("http://example.com/");
         $req->setProxy("192.168.5.254", 3128);*/
        $log = new WLog();
        $log->m_log('Start WebCrawler');
        
        // Define Seed Settings
        $seed_url = trim($_POST['url']);
        $seed_components = parse_url($seed_url);
        if ($seed_components === false) {
            die('Unable to Seed Parse URL');
        }

        if (!isset($seed_components['scheme'])) return false;
        $seed_scheme = $seed_components['scheme'];
        $seed_host = $seed_components['host'];
        if (isset($_POST['match_url']) && $_POST['match_url'] != '') {
            $url_start = trim($_POST['match_url']);
        } else {
            $url_start = $seed_scheme . '://' . $seed_host;
        }
        // Download Seed URL
        $parsePage = new ParsePage();
        $parsePage->referer = "/";
        
        if (!isset($seed_components['path'])) {
            $parsePage->target = $seed_url . '/';
            $parsePage->path = '/';
        } else {
            $parsePage->target = $seed_url;
            $parsePage->path = $seed_components['path'];
        }
        //  $parsePage->path =$seed_components['path'];
        $this->MySql = new DbMysql();
        $this->MySql->exitsUrl($parsePage->target);
        if ($this->MySql->result && !empty($this->MySql->result) && $continueCrawler === false) {
            $this->MySql->deleteWebPageData($this->MySql->result['id']);
        }
        
        // Puppeteer preparing
        $puppeteer = new Puppeteer([
            'idle_timeout' => 300,
            'read_timeout' => 300,
            'args' => ['--disable-dev-shm-usage']
        ]);
        $browser = $puppeteer->launch([
            'args' => [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36'
            ]
        ]);
        
        
        
        $parsePage->browser = $browser;
        
        $parsePage->pagesId = NULL;
        if ($continueCrawler === false) {
            $parsePage->firstCheck = $this->firstCheck;
            $parsePage->parsePage(true);
            $this->urlId = $parsePage->urlId;
            $this->firstCheck = $parsePage->firstCheck;
        }
        // Loop through all pages on site.
        while (1) {
            $counter = 0;
            $rowCount = $this->MySql->getLinks();   
            if ($rowCount) {
                for ($i = 0; $i < $rowCount; $i ++) {
                    $this->MySql->urlId = $this->urlId;
                    if($this->MySql->checkStop() === true) {
                        $this->stop = true;
                        break;
                    }
                    $row = $this->MySql->getLinkRow();
                    if ($row !== false) {
                        $path = $row['path'];
                        $referer = $row['path'];
                        $parsePage = new ParsePage();
                        $parsePage->urlId = $this->urlId;
                        $parsePage->firstCheck = $this->firstCheck;
                        //Check if first character isn't a '/'
                        
                        /* List of urls that should be handled differently from the general one */
                        if ($path[0] != '/') {
                            if (strpos($row['path'], 'https://www.youtube.com') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], 'www.youtube.com') !== false) {
                                $parsePage->target = 'https://' . $row['path'];
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], 'https://prezi.com') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], 'mailchimp.com') !== false || strpos($row['path'], 'https://mailchi.mp') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], 'youtu.be') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], '.vimeo.com') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], '.spotify.com') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], 'www.google.com/maps') !== false || strpos($row['path'], 'maps.google.com') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], 'https://smart-akis.com/SFCPPortal') !== false ) {
                                $parsePage->target = $path;
                                $parsePage->referer = $referer;
                                $parsePage->path = $path;
                            } elseif (substr($row['path'], 0, 3) === 'app') {
                                $parsePage->target = 'https://smart-akis.com/SFCPPortal/' . $row['path'];
                                $parsePage->referer = 'https://smart-akis.com/SFCPPortal/#/app-h' . $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'], 'https://www.smart-akis.com/wp-content/uploads/') !== false ) {
                                $parsePage->target = $path;
                                $parsePage->referer = $referer;
                                $parsePage->path = $path;
                            } elseif (strpos($row['path'],'https://4d4f.eu/') !== false ) { 
                                $parsePage->target = $path;
                                $parsePage->referer = $referer;
                                $parsePage->path = $path;
                            }
                            /*elseif (strpos($row['path'], 'https://www.google.com'))
                            $parsePage->target = $path;
                            $parsePage->referer = $url_start . $referer;
                            $parsePage->path = $path;
                            }*/else {
                            $this->MySql->path = $path;
                            $this->MySql->statusSave();
                            $log->m_log('Unknown URL, Failed to process: ' . $path);
                            continue;
                            }
                        } else {
                            $parsePage->target = $url_start . $path;
                            $parsePage->referer = $url_start . $referer;
                            $parsePage->path = $path;
                        }
                        $parsePage->pagesId = $row['id'];
                        
                        $parsePage->browser = $browser;
                        if ($parsePage->parsePage()) {
                            $this->firstCheck = $parsePage->firstCheck;
                            $counter ++;
                        }
                        sleep(1);
                    }
                }
            } else {
                //die("Unable to select un-downloaded pages\n");
            }
            if ($counter == 0) {
                $_SESSION['processing'] = 0;
                break;
            }
            $_SESSION['processing'] = 0;
        }
    }
    
    /**
     * Query download status
     */
    public function status()
    {   
        $this->getDownloadStatus();
        $this->checkHtml();
        if (isset($_SESSION['processing']) && $_SESSION['processing'] === 1) {
            echo json_encode(array('status' => 1, 'html' => $this->htmlResult) );
        } else {
            echo json_encode(array('status' => 0, 'html' => $this->htmlResult) );
        }
    }
    
    /**
     * Query download status and set values.
     */
    private function getDownloadStatus(){
        $this->MySql->getUrls();
        if ($this->MySql->resultUrl && !empty($this->MySql->resultUrl)) {
            foreach ($this->MySql->resultUrl as $value) {
                $this->domainData[$value['id']] = $value;
                $this->MySql->countFileElement($value['id']);
                $this->downlodedResult[$value['id']] = $this->MySql->result;
                $this->MySql->percentage($value['id']);
                $this->percentage[$value['id']] = $this->MySql->result2;
                $this->calculate($value['id']);
            }
        }
    }
    
    /**
     * Compile the HTML display required for statistics
     */
    private function checkHtml()
    {
        $this->htmlResult = '<h2>DOWNLOAD STATISTICS</h2><table class="table table-striped table-light"><thead class="thead-dark">
        <tr>
        <th class="text-center" scope="col" colspan="4"></th>
        <th class="text-center" scope="col" colspan="5">Text</th>
        <th class="text-center" scope="col" colspan="2">Image</th>
        <th class="text-center" scope="col" colspan="1">Presentation</th>
        <th class="text-center" scope="col" colspan="3">Video</th>
        <th class="text-center" scope="col" colspan="2">Other</th>
        </tr>
        <tr>
        <th scope="col"></th>
        <th scope="col">URL</th>
        <th scope="col">DOMAIN</th>
        <th scope="col">PAGE</th>
        <th scope="col">PDF</th>
        <th scope="col">SWF</th>
        <th scope="col">DOCX</th>
        <th scope="col">XLSX</th>
        <th scope="col">EPUB</th>
        <th scope="col">JPG</th>
        <th scope="col">BMP</th>
        <th scope="col">PNG</th>
        <th scope="col">PPTX</th>
        <th scope="col">MP4</th>
        <th scope="col">YOUTUBE</th>
        <th scope="col">VIMEO</th>
        <th scope="col">GOOGLE MAPS</th>
        <th scope="col">ZIP</th>
        </tr>
        </thead><tbody>';
        //print_r($this->downlodedResult);
        if (isset($this->downlodedResult) && !empty($this->downlodedResult)) {
            foreach ($this->downlodedResult as $key => $value) {
                $this->htmlResult .= '<tr>
                <td scope="row">QUANTITY</td>';
                if ($this->domainData[$key]['download'] == 0) {
                    $dtemp = '<div class="spinner-grow spinner-grow-sm" role="status"><span class="sr-only"></span></div>(Downloading) ';
                } else {
                    $dtemp = '(Finished) ';
                }
                $this->htmlResult .= '<td scope="row">' . $dtemp .$this->domainData[$key]['url'] . '</td>
                <td scope="row">' .$this->domainData[$key]['wname'] . '</td>
                <td scope="row">' . $value['page'] . '</td>
                <td scope="row">' . $value['pdf'] . '</td>
                <td scope="row">' . $value['swf'] . '</td>
                <td scope="row">' . $value['docx'] . '</td>
                <td scope="row">' . $value['xlsx'] . '</td>
                <td scope="row">' . $value['epub'] . '</td>
                <td scope="row">' . $value['jpg'] . '</td>
                <td scope="row">' . $value['bmp'] . '</td>
                <td scope="row">' . $value['png'] . '</td>
                <td scope="row">' . $value['pptx'] . '</td>
                <td scope="row">' . $value['mp4'] . '</td>
                <td scope="row">' . $value['youtube_video'] . '</td>
                <td scope="row">' . $value['vimeo_video'] . '</td>
                <td scope="row">' . $value['google_map'] . '</td>
                <td scope="row">' . $value['zip'] . '</td>';
                $this->htmlResult .= '</tr>';
                
                
                $this->htmlResult .= '<tr>
                <td scope="row">METADATA &nbsp; AVAILABILITY</td>
                <td scope="row">' . $this->domainData[$key]['url'] . '</td>
                <td scope="row">' . $this->domainData[$key]['wname'] . '</td>
                <td scope="row">' . $this->calculated[$key]['page'] . '</td>
                <td scope="row">' . $this->calculated[$key]['pdf'] . '</td>
                <td scope="row">' . $this->calculated[$key]['swf'] . '</td>
                <td scope="row">' . $this->calculated[$key]['docx'] . '</td>
                <td scope="row">' . $this->calculated[$key]['xlsx'] . '</td>
                <td scope="row">' . $this->calculated[$key]['epub'] . '</td>
                <td scope="row">' . $this->calculated[$key]['jpg'] . '</td>
                <td scope="row">' . $this->calculated[$key]['bmp'] . '</td>
                <td scope="row">' . $this->calculated[$key]['png'] . '</td>
                <td scope="row">' . $this->calculated[$key]['pptx'] . '</td>
                <td scope="row">' . $this->calculated[$key]['mp4'] . '</td>
                <td scope="row">' . $this->calculated[$key]['youtube_video'] . '</td>
                <td scope="row">' . $this->calculated[$key]['vimeo_video'] . '</td>
                <td scope="row">' . $this->calculated[$key]['zip'] .'</td>
                <td scope="row">' . $this->calculated[$key]['google_map'] . '<br>(link only)</td>';
                $this->htmlResult .= '</tr>';
            }
        }
        $this->htmlResult .= '</tbody></table>';
    }
    
    /**
     * Calculates values for statistics.
     * @param int $id url ID
     */
    private function calculate($id)
    {
        foreach ($this->fileTypes as $item) {
            if (array_key_exists($item, $this->downlodedResult[$id]) === false) {
                $this->downlodedResult[$id][$item] = 0;
                $this->percentage[$id][$item] = 0;
            }
        }
        foreach ($this->downlodedResult[$id] as $key => $value) {
            if (isset($this->percentage[$id][$key]) && $this->percentage[$id][$key] > 0 && $value > 0) {
                $this->calculated[$id][$key] = round($this->percentage[$id][$key] / $value * 100) . '%';
            } else {
                $this->calculated[$id][$key] = 0;
            }
        }
    }
    
    /**
     * Verifies that it exists and already creates a URL
     */
    public function checkUrl()
    {
        $this->changePost();
        //$_POST['url']
        $this->MySql->exitsUrl($_POST['url']);
        if ($this->MySql->result && !empty($this->MySql->result)) {
            echo json_encode(array('statusurl' => 1));
        } else {
            echo json_encode(array('statusurl' => 0));
        }
    }
}

$ajaxProcess = new AjaxProcess();
if (isset($_POST['processFunction']) && $_POST['processFunction'] == 'startcrawler') {
    $ajaxProcess->process();
} elseif (isset($_POST['processFunction']) && $_POST['processFunction'] == 'status') {
    $ajaxProcess->status();
} elseif (isset($_POST['processFunction']) && $_POST['processFunction'] == 'checkUrl') {
    $ajaxProcess->checkUrl();
} elseif (isset($_POST['processFunction']) && $_POST['processFunction'] == 'continue') {
    
}
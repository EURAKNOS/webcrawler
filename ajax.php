<?php
session_start ();
session_write_close();
require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Log.php';
set_time_limit (10000);

class AjaxProcess {
    
    public $urlId;
    
    public function __construct()
    {
        $this->MySql = new DbMysql();
    }
    
    public function process()
    {
        $log = new WLog();
        $log->m_log('Load WebCrawler page');
        $this->changePost();
        $_SESSION["processing"] = 1;
        if($this->startCrawler()) {
            echo json_encode(array('status' => 1) );
        } else {
            echo json_encode(array('status' => 0) );
        }
        $this->MySql->endDownloadUrl($this->urlId);
     }

     private function changePost()
     {    
         $temp = $_POST['formdata'];
         $cnt = 1;
         foreach ($temp as $item) {
             if (strpos($item['name'], 'class[') === false) {
                 $formdata[$item['name']] = $item['value'];
             } else {
                 if (strpos($item['name'], '[name]') !== false) {
                 //$formdata['class'][] = array('name' => $item['value'], 'title' => $item[''];
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
    private function startCrawler()
    {
        /*$req = new HTTP_Request("http://example.com/");
         $req->setProxy("192.168.5.254", 3128);*/
        $log = new WLog();
        $log->m_log('Start WebCrawler');
        
        // Define Seed Settings
        $seed_url = $_POST['url'];
        $seed_components = parse_url($seed_url);
        if ($seed_components === false) {
            die('Unable to Seed Parse URL');
        }
        if (!isset($seed_components['scheme'])) return false;
        $seed_scheme = $seed_components['scheme'];
        $seed_host = $seed_components['host'];
        $url_start = $seed_scheme . '://' . $seed_host;
        
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
        if ($this->MySql->result && !empty($this->MySql->result)) {
            $this->MySql->deleteWebPageData($this->MySql->result['id']);
        }
        $parsePage->parsePage(true);
        $this->urlId = $parsePage->urlId;
        // Loop through all pages on site.
        while (1) {
            $counter = 0;
            $rowCount = $this->MySql->getLinks();
            if ($rowCount) {
                for ($i = 0; $i < $rowCount; $i ++) {
                    $row = $this->MySql->getLinkRow();
                    if ($row !== false) {
                        $path = $row['path'];
                        $referer = $row['path'];
                        $parsePage = new ParsePage();
                        $parsePage->urlId = $this->urlId;
                        //Check if first character isn't a '/'
                        if ($path[0] != '/') {
                            if (strpos($row['path'], 'https://www.youtube.com') !== false) {
                                $parsePage->target = $path;
                                $parsePage->referer = $url_start . $referer;
                                $parsePage->path = $path;
                            } /*elseif (strpos($row['path'], 'https://www.google.com'))
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
                        
                        
                        if ($parsePage->parsePage()) {
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
    
    public function status()
    {   $this->getDownloadStatus();
        $this->checkHtml();
        if (isset($_SESSION['processing']) && $_SESSION['processing'] === 1) {
            echo json_encode(array('status' => 1, 'html' => $this->htmlResult) );
        } else {
            echo json_encode(array('status' => 0, 'html' => $this->htmlResult) );
        }
    }
    
    
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
        
    private function checkHtml()
    {
        $this->htmlResult = '<h2>DOWNLOAD STATISTICS</h2><table class="table table-striped table-light"><thead class="thead-dark"><tr>
        <th scope="col"></th>
        <th scope="col">URL</th>
        <th scope="col">DOMAIN</th>
        <th scope="col">PAGE</th>
        <th scope="col">PDF</th>
        <th scope="col">JPG</th>
        <th scope="col">PNG</th>
        <th scope="col">WORD</th>
        <th scope="col">EXCEL</th>
        <th scope="col">POWERPOINT</th>
        <th scope="col">YOUTUBE</th>
        </tr>
        </thead><tbody>';
        if (isset($this->downlodedResult) && !empty($this->downlodedResult)) {
            foreach ($this->downlodedResult as $key => $value) {
                $this->htmlResult .= '<tr>
                <td scope="row">QUANTITY</td>';
                if ($this->domainData[$key]['download'] == 0) {
                    $dtemp = '<div class="spinner-grow spinner-grow-sm" role="status"><span class="sr-only"></span></div>(Download) ';
                } else {
                    $dtemp = '(Finish) ';
                }
                $this->htmlResult .= '<td scope="row">' . $dtemp .$this->domainData[$key]['url'] . '</td>
                <td scope="row">' .$this->domainData[$key]['wname'] . '</td>
                <td scope="row">' . $value['page'] . '</td>
                <td scope="row">' . $value['pdf'] . '</td>
                <td scope="row">' . $value['jpg'] . '</td>
                <td scope="row">' . $value['png'] . '</td>
                <td scope="row">' . $value['docx'] . '</td>
                <td scope="row">' . $value['xlsx'] . '</td>
                <td scope="row">' . $value['pptx'] . '</td>
                <td scope="row">' . $value['youtube_video'] . '</td>';
                $this->htmlResult .= '</tr>';
                
                
                $this->htmlResult .= '<tr>
                <td scope="row">METADATA &nbsp; AVAILABILITY</td>
                <td scope="row">' . $this->domainData[$key]['url'] . '</td>
                <td scope="row">' . $this->domainData[$key]['wname'] . '</td>
                <td scope="row">' . $this->calculated[$key]['page'] . '</td>
                <td scope="row">' . $this->calculated[$key]['pdf'] . '</td>
                <td scope="row">' . $this->calculated[$key]['jpg'] . '</td>
                <td scope="row">' . $this->calculated[$key]['png'] . '</td>
                <td scope="row">' . $this->calculated[$key]['docx'] . '</td>
                <td scope="row">' . $this->calculated[$key]['xlsx'] . '</td>
                <td scope="row">' . $this->calculated[$key]['pptx'] . '</td>
                <td scope="row">' . $this->calculated[$key]['youtube_video'] . '</td>';
                $this->htmlResult .= '</tr>';
            }
        }
        $this->htmlResult .= '</tbody></table>';
    }
    
    private function calculate($id)
    {
        foreach ($this->downlodedResult[$id] as $key => $value) {
            if ($this->percentage[$id][$key] > 0 && $value > 0) {
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
}


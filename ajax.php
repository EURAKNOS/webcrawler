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
            
        } else {
            echo json_encode(array('status' => 0) );
        }
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
        $parsePage->parsePage();
        // Loop through all pages on site.
        
        $this->MySql = new DbMysql();
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
        $this->MySql->countFileElement();
        $this->downlodedResult = $this->MySql->result;
        $this->MySql->percentage();
        $this->percentage = $this->MySql->result2;
        $this->calculate();
    }
        
    private function checkHtml()
    {
        $this->htmlResult = '<h2>DOWNLOAD STATISTICS</h2><table class="table table-striped table-dark"><thead><tr>
        <th scope="col"></th>        
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
        $this->htmlResult .= '<tr>
        <td scope="row">QUANTITY</td>
        <td scope="row">' . $this->downlodedResult['page'] . '</td>
        <td scope="row">' . $this->downlodedResult['pdf'] . '</td>
        <td scope="row">' . $this->downlodedResult['jpg'] . '</td>
        <td scope="row">' . $this->downlodedResult['png'] . '</td>
        <td scope="row">' . $this->downlodedResult['docx'] . '</td>
        <td scope="row">' . $this->downlodedResult['xlsx'] . '</td>
        <td scope="row">' . $this->downlodedResult['pptx'] . '</td>
        <td scope="row">' . $this->downlodedResult['youtube_video'] . '</td>';
        $this->htmlResult .= '</tr>';
        
        $this->htmlResult .= '<tr>
        <td scope="row">METADATA AVAILABILITY</td>
        <td scope="row">' . $this->calculated['page'] . '</td>
        <td scope="row">' . $this->calculated['pdf'] . '</td>
        <td scope="row">' . $this->calculated['jpg'] . '</td>
        <td scope="row">' . $this->calculated['png'] . '</td>
        <td scope="row">' . $this->calculated['docx'] . '</td>
        <td scope="row">' . $this->calculated['xlsx'] . '</td>
        <td scope="row">' . $this->calculated['pptx'] . '</td>
        <td scope="row">' . $this->calculated['youtube_video'] . '</td>';
        $this->htmlResult .= '</tr>';
        
        $this->htmlResult .= '</tbody></table>';
    }
    
    private function calculate()
    {
        foreach ($this->downlodedResult as $key => $value) {
            if ($this->percentage[$key] > 0 && $value > 0) {
                $this->calculated[$key] = round($this->percentage[$key] / $value * 100) . '%';
            } else {
                $this->calculated[$key] = 0;
            }
            
        }
    }
}

$ajaxProcess = new AjaxProcess();
if (isset($_POST['processFunction']) && $_POST['processFunction'] == 'startcrawler') {
    $ajaxProcess->process();
} elseif (isset($_POST['processFunction']) && $_POST['processFunction'] == 'status') {
    $ajaxProcess->status();
}


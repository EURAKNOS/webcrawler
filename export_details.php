<?php
session_start ();
error_reporting(E_ALL);
require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Detail.php';
/**
 * Export current web page from the details page or all page on mainpage.
 * The contents of the page display the number of files and statistics in an excel file.
 * @author szabo
 *
 */
class DetailsExport {
    
  
    public $data = array();
    
    public $result = array();
  
    
    public function __construct()
    {
        $this->MySql = new DbMysql();
        $this->javascriptPlus = array(
            'page' => 'PAGE',
            'pdf' => 'PDF',
            'docx' => 'DOCX',
            'xlsx' => 'XLSX',
            'epub' => 'EPUB',
            'pptx' => 'PPTX',
            'prezi' => 'PREZI',
            'jpg' => 'JPG',
            'bmp' => 'BMP',
            'png' => 'PNG',
            'swf' => 'SWF',
            'svg' => 'SVG',
            'youtube_video' => 'YOUTUBE (link)',
            'vimeo_video' => 'VIMEO (link)',
            'spotify' => 'SPOTIFY (link)',
            'mp4' => 'MP4',
            'google_map' => 'GOOGLE MAPS (link)',
            'zip' => 'ZIP',
            'mailchimp' => 'MAILCHIMP'
        );
    }
    
    /**
     * Start exporting
     */ 
    public function exportProcess()
    {
        $this->cnt = 0;
        if (isset($_GET['id'])) {
            $this->getDataById($_GET['id']);
        } else {
            $this->createAllPage();
        }
        $this->createExcel();
    }
    
    private function createAllPage()
    {
        $this->MySql->getAllUrlWithoutDelete();
        foreach ($this->MySql->resultUrls as $item) {
            $this->getDataById($item['id']);
            $this->cnt++;
        }
    }
    
    /**
     * Retrieve page information
     * @param int $id
     */
    private function getDataById($id)
    {
        $this->getDataByUrlId($id);
        $this->getDownloadStatisticsByUrlId($id);
        $this->prepareData();
    }
    
    /**
     * Retrieve page information
     * @param int $id
     */
    private function getDataByUrlId($id)
    {
        $this->mainData = array();
        $this->MySql->getCrawlingData($id);
        $this->mainData = $this->MySql->result;
        $this->mainData['post'] = unserialize($this->mainData['post_data']);
        $this->mainData['run'] = $this->hourAndMinConverter($this->mainData['end_time'] - $this->mainData['download_time']);
        $this->mainData['post']['match_url'] = (isset($this->mainData['post']['match_url'])) ? $this->mainData['post']['match_url'] : '';
        $this->mainData['post']['class'] = (isset($this->mainData['post']['class'])) ? $this->mainData['post']['class'] : array();
    }
    
    /**
     * Generate download statistics
     */
    public function getDownloadStatisticsByUrlId($id)
    {
        $this->statistics = array();
        $this->statistics1 = array();
        $this->statistics2 = array();
        
        $this->MySql->countFileElement($id);
        $this->MySql->percentage($id);

        foreach ($this->MySql->result as $key => $item) {
            $this->statistics[$key]['all'] = $item;
            if (isset( $this->MySql->result2[$key])) {
                $this->statistics[$key]['meta'] = $this->MySql->result2[$key];
            } else {
                $this->statistics[$key]['meta'] = 0;
            }
            if (isset($this->MySql->result2[$key])) {
                $this->statistics[$key]['percentage'] = $this->percentageAllPage($item, $this->MySql->result2[$key]);
            } else {
                $this->statistics[$key]['percentage'] = 0;
            }
        }
        
        foreach ($this->javascriptPlus as $key => $value) {
            if (isset($this->statistics[$key]['all']) && $this->statistics[$key]['all']) {
                $this->statistics1[$key]['all'] = $this->statistics[$key]['all'];
                $this->statistics1[$key]['name'] = $value;
            }
            if (isset($this->statistics[$key]['meta']) && $this->statistics[$key]['meta'] != 0) {
                $this->statistics2[$key]['percentage'] = $this->statistics[$key]['percentage'];
                $this->statistics2[$key]['name'] = $value;
            }
        }
    
    }
    
    /**
     * Calculate
     * @param int $all
     * @param int $meta
     * @return number
     */
    private function percentageAllPage($all, $meta)
    {
        if ($all > 0 && $meta > 0) {
            return round($meta / $all * 100);
        } else {
            return 0;
        }
    }
    
    /**
     * 
     * @param int $time
     * @return string
     */
    private function hourAndMinConverter($time)
    {
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        
        return $hours . "h " . $minutes . "min ";
    }
    
    /**
     * Downloaded datat prepare from database
     */
    private function prepareData()
    {
        $this->edata[$this->cnt] = array('webpage_data' => 'QUANTITY', 'url' => $this->cleanData($this->mainData['url']), 
            'domain' => $this->cleanData($this->mainData['wname']));
        foreach ($this->javascriptPlus as $key => $value) {
            
            if (isset($this->statistics1[$key]['all'])) {
                $this->edata[$this->cnt][$key] = $this->statistics1[$key]['all'];
            } else {
                $this->edata[$this->cnt][$key] = 0;
            }
        }
        $this->cnt++;
        $this->edata[$this->cnt] = array('webpage_data' => 'METADATA AVAILABILITY','url' => '',
            'domain' => '');
        foreach ($this->javascriptPlus as $key => $value) {
            if (isset($this->statistics2[$key]['percentage'])) {
                $this->edata[$this->cnt][$key] = $this->statistics2[$key]['percentage'] . '%';
            } else {
                $this->edata[$this->cnt][$key] = 0;
            }
        }
        
    }
    
    
    private function cleanData(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }
    
    /**
     * Create excel file 
     */
    private function createExcel() 
    {
    // filename for download
        $filename = "website_data_" . date('Ymd') . ".xls";
        
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        $flag = false;
        foreach($this->edata as $row) {
            if(!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            echo implode("\t", array_values($row)) . "\r\n";
        }
        exit;
    }
     
}

$export = new DetailsExport();
$export->exportProcess();
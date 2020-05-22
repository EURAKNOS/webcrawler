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
            'jpg' => 'JPG',
            'png' => 'PNG',
            'swf' => 'SWF',
            'svg' => 'SVG',
            'youtube_video' => 'YOUTUBE',
            'vimeo_video' => 'VIMEO',
            'mp4' => 'MP4',
            'google_map' => 'GOOGLE MAPS',
            'zip' => 'ZIP'
        );
    }
    
  
    public function exportProcess()
    {
        $this->getDataById();
    }
    
    private function getDataById()
    {
        $this->getDataByUrlId();
        $this->getDownloadStatisticsByUrlId($_GET['id']);
        $this->prepareData();
        $this->createExcel();
    }
        
    private function getDataByUrlId()
    {
        $this->MySql->getCrawlingData($_GET['id']);
        $this->mainData = $this->MySql->result;
        $this->mainData['post'] = unserialize($this->mainData['post_data']);
        $this->mainData['run'] = $this->hourAndMinConverter($this->mainData['end_time'] - $this->mainData['download_time']);
        $this->mainData['post']['match_url'] = (isset($this->mainData['post']['match_url'])) ? $this->mainData['post']['match_url'] : '';
        $this->mainData['post']['class'] = (isset($this->mainData['post']['class'])) ? $this->mainData['post']['class'] : array();
    }
    
    public function getDownloadStatisticsByUrlId($id)
    {
        $this->MySql->countFileElement($id);
        $this->MySql->percentage($id);

        foreach ($this->MySql->result as $key => $item) {
            $this->statistics[$key]['all'] = $item;
            $this->statistics[$key]['meta'] = $this->MySql->result2[$key];
            $this->statistics[$key]['percentage'] = $this->percentageAllPage($item, $this->MySql->result2[$key]);
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
    
    private function percentageAllPage($all, $meta)
    {
        if ($all > 0 && $meta > 0) {
            return round($meta / $all * 100);
        } else {
            return 0;
        }
    }
    
    private function hourAndMinConverter($time)
    {
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        
        return $hours . "h " . $minutes . "min ";
    }
    
    private function prepareData()
    {
        $this->edata[0] = array('webpage_data' => 'QUANTITY', 'url' => $this->cleanData($this->mainData['url']), 
            'domain' => $this->cleanData($this->mainData['wname']));
        foreach ($this->javascriptPlus as $key => $value) {
            
            if (isset($this->statistics1[$key]['all'])) {
                $this->edata[0][$key] = $this->statistics1[$key]['all'];
            } else {
                $this->edata[0][$key] = 0;
            }
        }
        
        $this->edata[1] = array('webpage_data' => 'METADATA AVAILABILITY','url' => '',
            'domain' => '');
        foreach ($this->javascriptPlus as $key => $value) {
            if (isset($this->statistics2[$key]['percentage'])) {
                $this->edata[1][$key] = $this->statistics2[$key]['percentage'];
            } else {
                $this->edata[1][$key] = 0;
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
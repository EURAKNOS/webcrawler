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

class MetaExport {
    
  
    public $data = array();
    
    public $result = array();
  
    private $metadata = array();
    
    public function __construct()
    {
        $this->MySql = new DbMysql();
    }
    
  
    public function exportProcess()
    {
        $this->cnt = 0;
        $this->excelHeader();
        if (isset($_GET['id'])) {
            $this->getDataById($_GET['id']);
            $this->createExcel();
        } else {
            $this->createAllPage();
        }
        exit;
    }
    
    private function createAllPage()
    {
        $this->MySql->getAllUrlWithoutDelete();
        foreach ($this->MySql->resultUrls as $item) {
            $this->getDataById($item['id']);
            if (!empty($this->meta)) {
                echo $item['wname'] . "\r\n";
                $this->createExcel();
                echo "\r\n";
            }
        }
    }
    
    private function getDataById($id)
    {
        $this->getDataByUrlId($id);
        $this->getDownloadMetaByUrlId($id);
        if (!empty($this->meta)) {
            $this->metaDataSettlement($id);
        }
    }
        
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
    
    public function getDownloadMetaByUrlId($id)
    {
        //echo '<pre>';
        $data = $this->MySql->getAllFilesMetaByUrlId($id);
       
        $content = $this->MySql->getMetaContent($id);
        $this->meta = array();
        $this->metadata = array();
        $this->cnta = array();
        
        if (isset($data) && !empty($data)) {
            foreach ($data as $key => $item) {
                
                if (!isset($this->metadata[$item['file_type']])) {
                    $this->cnta[$item['file_type']] = 1;
                    $this->meta[$item['file_type']] = array();
                    $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]] = array();
                }
                $meta = array();
                if ( $item['meta_data'] != '' ) {
                    $meta = unserialize($item['meta_data']);
                }
                $this->meta[$item['file_type']][strtoupper('ID')] = strtoupper('ID');
                $this->meta[$item['file_type']][strtoupper('URL_ID')] = strtoupper('URL_ID');
                $this->meta[$item['file_type']][strtoupper('PATH')] = strtoupper('PATH');
                $this->meta[$item['file_type']][strtoupper('LOCATION')] = strtoupper('LOCATION');
                $this->meta[$item['file_type']][strtoupper('SIZE')] = strtoupper('SIZE');
                
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['ID'] = $item['id'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['URL_ID'] = $item['url_id'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['PATH'] = $item['path'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['LOCATION'] = $item['local_location'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['SIZE'] = filesize($item['local_location']) . ' byte';
                
                foreach ($meta as $keyMeta => $value) {
                    if (!isset($this->meta[$item['file_type']][strtoupper($keyMeta)])) {
                        $this->meta[$item['file_type']][strtoupper($keyMeta)] = strtoupper($keyMeta);
                    }
                    if (is_array($value)) {
                        $this->arrayMetaProcess($value, $item['file_type']);
                    } else {
                        $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]][strtoupper($keyMeta)] = $this->cleanData($value);
                    }
                }
                $this->cnta[$item['file_type']]++;
            }
        }
        
        
        if (isset($content) && !empty($content)) {
            foreach ($content as $key => $item) {
                
                if (!isset($this->metadata['content'])) {
                    $this->cnta['content'] = 1;
                    $this->meta['content'] = array();
                    $this->metadata['content'][$this->cnta['content']] = array();
                }
                $meta = array();
                if ( $item['content'] != '' ) {
                    $meta = unserialize($item['content']);
                }
                $this->meta['content'][strtoupper('ID')] = strtoupper('ID');
                $this->meta['content'][strtoupper('URL_ID')] = strtoupper('URL_ID');
                $this->meta['content'][strtoupper('PAGE')] = strtoupper('PAGE');
                $this->meta['content'][strtoupper('PATH')] = strtoupper('PATH');
                
                $this->metadata['content'][$this->cnta['content']]['ID'] = $item['id'];
                $this->metadata['content'][$this->cnta['content']]['URL_ID'] = $item['url_id'];
                $this->metadata['content'][$this->cnta['content']]['PAGE'] = $item['page'];
                $this->metadata['content'][$this->cnta['content']]['PATH'] = $item['path'];
                
                foreach ($meta as $keyMeta => $value) {
                    if (!isset($this->meta['content'][strtoupper($keyMeta)])) {
                        $this->meta['content'][strtoupper($keyMeta)] = strtoupper($keyMeta);
                    }
                    if (is_array($value)) {
                        $this->arrayMetaProcess($value, 'content');
                    } else {
                        $this->metadata['content'][$this->cnta['content']][strtoupper($keyMeta)] = $this->cleanData($value);
                    }
                }
                $this->cnta['content']++;
            }
        }
        
        
    }
    
    private function arrayMetaProcess($value, $filetype, $prefix = '') 
    {
        foreach ($value as $keyMeta2 => $meta) {
            if (is_array($meta)) {
                $this->arrayMetaProcess($meta, $filetype, $keyMeta2 . '_');
            } else {
                if (!isset($this->meta[$filetype][strtoupper($keyMeta2)])) {
                    $this->meta[$filetype][strtoupper(strtoupper($prefix . $keyMeta2))] = strtoupper($prefix . $keyMeta2);
                }
                $this->metadata[$filetype][$this->cnta[$filetype]][strtoupper($prefix . $keyMeta2)] = $this->cleanData($meta);
            }
        }
    }
    
    private function metaDataSettlement()
    {
        $this->readyMeta = array();
        foreach ($this->meta as $type => $metaPack) {   
            foreach ($metaPack as $metaKey => $metavalue) {
                foreach ($this->metadata[$type] as $key => $value) {
                    
                    if (isset($value[$metaKey]) ) {
                        $this->readyMeta[$type][$key][$metaKey] = $value[$metaKey];
                    } else {
                        $this->readyMeta[$type][$key][$metaKey] = ' ';
                    }
                };
            };
        }
        
        //print_r($this->readyMeta);
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
  
    
    private function cleanData(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }
    
    private function excelHeader()
    {
        $filename = "meta_data_" . date('Ymd') . ".xls";
        
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
    }
    
    private function createExcel() 
    {
        
        foreach($this->readyMeta as $type => $metaPack) {
            $flag = false;
            echo $type . "\r\n";
            foreach ($metaPack as $row) {
                if(!$flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }
            echo "\r\n";
        }
        
    }
     
}

$export = new MetaExport();
$export->exportProcess();
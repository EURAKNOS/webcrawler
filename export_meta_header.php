<?php
session_name('meta');

ini_set('session.use_only_cookies', false);
ini_set('session.use_cookies', false);
ini_set('session.use_trans_sid', false);
ini_set('session.cache_limiter', null);

if (array_key_exists('PHPSESSID', $_COOKIE))
    session_id($_COOKIE['PHPSESSID']);
else {
    session_start();
    setcookie('PHPSESSID', session_id());
    session_write_close();
}

error_reporting(E_ALL);
set_time_limit(500000);

require_once 'config.php';
require_once 'MySQL.php';
require_once 'Log.php';

class MetaHeaderExport
{

    public $data = array();

    public $result = array();

    public $start;
    
    public $type;
    
    public $readyHeader = array();
    
    public $readyMeta = array();

    private $metadata = array();

    public function __construct()
    {
        $this->type = $_GET['type'];
        $this->start = microtime(true);
        $this->MySql = new DbMysql();
    }
    
    public function getDataMetaTitleByType()
    {
        $this->result = $this->MySql->getDataMetaTitleFilesByType($this->type);
        $this->processingArra();

        $this->excelHeader();
        $this->createExcel();
    }
    
    private function processingArra()
    {
        $this->readyHeader[strtoupper('url')] = 'url';
        $this->readyHeader[strtoupper('sitename')] = 'sitename';
        $this->readyHeader[strtoupper('path')] = 'path';
        $this->readyHeader[strtoupper('location')] = 'location';
        
        foreach ($this->result as $key => &$value) {
            $this->readyMeta[$key][strtoupper('url')] = $this->cleanData($value['url']);
            $this->readyMeta[$key][strtoupper('sitename')] = $this->cleanData($value['wname']);
            $this->readyMeta[$key][strtoupper('path')] = $this->cleanData($value['path']);
            $this->readyMeta[$key][strtoupper('local_location')] = $this->cleanData($value['local_location']);
            $value['ok'] = unserialize($value['meta_data']);
            if ($value['ok']) {
                foreach ($value['ok'] as $keyMeta => $item) {
                    if (is_array($item)) {
                        $this->arrayMetaProcess($value, $key);
                    } else {
                        if (!isset($this->readyHeader[strtoupper($keyMeta)])) {
                            $this->readyHeader[strtoupper($keyMeta)] = $keyMeta;
                        }
                        $this->readyMeta[$key][strtoupper($keyMeta)] = $this->cleanData($item);
                    }
                }
            }
        }
    }
    
    private function arrayMetaProcess($value, $key, $prefix = '')
    {
        foreach ($value as $keyMeta2 => $meta) {
            if (is_array($meta)) {
                $this->arrayMetaProcess($meta, $key, $keyMeta2 . '_');
            } else {
                if (!isset($this->readyHeader[strtoupper($keyMeta2)])) {
                    $this->readyHeader[strtoupper($keyMeta2)] = $keyMeta2;
                }
                $this->readyMeta[$key][strtoupper($keyMeta2)] = $this->cleanData($meta);
            }
        }
    }

    private function cleanData(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if (strstr($str, '"'))
            $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }

    private function excelHeader()
    {
        $filename = $this->type . "_" . date('Ymd') . ".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
    }

    private function createExcel()
    {
        /*echo '<pre>';
        print_r($this->readyHeader);*/
        
        echo implode("\t", array_keys($this->readyHeader)) . "\r\n";
        
      //  echo $type . "\r\n";
        foreach ($this->readyMeta as $key => $metaPack) {
            
            foreach ($this->readyHeader as $headerKey => $header) {
                if (isset($metaPack[$headerKey])) {
                    echo $metaPack[$headerKey] . "\t";
                } else {
                    echo ' ' . "\t";
                }
                //echo implode("\t", array_values($row)) . "\r\n";
            }
            echo "\r\n";
        }
    }
    private function createExcel2()
    {
        foreach ($this->readyMeta as $type => $metaPack) {
            $flag = false;
            echo $type . "\r\n";
            foreach ($metaPack as $row) {
                if (! $flag) {
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
$export = new MetaHeaderExport();
$export->getDataMetaTitleByType();
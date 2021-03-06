<?php
session_name('meta');

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
    

error_reporting(E_ALL);
set_time_limit (500000);

require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Detail.php';
require_once 'vendor/PhpSpreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class MetaExport {
    
  
    public $data = array();
    
    public $result = array();
    
    public $start;
  
    private $metadata = array();
    
    public function __construct()
    {
        $this->start = microtime(true);
        $this->log = new WLog();
        $this->MySql = new DbMysql();
    }
    
  
    public function exportProcess()
    {
        $this->cnta = array();
        $this->meta = array();
        $this->metadata = array();
        $this->cnt = 0;
        
        session_start();
        $_SESSION['meta_export_check'] = 0;
        $_SESSION['meta_export_file'] = '';
        $_SESSION['meta_all_cnt'] = 0;
        $_SESSION['meta_export_cnt'] = 0;
        session_write_close();
        
        
        if (isset($_POST['id'])) {
            $this->getDataById($_POST['id']);
            $this->filename = $this->mainData['id'] . '_' . date('Y-m-d') . '.xlsx';
            $this->createExcel();
        }
        /*else {
            $this->filename = 'all_' . date('Y-m-d') . '.xlsx';
            $this->createAllPage();
        }*/
        
       /* $this->log->m_log('Memory used: ' . $this->isa_convert_bytes_to_specified(memory_get_peak_usage(), 'M'));
        exit;*/
    }
    
    function isa_convert_bytes_to_specified($bytes, $to, $decimal_places = 1) {
        $formulas = array(
            'K' => number_format($bytes / 1024, $decimal_places),
            'M' => number_format($bytes / 1048576, $decimal_places),
            'G' => number_format($bytes / 1073741824, $decimal_places)
        );
        return isset($formulas[$to]) ? $formulas[$to] : 0;
    }
    
    private function createAllPage()
    {
        $this->MySql->getAllUrlWithoutDelete();
        foreach ($this->MySql->resultUrls as $item) {
            $this->getDataById($item['id']);
        }
       
        if (!empty($this->meta)) {
            $this->createExcel();
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
    }
    
    public function getDownloadMetaByUrlId($id)
    {
        
        $data = $this->MySql->getAllFilesMetaByUrlId($id);
        $content = $this->MySql->getMetaContent($id);
        session_start();
        $_SESSION['meta_all_cnt'] += count($data);
        $_SESSION['meta_all_cnt'] += count($content);
        session_write_close();
        if (isset($data) && !empty($data)) {
            foreach ($data as $key => $item) {
                
                //$referer = $this->getRefererUrl($item['url_id'], $item['path']);
                
                if (!isset($this->metadata[$item['file_type']])) {
                    $this->cnta[$item['file_type']] = 1;
                    $this->meta[$item['file_type']] = array();
                    $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]] = array();
                }
                $meta = array();
                if ( $item['meta_data'] != '' ) {
                    $meta = unserialize($item['meta_data']);
                }
                
                $this->meta[$item['file_type']][strtoupper('PAGE_NAME')] = strtoupper('PAGE NAME');
                $this->meta[$item['file_type']][strtoupper('DOMAIN')] = strtoupper('DOMAIN');
                $this->meta[$item['file_type']][strtoupper('ID')] = strtoupper('ID');
                $this->meta[$item['file_type']][strtoupper('URL_ID')] = strtoupper('URL_ID');
                $this->meta[$item['file_type']][strtoupper('PATH')] = strtoupper('PATH');
                $this->meta[$item['file_type']][strtoupper('REFERER')] = strtoupper('REFERER');
                $this->meta[$item['file_type']][strtoupper('LOCATION')] = strtoupper('LOCATION');
                $this->meta[$item['file_type']][strtoupper('SIZE')] = strtoupper('SIZE');
                
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['PAGE_NAME'] = $this->mainData['wname'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['DOMAIN'] = $this->mainData['url'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['ID'] = $item['id'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['URL_ID'] = $item['url_id'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['PATH'] = $item['path'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['REFERER'] = $item['referer'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['LOCATION'] = $item['local_location'];
                $this->metadata[$item['file_type']][$this->cnta[$item['file_type']]]['SIZE'] = filesize($item['local_location']);
                if (is_array($meta)) {
                
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
                
                $referer = $this->getRefererUrl($item['url_id'], $item['path']);
                $meta = array();
                if ( $item['content'] != '' ) {
                    $meta = unserialize($item['content']);
                }
                $this->meta['content'][strtoupper('PAGE_NAME')] = strtoupper('PAGE NAME');
                $this->meta['content'][strtoupper('DOMAIN')] = strtoupper('DOMAIN');
                $this->meta['content'][strtoupper('ID')] = strtoupper('ID');
                $this->meta['content'][strtoupper('URL_ID')] = strtoupper('URL_ID');
                $this->meta['content'][strtoupper('PAGE')] = strtoupper('PAGE');
                $this->meta['content'][strtoupper('PATH')] = strtoupper('PATH');
                $this->meta['content'][strtoupper('REFERER')] = strtoupper('REFERER');
                
                $this->metadata['content'][$this->cnta['content']]['PAGE_NAME'] =  $this->mainData['wname'];
                $this->metadata['content'][$this->cnta['content']]['DOMAIN'] =  $this->mainData['url'];
                $this->metadata['content'][$this->cnta['content']]['ID'] = $item['id'];
                $this->metadata['content'][$this->cnta['content']]['URL_ID'] = $item['url_id'];
                $this->metadata['content'][$this->cnta['content']]['PAGE'] = $item['page'];
                $this->metadata['content'][$this->cnta['content']]['PATH'] = $item['path'];
                $this->metadata['content'][$this->cnta['content']]['REFERER'] = $referer;
                
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
    
    private function getRefererUrl($id, $path)
    {
        if ($path == '/') return $path;
        $result = $this->MySql->getRefererUrl($id, $path);
        if ($result && !empty($result)) {
            return $result['referer'];
        } else {
            $tpath = parse_url($path);
            //echo '<pre>';
            //print_r($tpath);
            $ppath = str_replace(array($tpath['scheme'] . '://', $tpath['host'] . '/'), array('',''), $path);
           
            $result = $this->MySql->getRefererUrlLike($id, $tpath['path']);
            if($result && !empty($result)) {
                return $result['referer'];
            }
        }
        return '';
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
        // test
        
        if ( is_string  ($str) ) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        } else {
            $str = ' ';
        }
        
        return $str;
    }
    
    /*private function excelHeader()
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
        
    }*/
    
    private function createExcel()
    {    
        /*$sec = (string)(microtime(true) - $this->start);
        $this->log->m_log('DATA PREPARE OK: ' . $sec . ' second');*/
        //object of the Spreadsheet class to create the excel data
        $spreadsheet = new Spreadsheet();

        $cntSheet = 0;
        
        foreach($this->readyMeta as $type => $metaPack) {
            $flag = false;
            if ($type == '') continue;
            
            $spreadsheet->createSheet();
            // Zero based, so set the second tab as active sheet
            $spreadsheet->setActiveSheetIndex($cntSheet);
            $spreadsheet->setActiveSheetIndex($cntSheet)->setTitle($type);
            //            
            $cntRow = 2;    // Second row
            $spreadsheet->getActiveSheet()->insertNewRowBefore(1, count($metaPack));
            foreach ($metaPack as $row) {
                if(!$flag) {
                    // display field/column names as first row
                    $cntColumn = 1;
                    foreach ($row as $key => $item) {
                        $spreadsheet->setActiveSheetIndex($cntSheet)->setCellValueByColumnAndRow($cntColumn, 1, $key);    //first row, dinamic column
                        $lastCellAddress = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($cntColumn, 1)->getCoordinate();
                        $spreadsheet->getActiveSheet()->getCell($lastCellAddress)->setDataType(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        ++$cntColumn;
                    }
                    $flag = true;
                }
                $cntColumn = 1;
                $tmp = $spreadsheet->setActiveSheetIndex($cntSheet);
                $activeSheet = $spreadsheet->getActiveSheet();
                
                foreach ($row as $item) {
                    
                    $tmp->setCellValueByColumnAndRow($cntColumn, $cntRow, $item);
                    $lastCellAddress = $activeSheet->getCellByColumnAndRow($cntColumn, $cntRow)->getCoordinate();
                    $spreadsheet->getActiveSheet()->getCell($lastCellAddress)->setDataType(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    ++$cntColumn;
                }
                
                session_start();
                $_SESSION['meta_export_cnt']++;
                session_write_close();
                
                /*$sec = (string)(microtime(true) - $this->start);
                $this->log->m_log('resz BENT: ' . $sec . 'second');*/
                ++$cntRow;
            }
            $cell_st =[
                'font' =>['bold' => true],
                'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders'=>['bottom' =>['style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
            ];
            $spreadsheet->setActiveSheetIndex($cntSheet)->getStyle('A1:Z1')->applyFromArray($cell_st);
            ++$cntSheet;
        }
        /*$sec = (string)(microtime(true) - $this->start);
        $this->log->m_log('ADAT BENT: ' . $sec . 'second');*/

        
        //set columns width
        /*$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(18);*/
        
        
        //make object of the Xlsx class to save the excel file
        $writer = new Xlsx($spreadsheet);
        $fxls = FOLDER_META_EXPORT .'/' . $this->filename;
        
        if (!file_exists(FOLDER_META_EXPORT)) {
            mkdir(FOLDER_META_EXPORT, 0777, true);
        }
        
        $writer->save($fxls);
        session_start();
        $_SESSION['meta_export_file'] = $fxls;
        $_SESSION['meta_export_check'] = 1;
        session_write_close();
        // download
        //$file = basename($_GET['file']);
        /*$sec = (string)(microtime(true) - $this->start);
        $this->log->m_log('MENTES: ' . $sec . 'second');*/
        /*if(!file_exists($fxls)){ // file does not exist
            die('file not found');
        } else {
            header('Content-disposition: attachment; filename='.$fxls);
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Length: ' . filesize($fxls));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            ob_clean();
            flush();
            readfile($fxls);
        }*/
       /* $sec = (string)(microtime(true) - $this->start);
        $this->log->m_log('EXCEL GENERATE OK: ' . $sec . 'second');*/
    }
    
    
    private function createExcelTemplate()
    {
        
        //object of the Spreadsheet class to create the excel data
        $spreadsheet = new Spreadsheet();
        
        $spreadsheet->createSheet();
        // Zero based, so set the second tab as active sheet
        $spreadsheet->setActiveSheetIndex(1);
        $spreadsheet->getActiveSheet()->setTitle('ddd tab');
        
        
        //add some data in excel cells
        $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('A1', 'Domain')
        ->setCellValue('B1', 'Category')
        ->setCellValue('C1', 'Nr. Pages');
        
        
        $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('A2', 'CoursesWeb.net')
        ->setCellValue('B2', 'Web Development')
        ->setCellValue('C2', '4000');
        
        $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('A3', 'MarPlo.net')
        ->setCellValue('B3', 'Courses & Games')
        ->setCellValue('C3', '15000');
        
        //set style for A1,B1,C1 cells
        $cell_st =[
            'font' =>['bold' => true],
            'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders'=>['bottom' =>['style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
        ];
        $spreadsheet->getActiveSheet()->getStyle('A1:C1')->applyFromArray($cell_st);
        
        //set columns width
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        
        $spreadsheet->getActiveSheet()->setTitle('Simple'); //set a title for Worksheet
        
        //make object of the Xlsx class to save the excel file
        $writer = new Xlsx($spreadsheet);
        $fxls ='excel-file_test.xlsx';
        $writer->save($fxls);
        // download
        //$file = basename($_GET['file']);
        $file = $fxls;
        
        if(!file_exists($file)){ // file does not exist
            die('file not found');
        } else {
            header('Content-disposition: attachment; filename='.$file);
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Length: ' . filesize($file));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            ob_clean();
            flush();
            readfile($file);
        }
    }
    
    
}
$export = new MetaExport();
$export->exportProcess();
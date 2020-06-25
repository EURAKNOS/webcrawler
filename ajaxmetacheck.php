<?php
session_start ();
session_write_close();
require_once 'config.php';
set_time_limit (10000);

class AjaxMetaCheck {
    
   
    public function __construct()
    {
        
    }
    

    public function status()
    { //print_r($_SESSION);
        if ($_SESSION['meta_export_check'] == 0) {
            $p = $this->percentage($_SESSION['meta_all_cnt'], $_SESSION['meta_export_cnt']);
            if ($p == 100) $p = 99;
            $html = '<div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" aria-valuenow="'.$p.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$p.'%"></div>';
            echo json_encode(array('check' => $_SESSION['meta_export_check'], 'percentage' => $html, 'ok' => $_SESSION['meta_export_cnt'], 'all' => $_SESSION['meta_all_cnt']));
        } else {
            echo json_encode(array( 'check' => $_SESSION['meta_export_check'], 'file' => $_SESSION['meta_export_file']));
        }
    }   
        
    private function percentage($all, $cnt)
    {
        if ($all > 0 && $cnt > 0) {
            return round($cnt / $all * 100);
        } else {
            return 0;
        }
    }
 
}

$ajaxProcess = new AjaxMetaCheck();
$ajaxProcess->status();
<?php
session_start ();
session_write_close();
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Detail.php';
set_time_limit (10000);

class AjaxCheck {
    
   
    public function __construct()
    {
        $this->MySql = new DbMysql();
    }
    

    public function status()
    {  
        if(isset($_SESSION['urlid']) && $_SESSION['urlid']) {
            $details = new Detail();
            $details->getDownloadStatisticsByUrlId($_SESSION['urlid']);
            $this->statHtml = $details->statHtml;
            $this->statTemplate();
            //$this->getDownloadStatus();
            //$this->checkHtml();
            echo $this->htmlResult;
        } else {

            echo '';
        }
    }
    
    public function statTemplate() {
        $this->htmlResult = ('<div class="row justify-content-center" style="margin-top:2rem;">
        <div class="col-md-8 dashboardittem">
        <h3>DOWNLOAD STATISTICS</h3>
        <div class="row">
        <div class="col-md-5">
        <canvas id="chartjs-4" class="chartjs" style="display: block;height: 30rem; width:100%;"></canvas>
        </div>
        <div class="col-md-7">
        <canvas id="chartjs-2" class="chartjs" style="display: block;height: 30rem; width:100%;"></canvas>
        </div>
        </div>
        </div>
        </div>
        </div>
        
        <script>'.$this->statHtml.'</script>');
    }
    
 
}

$ajaxProcess = new AjaxCheck();
$ajaxProcess->status();
<?php
session_start ();
session_write_close();
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Detail.php';
set_time_limit (10000);
/**
 * 
 * Returns the current download status to Ajax
 *
 */
class AjaxCheck {
    
   
    public function __construct()
    {
        $this->MySql = new DbMysql();
    }
    
    /**
     * Start a status query
     */
    public function status()
    {  
        if(isset($_SESSION['urlid']) && $_SESSION['urlid']) {
            $this->details = new Detail();
            $this->details->getDownloadStatisticsByUrlId($_SESSION['urlid']);
            $this->statHtml = $this->details->statHtml;
            $this->statTemplate();
            //$this->getDownloadStatus();
            //$this->checkHtml();
            echo $this->htmlResult;
        } else {

            echo '';
        }
    }
    
    /**
     * Create a status statement to display
     */
    public function statTemplate() {
        if ($this->details->mainData['download'] == 1) {
            $status = '<p>Finished</p>';
        } elseif ($this->details->mainData['download'] == 2) {
            $status = '<p>Stopped</p>';
        } else {
            $status = '<p><span class="working">Working</span></p>';
        }
        
        $this->htmlResult = ('<div style="margin: 10px;">'.$status.'</div>');
        $this->htmlResult .= ('<div class="row justify-content-center" style="margin-top:2rem;">
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
<?php
session_start ();
session_write_close();
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Main.php';
require_once 'Detail.php';
set_time_limit (10000);

class MainPageAjax {
    
   
    public function __construct()
    {
    }
    

    public function content()
    {  
        $this->mainpage = new MainPage();
        $this->mainpage->getMainContentAjax();
        $this->statHtml = $this->mainpage->ctemplate;
        echo $this->statHtml;
    }
    
    public function detail()
    {
        $this->details = new Detail();
        $this->details->getDownloadStatisticsByUrlId($_POST['data']);
        $this->statHtml = $this->details->statHtml;
        $this->statTemplate();
        echo $this->htmlResult;
    }
    
    public function statTemplate() {

        $this->htmlResult = ('
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
        <script>'.$this->statHtml.'</script>');
    }
    
    public function detailStatus()
    {
        $this->details = new Detail();
        $this->details->getStatus($_POST['data']);
        $this->mainData = $this->details->mainData;
        if ($this->mainData['download'] == 1) {
            $status = 'Finished';
        } elseif ($this->mainData['download'] == 2) {
            $status = 'Stopped';
        } else {
            $status = 'Working';
        }
        if ($this->mainData['download'] != 0) {
            $this->html = ('<p>('.$status.') Last run: ' . date("Y. M d", $this->mainData['download_time']) . ' (Runtime: ' . $this->mainData['run'] . ')</p>');
        } else {
            $this->html = ('<p> <span class="working">'.$status.'</span></p>');
        }
        echo $this->html;
    }
  
}

$ajaxProcess = new MainPageAjax();
if (isset($_POST['processFunction']) && $_POST['processFunction'] == 'mainpage') {
    $ajaxProcess->content();
} elseif (isset($_POST['processFunction']) && $_POST['processFunction'] == 'detail') {
    $ajaxProcess->detail();
} elseif (isset($_POST['processFunction']) && $_POST['processFunction'] == 'detail-status') {
    $ajaxProcess->detailStatus();
}

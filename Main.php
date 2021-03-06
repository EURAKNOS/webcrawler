<?php

/**
 * WebCrawler
 *
 * Main page (DASHBOARD)
 *
 * @author Zoltan Szabo
 *
 *
 */
class MainPage
{
    public $html;
    
    public $ctemplate;
    
    private $types;
    
    public function __construct()
    {
        $this->types['text'] = array('content', 'pdf', 'docx', 'xlsx', 'epub');
        $this->types['presentation'] = array('pptx', 'ppt', 'prezi');
        $this->types['image'] = array('jpg', 'bmp', 'png', 'swf', 'svg');
        $this->types['video'] = array('youtube_video', 'vimeo_video', 'mp4');
        $this->types['audio'] = array('spotify');
        $this->types['other'] = array('google_map', 'zip', 'mailchimp');
    }
    
    public function getPage()
    {
        $this->getData();
        $this->contentTemplate();
        $this->template();
    }
    
    
    /**
     * Preparation of the frontend surface.
     * (Due to the minimal frontend part, I don't create a separate file for it or use a temaplet manager.)
     */
    public function template()
    {
        $this->html .= ('<!doctype html>
            <html>
            <head>
            	<meta charset="utf-8">
                <title>Euraknos WebCrawler</title>
                <link rel="stylesheet" href="style/css/bootstrap.min.css">
                <link rel="stylesheet" href="style/css/css.css">
                <link rel="stylesheet" href="style/css/all.min.css"> <!-- Font Awesome -->
                <link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet" type="text/css">
            	<script src="style/js/jquery.min.js"></script>
            	<script src="style/js/bootstrap.min.js"></script>
            
            	<script src="style/js/Chart/Chart.min.js"></script>
            	<script src="style/js/chartjs-plugin-datalabels.min.js"></script>
                <script src="style/js/mainpage.js"></script>
                
            
            </head>
            <body>
            
            <!-- Load an icon library to show a hamburger menu (bars) on small screens -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            
            <!-- Modal -->
            <div class="modal fade" id="deleteModal" data-id="" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="WebCrawlerModalLabel">Warning!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true"></span>
                    </button>
                  </div>
                  <div class="modal-body">
                    Are you sure to delete this crawler and all of its data?<br>
                    This will also delete the crawler-related results in the database and on the server!
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="accept-delete" class="btn btn-danger">Delete</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Navigation Menu -->
            <div class="topnav" style="margin-bottom: 10px;">
              <a class="active navbar-brand" style="text-align: left;" href="/">
                    <img src="style/images/logo-white_notext2.png" class="d-inline-block align-top" alt="">
                    EURAKNOS WEBCRAWLER
            	</a>
              <!-- Navigation links (hidden by default) -->
              <div id="myLinks">
                <a class="nav-link" href="new.php">Add Crawler</a>
                <a class="nav-link" href="export_details.php">Export all</a>
                <a class="nav-link" href="howto.html" target="_blank">How to</a>
              </div>
              <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
              <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                <i class="fa fa-bars"></i>
              </a>
            </div>

            <div class="navbar navbar-expand-lg navbar-dark">
            	<a class="navbar-brand" href="/">
                    <img src="style/images/logo-white_notext2.png" class="d-inline-block align-top" alt="">
                    EURAKNOS WEBCRAWLER
            	</a>
            	<ul class="navbar-nav">
                  	<li class="nav-item">
                    	<a class="nav-link" href="new.php">Add crawler</a>
                  	</li>
                    <li class="nav-item">
                    	<a class="nav-link" href="export_details.php">Export all</a>
                  	</li>
                    <li class="nav-item">
                    	<a class="nav-link" href="howto.html" target="_blank">How to</a>
                  	</li>
            </ul>
            </div>
            <div class="container-fluid">');
            $this->html .= $this->ctemplate;
            $this->html .= ('</div>
            <script>
            /* Toggle between showing and hiding the navigation menu links when the user clicks on the hamburger menu / bar icon */
            function myFunction() {
              var x = document.getElementById("myLinks");
              if (x.style.display === "block") {
                x.style.display = "none";
              } else {
                x.style.display = "block";
              }
            }    
            </script>
            </body>
            </html>

');
        print($this->html);
    }
    
    /**
     * Prepares the content section for display
     * View statistics for downloaded pages
     */
    private function contentTemplate()
    {
        $this->ctemplate = ('
            <div class="row justify-content-center">
            
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">TEXT</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['text']['all'] . '</p>
                        <p style="font-size:1rem;margin:0;padding:0;">(' . $this->resultAllOutput['text']['distinct'] . ')</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-text" class="chartjs" data-percentage="' . $this->resultAllOutput['text']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">PRESENTATION</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['presentation']['all'] . '</p>
                        <p style="font-size:1rem;margin:0;padding:0;">(' . $this->resultAllOutput['presentation']['distinct'] . ')</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-prez" class="chartjs" data-percentage="' . $this->resultAllOutput['presentation']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">IMAGE</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['image']['all'] . '</p>
                        <p style="font-size:1rem;margin:0;padding:0;">(' . $this->resultAllOutput['image']['distinct'] . ')</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-image" class="chartjs" data-percentage="' . $this->resultAllOutput['image']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">VIDEO</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['video']['all'] . '</p>
                        <p style="font-size:1rem;margin:0;padding:0;">(' . $this->resultAllOutput['video']['distinct'] . ')</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-video" class="chartjs" data-percentage="' . $this->resultAllOutput['video']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>
                <div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">AUDIO</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['audio']['all'] . '</p>
                        <p style="font-size:1rem;margin:0;padding:0;">(' . $this->resultAllOutput['audio']['distinct'] . ')</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-audio" class="chartjs" data-percentage="' . $this->resultAllOutput['audio']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">OTHER</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['other']['all'] . '</p>
                        <p style="font-size:1rem;margin:0;padding:0;">(' . $this->resultAllOutput['other']['distinct'] . ')</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-other" class="chartjs" data-percentage="' . $this->resultAllOutput['other']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            </div>
            
            <div class="row justify-content-center">
            	<div class="col-md-10">
            		<div class="dashboardittem">
            			<p class="stattitle" style="font-size: 1.5rem;font-weight:bold;">Crawler(s)</p>
                        <table class="table table-striped table-light dashboard-table">
                        	<thead class="thead-dark">
                            	<tr>
                                    <th></th>
                                	<th>name</th>
                                	<th>url</th>
                                	<th>status</th>
                                	<th>last run</th>
                                	<th>run time</th>
                                	<th>objects</th>
                                	<th>metadata</th>
                                	<th></th>
                                </tr>
                            </thead>
                            <tbody>');
        foreach($this->tableData as $item) {
            $this->ctemplate .= ('<tr class="w-row">');
            $this->ctemplate .= ('<td class="fbuttons dashboard-table-details" style="width: 140px;">');
            if ( $item['status_n'] == 0 ) {
                $this->ctemplate .= ('<span class="stop-button btn btn-sm btn-orange-warning" data-id="' . $item['id'] . '">Stop</span>');
            } else {
                $this->ctemplate .= ('<span class="delete-button btn btn-sm btn-danger" data-id="' . $item['id'] . '">Delete</span>');
                if ($item['status_n'] == 2) {
                    $this->ctemplate .= ('<span class="continue-button btn btn-sm btn-success" data-id="' . $item['id'] . '">Continue</span>');
                }
            }
            $this->ctemplate .= ('</td>');
            $this->ctemplate .= ('<td class="dashboard-table-name">' . $item['wname'] . '</td>');
            $this->ctemplate .= ('<td class="dashboard-table-url">' . $item['url'] . '</td>');
            $this->ctemplate .= ('<td class="dashboard-table-status' . $item['status_class'] . '">' . $item['status'] . '</td>');
            $this->ctemplate .= ('<td class="dashboard-table-lastrun">' . $item['last_run'] . '</td>');
            $this->ctemplate .= ('<td class="dashboard-table-runtime">' . $item['run'] . '</td>');
            $this->ctemplate .= ('<td class="dashboard-table-objects">' . $item['objectc'] . '</td>');
            $this->ctemplate .= ('<td class="dashboard-table-meta">' . $item['metadata'] . '%</td>');
            $this->ctemplate .= ('<td class="dashboard-table-details"><a href="details.php?id='. $item['id'] .'"><span class="btn btn-sm btn-info">Details</span></a></td>');
            $this->ctemplate .= ('</tr>');
        }
                          
                          //      	<td class="working">Working</td>
            
        $this->ctemplate .= ('</tbody>
                        </table>
            		</div>
            	</div>
            </div>
                <div class="version"><p>Ver. ' . VERSION . '</p></div>
            	<script src="style/js/chartdata.js"></script>
            	<script src="style/js/javascript.js"></script>
                <script src="style/js/buttons.js"></script>');
    }
    
    /**
     * Retrieve data for all downloaded pages
     */
    private function getData()
    {
        $this->MySql = new DbMysql();
        $this->getAllDataByType();
    }
    
    /**
     * Data count by type
     */
    private function getAllDataByType()
    {
        $this->getAllTextCount();
        $this->countByType();
        $this->resultAll = $this->MySql->result;
        $this->resultWithMeta = $this->MySql->result2;
        $this->systematization();   
        $this->getDataToTable();
        
/*echo '<pre>';
        print_r($this->resultAll);
        print_r($this->resultWithMeta);
        print_r($this->resultAllOutput);*/
        
    }
    
    private function getAllTextCount()
    {
        $this->MySql->getAllContent();
        $this->MySql->getAllDistinctContent();
        $this->MySql->getAllContentWithMeta();
    }
    
    private function countByType()
    {
        $this->MySql->countByTypeAllPage();
        $this->MySql->countByTypeDistinctAllPage();
        $this->MySql->countByTypeAllPageWithMeta();
    }
    
    /**
     * Organizes the display
     */
    private function systematization()
    {
        foreach ($this->types as $ktp => $tp) {
            $this->resultAllOutput[$ktp]['all'] = 0;
            $this->resultAllOutput[$ktp]['allmeta'] = 0;
            $this->resultAllOutput[$ktp]['distinct'] = 0;
            foreach ($tp as $value) {
                $this->resultAllOutput[$ktp]['all'] += (isset($this->resultAll[$value])) ? $this->resultAll[$value] : 0;
                $this->resultAllOutput[$ktp]['distinct'] += (isset($this->resultAll['distinct_' . $value])) ? $this->resultAll['distinct_' . $value] : 0;
                $this->resultAllOutput[$ktp]['allmeta'] += (isset($this->resultWithMeta[$value])) ? $this->resultWithMeta[$value] : 0;
                $this->resultAllOutput[$ktp]['percentage'] = $this->percentageAllPage($this->resultAllOutput[$ktp]['all'], $this->resultAllOutput[$ktp]['allmeta']);
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
     * Retrieves the data from the database to create the table
     */
    private function getDataToTable()
    {
        $this->MySql->getAllUrlWithoutDelete();
        if (isset($this->MySql->resultUrls) && !empty($this->MySql->resultUrls)) {
            foreach ($this->MySql->resultUrls as $value) {
                $this->MySql->countFileElement($value['id']);
                $this->MySql->percentage($value['id']);
                $this->data1 = $this->MySql->allCount;
                $this->data2 = $this->MySql->allCount2;
                
                $this->tableData[$value['id']]['wname'] = $value['wname'];
                $this->tableData[$value['id']]['url'] = $value['url'];
                $this->tableData[$value['id']]['status_n'] = $value['download'];
                if ($value['download'] == 1) {
                    $this->tableData[$value['id']]['status'] = 'Finished';  
                } elseif ($value['download'] == 2) {
                    $this->tableData[$value['id']]['status'] = 'Stopped';
                } else {
                    $this->tableData[$value['id']]['status'] = 'Working';
                }
                $this->tableData[$value['id']]['status_class'] = ($value['download'] == 0) ? ' working' : '';
                $this->tableData[$value['id']]['last_run'] = date("M d, Y", $value['download_time']);
                $this->tableData[$value['id']]['run'] = ($value['end_time'] > 0)?$this->hourAndMinConverter($value['end_time'] - $value['download_time']): '';
                $this->tableData[$value['id']]['objectc'] = $this->data1;
                $this->tableData[$value['id']]['metadata'] = $this->percentageAllPage($this->data1, $this->data2);
                $this->tableData[$value['id']]['id'] = $value['id'];
                
            }
        }
    }
    
    private function hourAndMinConverter($time)
    {
        $s = $time%60;
        $m = floor(($time%3600)/60);
        $h = floor(($time%86400)/3600);
        $d = floor(($time%2592000)/86400);
        
        return "$d days, $h h $m min";
        
        return gmdate('j\d\a\y G\h i\m\i\n', $time);
    }
    
    public function getMainContentAjax()
    {
        $this->stuckProcess();
        $this->getData();
        $this->contentTemplate();
    }
    
    private function stuckProcess()
    {
        $this->MySql = new DbMysql();
        $this->MySql->stuckProcessStop();
    }
    
    
}
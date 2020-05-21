<?php

/**
 * WebCrawler
 *
 * Main page (DASHBOARD)
 *
 * @author Zoltan Szabo
 * Email: szabo.zoltan@aki.naik.hu
 *
 *
 */
class MainPage
{
    public $html;
    
    private $types;
    
    public function __construct()
    {
        $this->types['text'] = array('content', 'pdf', 'docx', 'xlsx', 'epub');
        $this->types['presentation'] = array('pptx', 'ppt');
        $this->types['image'] = array('jpg', 'png', 'swf', 'svg');
        $this->types['video'] = array('youtube_video', 'vimeo_video', 'mp4');
        $this->types['other'] = array('google_map', 'zip');
        
        $this->getData();
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
            	<script src="style/js/main.js"></script>
            
            	<script src="style/js/Chart/Chart.min.js"></script>
            	<script src="style/js/chartjs-plugin-datalabels.min.js"></script>
            
            </head>
            <body>
            <div class="navbar navbar-expand-lg navbar-dark">
            	<a class="navbar-brand" href="#">
                    <img src="style/images/logo-white_notext2.png" class="d-inline-block align-top" alt="">
                    EURAKNOS WEBCRAWLER
            	</a>
            	<ul class="navbar-nav">
            		<li class="nav-item active">
                    	<a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
                  	</li>
                  	<li class="nav-item">
                    	<a class="nav-link" href="new.php">Add Crawler</a>
                  	</li>
            </ul>
            </div>
            
            <div class="container-fluid">
            	<!-- Modal -->
            
            <div class="row justify-content-center">
            
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">TEXT</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['text']['all'] . '</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-text" class="chartjs" data-percentage="' . $this->resultAllOutput['text']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">PRESENTATION</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['presentation']['all'] . '</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-prez" class="chartjs" data-percentage="' . $this->resultAllOutput['presentation']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">IMAGE</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['image']['all'] . '</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-image" class="chartjs" data-percentage="' . $this->resultAllOutput['image']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">VIDEO</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['video']['all'] . '</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-video" class="chartjs" data-percentage="' . $this->resultAllOutput['video']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            	<div class="col-md-2">
                	<div class="dashboardittem">
                        <p class="sitename">OTHER</p>
                        <p style="font-size:3rem;margin:0;padding:0;">' . $this->resultAllOutput['other']['all'] . '</p>
            			<p style="margin-bottom:0;">metadata availability</p>
                        <canvas id="stat-other" class="chartjs" data-percentage="' . $this->resultAllOutput['other']['percentage'] . '" style="display: inline-block;height: 20px;width:100%;background-color:#e5e5e5;"></canvas>
                    </div>
            	</div>	
            </div>
            
            <div class="row justify-content-center">
            	<div class="col-md-10">
            		<div class="dashboardittem">
            			<p class="stattitle" style="font-size: 1.5rem;font-weight:bold;">list of Crawlers</p>
                        <table class="table table-striped table-light dashboard-table">
                        	<thead class="thead-dark">
                            	<tr>
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
            $this->html .= ('<tr>');
            $this->html .= ('<td class="dashboard-table-name">' . $item['wname'] . '</td>');
            $this->html .= ('<td class="dashboard-table-url">' . $item['url'] . '</td>');
            $this->html .= ('<td class="dashboard-table-status' . $item['status_class'] . '">' . $item['status'] . '</td>');
            $this->html .= ('<td class="dashboard-table-lastrun">' . $item['last_run'] . '</td>');
            $this->html .= ('<td class="dashboard-table-runtime">' . $item['run'] . '</td>');
            $this->html .= ('<td class="dashboard-table-objects">' . $item['objectc'] . '</td>');
            $this->html .= ('<td class="dashboard-table-meta">' . $item['metadata'] . '%</td>');
            $this->html .= ('<td class="dashboard-table-details"><a href="Detail.php?id='. $item['id'] .'"><span class="btn btn-sm btn-info">Details</span></a></td>');
            $this->html .= ('</tr>');
        }
                          
                          //      	<td class="working">Working</td>
            
        $this->html .= ('</tbody>
                        </table>
            		</div>
            	</div>
            </div>
            
            	<script src="style/js/chartdata.js"></script>
            	<script src="style/js/javascript.js"></script>
            
            
            </div>
            </body>
            </html>

');
        print($this->html);
    }
    
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
        $this->MySql->getAllContentWithMeta();
    }
    
    private function countByType()
    {
        $this->MySql->countByTypeAllPage();
        $this->MySql->countByTypeAllPageWithMeta();
    }
    
    private function systematization()
    {
        foreach ($this->types as $ktp => $tp) {
            $this->resultAllOutput[$ktp]['all'] = 0;
            $this->resultAllOutput[$ktp]['allmeta'] = 0;
            foreach ($tp as $value) {
                $this->resultAllOutput[$ktp]['all'] += (isset($this->resultAll[$value])) ? $this->resultAll[$value] : 0;
                $this->resultAllOutput[$ktp]['allmeta'] += (isset($this->resultWithMeta[$value])) ? $this->resultWithMeta[$value] : 0;
                $this->resultAllOutput[$ktp]['percentage'] = $this->percentageAllPage($this->resultAllOutput[$ktp]['all'], $this->resultAllOutput[$ktp]['allmeta']);
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
                $this->tableData[$value['id']]['status'] = ($value['download'] != 1) ? 'Working' : 'Finished';
                $this->tableData[$value['id']]['status_class'] = ($value['download'] != 1) ? ' working' : '';
                $this->tableData[$value['id']]['last_run'] = date("Y. M d", $value['download_time']);
                $this->tableData[$value['id']]['run'] = $this->hourAndMinConverter($value['end_time'] - $value['download_time']);
                $this->tableData[$value['id']]['objectc'] = $this->data1;
                $this->tableData[$value['id']]['metadata'] = $this->percentageAllPage($this->data1, $this->data2);
                $this->tableData[$value['id']]['id'] = $value['id'];
                
            }
        }
    }
    
    private function hourAndMinConverter($time)
    {
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return date('H:i:s', $time);
        return $hours."h ".$minutes."min ";
    }
    
    
}
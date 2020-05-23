<?php
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Log.php';
set_time_limit(500000);

/*
 * $_POST['class'][1] = array ('name' => 'elements-box', 'title' => 'articlecontent');
 * $_POST['class'][2] = array ('name' => 'page-title', 'title' => 'also');
 */
class Detail
{

    public $html;

    public $statHtml;

    private $types;

    public function __construct()
    {
        $this->types['text'] = array(
            'content',
            'pdf',
            'docx',
            'xlsx',
            'epub'
        );
        $this->types['presentation'] = array(
            'pptx',
            'ppt'
        );
        $this->types['image'] = array(
            'jpg',
            'png',
            'swf',
            'svg'
        );
        $this->types['video'] = array(
            'youtube_video',
            'vimeo_video',
            'mp4'
        );
        $this->types['other'] = array(
            'google_map',
            'zip'
        );

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
        $this->MySql = new DbMysql();
    }

    public function getDetails()
    {
        $this->getData();
        // echo $this->statHtml;
        $this->template();
    }

    /**
     * Preparation of the frontend surface.
     * (Due to the minimal frontend part, I don't create a separate file for it or use a temaplet manager.)
     */
    public function template()
    {
        $h1check = (isset($this->mainData['post']['h1']) && $this->mainData['post']['h1'] == '1') ? ' checked' : '';
        $h2check = (isset($this->mainData['post']['h2']) && $this->mainData['post']['h2'] == '1') ? ' checked' : '';
        $h3check = (isset($this->mainData['post']['h3']) && $this->mainData['post']['h3'] == '1') ? ' checked' : '';
        $mtitle = (isset($this->mainData['post']['meta-title']) && $this->mainData['post']['meta-title'] == 1) ? ' checked' : '';
        $mkeyw = (isset($this->mainData['post']['meta-keywords']) && $this->mainData['post']['meta-keywords'] == 1) ? ' checked' : '';
        $mdesc = (isset($this->mainData['post']['meta-description']) && $this->mainData['post']['meta-description'] == 1) ? ' checked' : '';

        $this->html .= ('<!doctype html>
            <html>
            <head>
            	<meta charset="utf-8">');
        if ($this->mainData['download'] == 0) {
            $this->html .= ('<meta http-equiv="refresh" content="60" />');
        }
                $this->html .= ('<title>Euraknos WebCrawler</title>
                <link rel="stylesheet" href="style/css/bootstrap.min.css">
                <link rel="stylesheet" href="style/css/css.css">
                <link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet" type="text/css">
            	<script src="style/js/jquery.min.js"></script>
            	<script src="style/js/bootstrap.min.js"></script>
            	<script src="style/js/main.js"></script>
            
            	<script src="style/js/Chart/Chart.min.js"></script>
            
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
            	<div class="row justify-content-center">
                    <div class="col-md-10">
                        <h2 style="font-weight: bold;">' . $this->mainData['wname'] . '</h2>');
                    if ($this->mainData['download'] == 1) {
                        $this->html .= ('<p>Last run: ' . date("Y. M d", $this->mainData['download_time']) . ' (Runtime: ' . $this->mainData['run'] . ')</p>');
                    } else {
                        $this->html .= ('<p> <span class="working">Working</span></p>');
                    }
            
                    $this->html .= ('<div class="col-md-12">
                            <a href="export_details.php?id='.$this->mainData['id'].'"><button id="editcrawler" type="button" class="btn btn-warning">Export Data</button></a>
            			</div>');
                    $this->html .= ('
                    </div>
                </div>
            	
                <form action="/" method="post" name="webcrawler" style="margin-top: 2rem;">
                	<div class="row justify-content-center">
                    	<div class="col-md-3">
                            <div class="input-group input-group-md mb-3">
                                <input type="text" class="form-control" id="url" value="' . $this->mainData['url'] . '" placeholder="Starting URL" aria-label="URL" aria-describedby="button-addon2" style="color: #b5b5b5;" required="" disabled>
                            </div>
                            <div class="input-group input-group-md mb-3">
                                <input type="text" class="form-control" id="match_url" value="' . $this->mainData['post']['match_url'] . '" placeholder="Matching URL (Optional)" aria-label="URL" aria-describedby="button-addon2" style="color: #b5b5b5;" required="" disabled>
                            </div>
                        </div>
                    	<div class="col-md-3">
            	        <h5>General elements</h5>
                        	<div class="row justify-content-center">
                                <div class="col-sm-5">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="meta-title" value="1" id="meta-title"' . $mtitle . '>
                                        <label class="form-check-label" for="meta-title"> META Title</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="meta-keywords" value="1" id="meta-keywords"' . $mkeyw . '>
                                        <label class="form-check-label" for="meta-keywords"> META Keywords</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="meta-description" value="1" id="meta-description"' . $mdesc . '>
                                        <label class="form-check-label" for="meta-description"> META Description</label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="h1" value="1" id="h1"' . $h1check . '>
                                        <label class="form-check-label" for="h1"> Heading 1</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="h2" value="1" id="h2"' . $h2check . '>
                                        <label class="form-check-label" for="h2"> Heading 2</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="h3" value="1" id="h3"' . $h3check . '>
                                        <label class="form-check-label" for="h3"> Heading 3</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center" style="margin-top:2rem;">
            	        <div class="col-md-6">
                        	<h5>Other searching elements</h5>');
                    foreach ($this->mainData['post']['class'] as $item) {
            
                        $this->html .= ('<div class="row">
                                <div class="col-lg-12">
                                    <div><div id="inputFormRow"><div class="input-group mb-3"><input type="text" value="' . $item['name'] . '" class="form-control m-input" placeholder="Enter class name" disabled><input type="text" value="' . $item['title'] . '" class="form-control m-input" placeholder="Enter title" disabled></div></div></div>
                                </div>
                            </div>');
                    }
                    $this->html .= ('</div>
                    </div>
                </form>
            
                <div class="row justify-content-center" style="margin-top:2rem;">
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
            
    	<script>' . $this->statHtml . '</script>
    	<script src="style/js/javascript.js"></script>
            
            
            </div>
            </body>
            </html>
            
');
        print($this->html);
    }

    private function getData()
    {
        $this->getDataByUrlId();
        $this->getDownloadStatisticsByUrlId($_GET['id']);
    }

    private function getDataByUrlId()
    {
        $this->MySql->getCrawlingData($_GET['id']);
        $this->mainData = $this->MySql->result;
        $this->mainData['post'] = unserialize($this->mainData['post_data']);
        $this->mainData['run'] = $this->hourAndMinConverter($this->mainData['end_time'] - $this->mainData['download_time']);
        $this->mainData['post']['match_url'] = (isset($this->mainData['post']['match_url'])) ? $this->mainData['post']['match_url'] : '';
        $this->mainData['post']['class'] = (isset($this->mainData['post']['class'])) ? $this->mainData['post']['class'] : array();
        /*
         * echo '<pre>';
         * print_r($this->mainData);
         */
    }

    public function getDownloadStatisticsByUrlId($id)
    {
        $this->MySql->countFileElement($id);
        $this->MySql->percentage($id);

        foreach ($this->MySql->result as $key => $item) {
            $this->statistics[$key]['all'] = $item;
            if(isset($this->MySql->result2[$key])) {
                $this->statistics[$key]['meta'] = $this->MySql->result2[$key];
            }
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
        $this->statisticsJavaScript();
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

    private function statisticsJavaScript()
    {
        $this->statHtml = ("// DOUGHNUT
    		new Chart(document.getElementById('chartjs-4'),{
    			type:'doughnut',
    			data:{
    				labels:[");
        foreach ($this->statistics1 as $value) {
            $this->statHtml .= "'" . ($value['name'] . "',");
        }
        $this->statHtml .= ("],datasets:[{
    					data:[");
        foreach ($this->statistics1 as $value) {
            $this->statHtml .= ($value['all'] . ",");
        }
        $this->statHtml .= ("],
    					backgroundColor:[
    						// Colors 13 (16)
    						'#ff6b6c','#ff9165','#ffb165','#ffc365','#ffda65','#f4f170','#dafd67','#82fc68','#74f0ac','#7aead3','#7ad7ea','#7ac0ea','#7aa5ea','#7884f7','#785df7','#9a6cf8',
    					],
    					borderWidth: 0,
    				}],
    			},
    			
    			options: {
    				legend: {
    					position: 'right',
    					onClick: function(event, legendItem) {
    					}
    		        },
    				title: {
                		display: true,
                		text: 'QUANTITY',
    					fontFamily: 'Lato',
    					fontSize: 14,
    					fontColor: '#0b1c26',
           			},
    		    },
    		});
    
    
    
    // HORIZONTAL BAR
    
    new Chart(document.getElementById('chartjs-2'),{
    //	plugins: [ChartDataLabels],
    	type:'horizontalBar',
    	data:{
    		labels:[");
        foreach ($this->statistics2 as $value) {
            $this->statHtml .= "'" . ($value['name'] . "',");
        }
        $this->statHtml .= ("],
    		datasets:[{
    			data:[");
        foreach ($this->statistics2 as $value) {
            $this->statHtml .= ($value['percentage'] . ",");
        }
        $this->statHtml .= ("],
    			backgroundColor:[
    				'#ff6b6c','#ff9165','#ffb165','#ffc365','#ffda65','#f4f170','#dafd67','#82fc68','#74f0ac','#7aead3','#7ad7ea','#7ac0ea','#7aa5ea','#7884f7','#785df7','#9a6cf8',
    			],
    		}],
    	},
    			
    	options: {
    		legend: {
    			display: false,
    		},
    		title: {
    			display: true,
    			text: 'METADATA AVAILABILITY',
    			fontFamily: 'Lato',
    			fontSize: 14,
    			fontColor: '#0b1c26',
    		},
    		scales: {
    			yAxes: [{
    				gridLines: {
    					display: false,
    				},
    				ticks: {
    					display: false,	// Y tengely feliratok elrejtése
    					fontFamily: 'Lato'
    				},
    			}],
    			xAxes: [{
    				ticks: {
    					fontFamily: 'Lato',
    					callback: function(value, index, values) {
    						return value+'%';
    					},
                        beginAtZero: true,
                        max: 100 
    				},
    			}],
    		},
    		tooltips: {
    			callback: {
    				label: function(tooltipItem, data) {
    						return data + '%';
    					},
    			},
    		},
    	},
    });");
    }
}
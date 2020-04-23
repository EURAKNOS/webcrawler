<?php

/**
 * WebCrawler 
 *  
 * Extract content from a website. 
 * It traverses the URL on the web page and then generates 
 * it using an appropriate methods for processing.
 * 
 * @author Zoltan Szabo
 * Email: szabo.zoltan@aki.naik.hu
 * 
 *
 */
class WebCrawler
{

    public $version = '0.1.4.3';

    public $html;

    public function __construct()
    {}

    /**
     * INDEX page.
     * Form website URL
     */
    public function mainPage()
    {
        $log = new WLog();
        $log->m_log('Load WebCrawler page');
        $this->process();
        $this->template();
    }

    /**
     * Preparation of the frontend surface.
     * (Due to the minimal frontend part, I don't create a separate file for it or use a temaplet manager.)
     */
    public function template()
    {   
        $this->html .= ('<html><head><meta charset="utf-8"><title>Euraknos WebCrawler</title><link rel="stylesheet" href="style/css/bootstrap.min.css"><link rel="stylesheet" href="style/css.css"><link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet" type="text/css"><script src="style/js/jquery.min.js"></script><script src="style/js/bootstrap.min.js"></script></head>');
        $this->html .= ('<body>');
        $this->html .= ('
        <form action="' . ROOT_PATH . '" method="post" name="webcrawler">
        <div class="header">
        <p>WebCrawler</p>
        </div>
        <div class="description">
        <p>Please enter the URL of the webpage you want to save</p>
        </div>
        <div class="row">
        <div class="col-lg-12">
        <div class="input">
        <input type="url" class="button" id="url" name="url"
            placeholder="https://www.aki.gov.hu"> <input type="submit"
                class="button" id="submit" name="submit" value="DOWNLOAD">
                </div>
                </div>
                </div>
                <h3 class="other-element">General elements</h3>
                <div class="row">
                <div class="col-lg-6">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" name="meta-title" value="1" id="meta-title">
                <label class="form-check-label"	for="meta-title"> META Title</label>
                </div>
                <div class="form-check">
                <input class="form-check-input" type="checkbox" name="meta-keywords" value="1" id="meta-keywords">
                <label class="form-check-label"	for="meta-keywords"> META Keywords</label>
                </div>
                <div class="form-check">
                <input class="form-check-input" type="checkbox" name="meta-description" value="1" id="meta-description">
                <label class="form-check-label"	for="meta-description"> META Description</label>
                </div>
                </div>
                <div class="col-lg-6">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" name="h1" value="1" id="h1">
                <label class="form-check-label"	for="h1"> H1</label>
                </div>
                <div class="form-check">
                <input class="form-check-input" type="checkbox" name="h2" value="1" id="h2">
                <label class="form-check-label"	for="h2"> H2</label>
                </div>
                <div class="form-check">
                <input class="form-check-input" type="checkbox" name="h3" value="1" id="h3">
                <label class="form-check-label"	for="h3"> H3</label>
                </div>
                </div>
                </div>
                
                <h3 class="other-element">Other searching elements</h3>
                <div class="row">
                <div class="col-lg-12">
             
                                <div id="newRow"></div>
                                <button id="addRow" type="button" class="btn btn-info">Add
                                Row</button>
                                </div>
                                </div>
                                </form>');
        $this->html .= ('<div class="version"><p>Ver. ' . $this->version . '</p></div><script src="style/javascript.js"></script>');
        $this->html .= ('</body>');
        $this->html .= ('</html>');
        print($this->html);
    }
    
    /**
     * Start
     */
    public function process()
    {
        if (isset($_POST['submit'])) {
            /*
             * echo '<pre>';
             * print_r($_POST);
             * echo '</pre>';
             * die();
             */
            $this->startCrawler();
        }
    }

    /**
     * After receiving the starting url, it starts processing the first page. Processing the first page is also storing the found urls.
     * In the next round, you will start queuing the urls already stored in the database.
     */
    private function startCrawler()
    {
        /*$req = new HTTP_Request("http://example.com/");
        $req->setProxy("192.168.5.254", 3128);*/
        $log = new WLog();
        $log->m_log('Start WebCrawler');
        
        // Define Seed Settings
        $seed_url = $_POST['url'];
        $seed_components = parse_url($seed_url);
        if ($seed_components === false) {
            die('Unable to Seed Parse URL');
        }
        $seed_scheme = $seed_components['scheme'];
        $seed_host = $seed_components['host'];
        $url_start = $seed_scheme . '://' . $seed_host;
        // Download Seed URL

        $parsePage = new ParsePage();
        $parsePage->target = $seed_url;
        $parsePage->referer = "";
        /*var_dump($seed_components);
        die();
        $parsePage->path = ($seed_components['path'] == '')? '/':$seed_components['path'];*/
        $parsePage->path =$seed_components['path'];
        $parsePage->parsePage();
        // Loop through all pages on site.

        $mySql = new DbMysql();
        while (1) {
            $counter = 0;
            $rowCount = $mySql->getLinks();
            if ($rowCount) {
                for ($i = 0; $i < $rowCount; $i ++) {
                    $row = $mySql->getLinkRow();
                    if ($row !== false) {
                        $path = $row['path'];
                        $referer = $row['referer'];
                        // Check if first character isn't a '/'
                        if ($path[0] != '/') {
                            continue;
                        }
                       /* echo '<pre>';
                        print_r($row);
                        echo '</pre>';*/
                        $path = $row['path'];
                        $referer = $row['referer'];
                        $parsePage = new ParsePage();
                        $parsePage->target = $url_start . $path;
                        $parsePage->referer = $referer;
                        $parsePage->path = $path;

                        if ($parsePage->parsePage()) {
                            $counter ++;
                        }
                        sleep(1);
                    }
                }
            } else {
                die("Unable to select un-downloaded pages\n");
            }
            if ($counter == 0) {
                break;
            }
        }
    }
}

?>
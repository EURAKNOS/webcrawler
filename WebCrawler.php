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
    public $html;

    public function __construct()
    {}

    /**
     * INDEX page.
     * Form website URL
     */
    public function mainPage()
    {
        // $this->process();(Finished) https://eurodairy.eu/resources/two-webinars-on-the-principles-of-biodiversity/
        // $this->getData();
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

                   <!-- Modal -->
        <div class="modal fade" id="submitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="WebCrawlerModalLabel">Warning!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"></span>
                </button>
              </div>
              <div class="modal-body">
                The specified website is already in the database. If you want to delete it again, the previous data will be lost.
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="accept-download" class="btn btn-success">Download accepted</button>
              </div>
            </div>
          </div>
        </div>
              <!-- Top Navigation Menu -->
            <div class="topnav">
              <a class="active navbar-brand" href="/">
                    <img src="style/images/logo-white_notext2.png" class="d-inline-block align-top" alt="">
                    EURAKNOS WEBCRAWLER
            	</a>
              <!-- Navigation links (hidden by default) -->
              <div id="myLinks">
                <a class="nav-link" href="new.php">Add Crawler</a>
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
                    	<a class="nav-link" href="new.php">Add Crawler</a>
                  	</li>
                </ul>
            </div>
            
            <div class="container-fluid">
            	<!-- Modal -->
            
                <div class="row justify-content-center">
         
        <form action="' . ROOT_PATH . '" method="post" name="webcrawler">
       
        <div class="description">
        <p>Please enter the URL of the webpage you want to save</p>
        </div>
        <div class="row" style="/* border: 1px solid red; */">
            <div class="col-lg-12">
                <div class="input-group input-group-lg mb-3">
                    <input type="text" class="form-control" id="url" name="url" placeholder="Starting URL" aria-label="URL" aria-describedby="button-addon2" style="color: #b5b5b5;" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary downloadbutton" type="submit" id="submit" >START</button>
                    </div>
                </div>
                <div class="input-group input-group-lg mb-3">
                    <input type="text" class="form-control" id="match_url" name="match_url" placeholder="Matching URL (Optional)" aria-label="URL" aria-describedby="button-addon2" style="color: #b5b5b5;" required>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="input-group input-group-lg mb-4">
                    <input type="text" class="form-control" id="w-name" name="wname" placeholder="The name of the website" aria-label="URL" aria-describedby="button-addon2" style="color: #b5b5b5;" required>
                </div>
            </div>
            <div class="col-lg-12">
            <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="external" value="1" id="external" checked>
                    <label class="form-check-label"	for="meta-title"> Download external contents</label>
                </div>
            </div>
        </div>
        <h3 class="other-element">General elements</h3>
        <div class="row">
            <div class="col-lg-3 offset-md-3">
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
        <div class="col-lg-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="h1" value="1" id="h1">
                <label class="form-check-label"	for="h1"> Heading 1</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="h2" value="1" id="h2">
                <label class="form-check-label"	for="h2"> Heading 2</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="h3" value="1" id="h3">
                <label class="form-check-label"	for="h3"> Heading 3</label>
            </div>
        </div>
        </div>
        
        <h3 class="other-element">Other searching elements</h3>
        <div class="row">
            <div class="col-lg-12">
                <div id="newRow"></div>
                <button id="addRow" type="button" class="btn btn-info">Add Row</button>
            </div>
        </div>
    </form>
     
</div>
<div id="status" class="status"></div>
          	</div>
           
           	
            
            <div class="version"><p>Ver. ' . VERSION . '</p></div>
            <script src="style/js/javascript.js"></script>
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
            </html>');
        print($this->html);
    }

    public function getData()
    {
        $this->MySql = new DbMysql();
        $this->getDownlodedPages();
    }

    public function getDownlodedPages()
    {
        $this->MySql->getDownlodedPages();
        $this->result = $this->MySql->result;
        $this->resultTemplate();
    }

    public function resultTemplate()
    {
        $this->htmlResult = '<table class="table table-striped table-dark"><thead><tr>
        <th scope="col">PATH</th>
        <th scope="col">REFERER</th>
        <th scope="col">DOWNLOAD TIME</th>
        </tr>
        </thead><tbody>';
        foreach ($this->result as $item) {
            $this->htmlResult .= '<tr>
            <th scope="row">' . $item['path'] . '</th>
            <td>' . $item['referer'] . '</td>';
            if ($item['download_time'] && $item['download_time'] > 0) {
                $this->htmlResult .= '<td>' . date("Y-m-d H:i:s", $item['download_time']) . '</td>';
            } else {
                $this->htmlResult .= '';
            }
            $this->htmlResult .= '</tr>';
        }
        $this->htmlResult .= '</tbody></table>';
    }
}

?>
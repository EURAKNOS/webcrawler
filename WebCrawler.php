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

    public $version = '0.2.0';

    public $html;

    public function __construct()
    {}

    /**
     * INDEX page.
     * Form website URL
     */
    public function mainPage()
    {
        //$this->process();
        //$this->getData();
        $this->template();
    }

    /**
     * Preparation of the frontend surface.
     * (Due to the minimal frontend part, I don't create a separate file for it or use a temaplet manager.)
     */
    public function template()
    {   
        $this->html .= ('<html><head><meta charset="utf-8"><title>Euraknos WebCrawler</title><link rel="stylesheet" href="style/css/bootstrap.min.css"><link rel="stylesheet" href="style/css.css"><link href="https://fonts.googleapis.com/css?family=Lato:400,700" rel="stylesheet" type="text/css"><script src="style/js/jquery.min.js"></script><script src="style/js/bootstrap.min.js"></script><script src="style/js/main.js"></script></head>');
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
        /*if (isset($this->htmlResult)) {
            $this->html .= $this->htmlResult;
        }*/
        $this->html .= ('<div class="status"></div>');
        $this->html .= ('<div class="version"><p>Ver. ' . $this->version . '</p></div><script src="style/javascript.js"></script>');
        $this->html .= ('</body>');
        $this->html .= ('</html>');
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
        foreach ($this->result as $item){
            $this->htmlResult .= '<tr>
            <th scope="row">' . $item['path'] . '</th>
            <td>' . $item['referer'] . '</td>';
            if ($item['download_time'] && $item['download_time'] > 0) {
                $this->htmlResult .=  '<td>' . date("Y-m-d H:i:s", $item['download_time']). '</td>';
            } else {
                $this->htmlResult .= '';
            }
            $this->htmlResult .= '</tr>';
        }
        $this->htmlResult .= '</tbody></table>';
    }
        
}

?>
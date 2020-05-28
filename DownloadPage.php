<?php
require_once 'vendor/pdfinfo.php';
require_once 'vendor/PNGMetadata/src/PNGMetadata.php';
require_once 'vendor/docx_metadata.php';
require_once 'vendor/vimeo/src/Vimeo/Vimeo.php';
require_once 'vendor/php-epub-meta/epub.php';
require_once 'vendor/swfheader/swfheader.class.php';
require_once 'vendor/pdfparser-master/vendor/autoload.php';
require_once 'vendor/getID3-master/getid3/getid3.php';


use Nesk\Rialto\Data\JsFunction;
use Nesk\Rialto\Exceptions\IdleTimeoutException;
use PNGMetadata\PNGMetadata;
/**
 * Download Actual URL
 * @author szabo
 *
 */
class DownloadPage {
    
    public $target; // Url to download
    
    public $referer;
    
    public $result;
    
    public $log;
    
    public $urlId;
    
    public $browser;
    
    public function __construct()
    {
        $this->log = new WLog();
    }
    
    /**
     * Determines the type of link.
     * It can be a document, but it can even be a web page.
     */
    public function downloadData()
    {
        $cnt = 0;
        $err_c = 1;
        $this->log->m_log($this->target . '------1');
        while ($cnt < 3 && $err_c == 1) {
            $err_c = $this->urlCheck();
            $cnt++;
        }
        $this->log->m_log($this->target . '------2');
        if ($err_c != 2) {
            $contents['error_page'] = 1;
            return $contents;
        }
        $file_headers = @get_headers($this->target);
        $this->log->m_log('f');
        $pos = strpos($this->target, 'https://www.youtube.com');
        $posYoutube2 = strpos($this->target, 'youtu.be');
        $posVimeo = strpos($this->target, '.vimeo.com');
        $posSpotify = strpos($this->target, '.spotify.com');
        $posMaps = strpos($this->target, 'www.google.com/maps');
        $posMaps2 = strpos($this->target, 'maps.google.com');
        
        

        if ($pos !== false || $posYoutube2 !== false) {
            $this->processYoutube();
        } elseif ($posVimeo !== false) {
            $this->processVimeo();
        } elseif ($posSpotify !== false) {
            $this->processSpotify();
        } elseif ($posMaps !== false || $posMaps2 !== false) {
            $this->processMaps();
        }else {
            $info = pathinfo($this->target);
            
            if (isset($info["extension"])) {
                $info["extension"] = strtolower($info["extension"]);
                if ($info["extension"] == "pdf") {
                    return $this->processPdf();
                } elseif ($info["extension"] == "jpg") {
                    return $this->processJpg();
                } elseif ($info["extension"] == "png") {
                    return $this->processPng();
                } elseif ($info["extension"] == "pptx") {
                    return $this->processPptx();
                } elseif ($info["extension"] == "ppt") {
                    return $this->processPpt();
                } elseif ($info["extension"] == "docx") {
                    return $this->processDocx();
                } elseif ($info["extension"] == "xlsx") {
                    return $this->processXlsx();
                } elseif ($info["extension"] == "epub") {
                    return $this->processEpub();
                } elseif ($info["extension"] == "swf") {
                    return $this->processSwf();
                } elseif ($info["extension"] == "mp4") {
                    return $this->processMp4();
                } elseif ($info["extension"] == "zip") {
                    return $this->processZip();
                } elseif ($info["extension"] == "svg") {
                    return $this->processSvg();
                } elseif (isset($file_headers['3']) && $file_headers['3'] == 'Content-Type: application/octet-stream') {
                } else {
                    return $this->dinamicDownloadPage();
                }
            } elseif ($file_headers['3'] == 'Content-Type: application/octet-stream') { 
                
            } else {
                return $this->dinamicDownloadPage();
            }
        }
    }
    
    /**
     * Check 301 and 404 urls
     */
    private function urlCheck()
    {
        $this->log->m_log($this->target . '------3');
        $file_headers = @get_headers($this->target);
        print_r($file_headers);
        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $this->log->m_log('HTTP/1.1 404 Not Found:' . $this->target);
            return 0;
        } elseif (isset($file_headers[0]) && $file_headers[0] == 'HTTP/1.1 401 Unauthorized') {
            $this->log->m_log('HTTP/1.1 401 Unauthorized:' . $this->target);
            return 0;
        } elseif (isset($file_headers[0]) && $file_headers[0] == 'HTTP/1.1 403 Forbidden') {
            $this->log->m_log('HTTP/1.1 403 Forbidden:' . $this->target);
            return 0;
        } elseif (isset($file_headers[0]) && $file_headers[0] == 'HTTP/1.0 301 Moved Permanently') {
            $this->log->m_log('HTTP/1.0 301 Moved Permanently:' . $this->target . ' changeto: ' . $file_headers[5]);
            $this->target = ltrim($file_headers[5], 'Location: ');
            return 1;
        } elseif (isset($file_headers[0]) && $file_headers[0] == 'HTTP/1.1 301 Moved Permanently') {
            $this->target = ltrim($file_headers[12], 'Location: ');
            return 1;
        } elseif (isset($file_headers[0]) && $file_headers[0] == 'HTTP/1.0 302 Moved Temporarily') {
            $this->target = ltrim($file_headers[1], 'Location: ');
            return 1;
        } elseif (isset($file_headers[0]) && $file_headers[0] == 'HTTP/1.1 302 Found') {
            $tmp = parse_url($this->target);
            $this->target = $tmp['scheme'] .'://'. $tmp['host'] . ltrim($file_headers[3], 'Location: ');
            return 1;
        } else {
            return 2;
        }
    }
    
    /**
     * Website download from url.
     * @return array
     */
    public function DownloadPage()
    {
        $this->log->m_log('Start download (DownloadPage) content');
        $handle = curl_init();
        //Define Settings Curl
        
        curl_setopt ( $handle, CURLOPT_HTTPGET, true );
        curl_setopt ( $handle, CURLOPT_HEADER, true );
        curl_setopt ( $handle, CURLOPT_COOKIEJAR, "cookie_jar.txt" );
        curl_setopt ( $handle, CURLOPT_COOKIEFILE, "cookies.txt" );
        curl_setopt ( $handle, CURLOPT_USERAGENT, "web-crawler-tutorial-test" );
        curl_setopt ( $handle, CURLOPT_TIMEOUT, 300);
        //curl_setopt ( $handle, CURLOPT_PROXY, "192.168.5.254:3128");
        curl_setopt ( $handle, CURLOPT_URL, $this->target );
        curl_setopt ( $handle, CURLOPT_REFERER, $this->referer );
        curl_setopt ( $handle, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt ( $handle, CURLOPT_MAXREDIRS, 4 );
        curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
        //Execute Request
        $output = curl_exec ( $handle );
        //Close cURL handle
        curl_close ( $handle );
        
        //Separate Header and Body
        $separator = "\r\n\r\n";
        $header = substr( $output, 0, strpos( $output, $separator ) );
        $body_start = strlen( $header ) + strlen( $separator );
        $body = substr( $output, $body_start, strlen( $output ) - $body_start );
        //Parse Headers
        $header_array = Array();
        foreach ( explode ( "\r\n", $header ) as $i => $line ) {
            if($i === 0) {
                $header_array['http_code'] = $line;
                $status_info = explode( " ", $line );
                $header_array['status_info'] = $status_info;
            } else {
                list ( $key, $value ) = explode ( ': ', $line );
                $header_array[$key] = $value;
            }
        }
        //Form Return Structure
        $ret = Array("headers" => $header_array, "body" => $body );
        $this->log->m_log('End download (DownloadPage) content');
        return $ret;
    }	
        
    public function dinamicDownloadPage()
    {
        $this->log->m_log('Start download (DinamicDownloadPage) content');
        
        $cnt = 0;
        $ddps = 0;
        while ($cnt < 3 && $ddps < 2) {
            $res = $this->dinamicDownloadPageProcess();
            $ddps = $res['status'];
            $cnt++;
        }        
        $data = $res['data'];
        $this->log->m_log('End download (DownloadPage) content');
        if ($data != '') {
            $headers['status_info'][1] = 200;
        } else {
            $headers['status_info'][1] = 0;
        }
        return array("headers" => $headers, "body" => $data);
    }
    
    private function dinamicDownloadPageProcess(){
        try {
            //$browser = $puppeteer->launch();
            $page = $this->browser->newPage();
            $page->goto($this->target, [ 'waitUntil' => 'networkidle0' ]);
            //  $page->goto($this->target);
            //$page->waitFor(10000);
            //$page->waitForNavigation();
            $data = $page->evaluate(JsFunction::createWithBody('return document.documentElement.outerHTML'));
            $page->close();
            return array('data' => $data, 'status' => 2);
        } catch (IdleTimeoutException $e) {
            $this->log->m_log('IdleTimeoutException:' . $this->target);
            $this->log->m_log($e);
            return array('data' => '', 'status' => 1);
        } catch (Throwable $t) {
            $this->log->m_log('Unknown exception:' . $this->target);
            $this->log->m_log($t);
            return array('data' => '', 'status' => 2);
        } catch (Exception $e) {
            $this->log->m_log('Unknown exception:' . $this->target);
            $this->log->m_log($e);
            return array('data' => '', 'status' => 2); // Executed only in PHP 5.x, will not be reached in PHP 7
        }
    }
    
    /**
     * Download PDF file and get info
     */
    /*public function processPdf()
    {
        $this->log->m_log('Start download pdf');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_PDF;
        $dl->downloadProcessing();
        
        $p = new PDFInfo;
        $result = $p->load($dl->localfile);
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'pdf';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
    }*/
    
    
    public function processPdf()
    {
        $this->log->m_log('Start download pdf');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_PDF;
        $dl->downloadProcessing();
        // Parse pdf file and build necessary objects.
        $saveData['meta_data'] = '';
        $parser = new \Smalot\PdfParser\Parser();
        try {
            $pdf    = $parser->parseFile($dl->localfile);
            
            // Retrieve all details from the pdf file.
            $details  = $pdf->getDetails();
            
            $result = $details;
        } catch (Exception $e) {
            $this->log->m_log($e);
        }
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'pdf';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
        
    }
    
    /**
     * Download and store jpg metadata and file
     * @return array
     */
    public function processJpg()
    {
        $this->log->m_log('Start download jpg');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_JPG;
        $dl->downloadProcessing();
        
        
        $result = @exif_read_data($dl->localfile);
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'jpg';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
          
    }
    
    /**
     * Download and store png metadata and file
     * @return array
     */
    public function processPng()
    {
        $this->log->m_log('Start download png');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_PNG;
        $dl->downloadProcessing();
        
        $png_metadata = new PNGMetadata($dl->localfile);
      
        $this->log->m_log('PNG metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($png_metadata) && !empty($png_metadata)) {
            $saveData['meta_data'] = serialize($png_metadata);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'png';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
    }
    
    /**
     * Download and store pptx metadata and file
     * @return array
     */
    public function processPptx()
    {
        $this->log->m_log('Start download pptx');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_PPTX;
        $dl->downloadProcessing();
        
        
        $docxmeta = new docxmetadata();
        $docxmeta->setDocument($dl->localfile);
        $result = $docxmeta->allData();
        
        $this->log->m_log('PPTX metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'pptx';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
        
    }
    
    /**
     * Download and store pptx metadata and file
     * @return array
     */
    public function processPpt()
    {
        $this->log->m_log('Start download ppt');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_PPT;
        $dl->downloadProcessing();
        
        $this->log->m_log('PPT metadata Ok');
        $saveData['meta_data'] = '';
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'ppt';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
        
    }
    
    /**
     * Download and store docx metadata and file
     * @return array
     */
    public function processDocx()
    {
        $this->log->m_log('Start download Docx');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_DOCX;
        $dl->downloadProcessing();
        
        
        $docxmeta = new docxmetadata();
        if($docxmeta->setDocument($dl->localfile)) {
            $result = $docxmeta->allData();
        }
        
        $this->log->m_log('DOCX metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'docx';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
        
    }
    
    /**
     * Download and store xlsx metadata and file
     * @return array
     */
    public function processXlsx()
    {
        $this->log->m_log('Start download xlsx');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_XLSX;
        $dl->downloadProcessing();
        
        
        $docxmeta = new docxmetadata();
        $docxmeta->setDocument($dl->localfile);
        $result = $docxmeta->allData();
        
        $this->log->m_log('XLSX metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'xlsx';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
        
    }
    
    /**
     * Youtube metadata
     */
    public function processYoutube()
    {
        $this->log->m_log('Start youtube meta');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = '';
        $dl->preSaveDatabaseDownlodedFile();
        
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $this->target, $match);
        $videoId = $match[1];
        
        $googleApiUrl = 'https://www.googleapis.com/youtube/v3/videos?id=' . $videoId . '&key=' . YOUTUBE_API_KEY . '&part=snippet';
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        
        curl_close($ch);
        
        $data = json_decode($response);
        
        $value = json_decode(json_encode($data), true);
        
        $saveData['meta_data'] = '';
        if (isset($value) && !empty($value)) {
            $saveData['meta_data'] = serialize($value);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'youtube_video';
        
        // File data Save Database
        $dl->saveData = $saveData;
        $this->log->m_log('Start youtube success');
        return $dl->saveEnd();
    }
    
    private function processVimeo()
    {
        $this->log->m_log('Start Vimeo meta');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = '';
        $dl->preSaveDatabaseDownlodedFile();
        
        if(preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $this->target, $match)) {
            //echo "Vimeo ID: $match[5]";
        }
        $path = $match[5];
        $vimeo = new Vimeo(VIMEO_API_KEY, VIMEO_API_SECRET, VIMEO_API_TOKEN);
        
        //Get a video - https://developer.vimeo.com/api/playground/videos/{video_id}
        $result = $vimeo->request("/videos/$path", array(
            'fields' =>     'uri,name,description,duration,width,height,privacy,pictures.sizes'
        ));
        
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = ''; //$dl->localfile;
        $saveData['file_type'] = 'vimeo_video';
        
        // File data Save Database
        $dl->saveData = $saveData;
        $this->log->m_log('Start Vimeo success');
        return $dl->saveEnd();
        
    }
    
    private function processSpotify()
    {
        $this->log->m_log('Start spotify');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = '';
        $dl->preSaveDatabaseDownlodedFile();
        
        $saveData['meta_data'] = '';
        $saveData['meta_data'] = serialize($this->target);
        
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = ''; //$dl->localfile;
        $saveData['file_type'] = 'spotify';
        
        // File data Save Database
        $dl->saveData = $saveData;
        $this->log->m_log('Start spotify');
        return $dl->saveEnd();
    }
    
    private function processMaps()
    {
        $this->log->m_log('Start google map');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = '';
        $dl->preSaveDatabaseDownlodedFile();
        
        $saveData['meta_data'] = '';
        $saveData['meta_data'] = serialize($this->target);
        
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = ''; //$dl->localfile;
        $saveData['file_type'] = 'google_map';
        
        // File data Save Database
        $dl->saveData = $saveData;
        $this->log->m_log('Start google map');
        return $dl->saveEnd();
    }

     /**
     * Epub metadata
     */
    private function processEpub()
    {
        $this->log->m_log('Start download epub');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_EPUB;
        $dl->downloadProcessing();
        
        $epub = new EPub($dl->localfile);
        
        $result['Authors'] = $epub->Authors();
        $result['Title'] = $epub->Title();
        $result['Language'] = $epub->Language();
        $result['Publisher'] = $epub->Publisher();
        $result['Copyright'] = $epub->Copyright();
        $result['Description'] = $epub->Description();
        $result['ISBN'] = $epub->ISBN();
        $result['Google'] = $epub->Google();
        $result['Amazon'] = $epub->Amazon();
        $result['Subjects'] = $epub->Subjects();
        
        $this->log->m_log('EPUB metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'epub';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
    }
    
    private function processSwf()
    {        
        $this->log->m_log('Start download swf');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_SWF;
        $dl->downloadProcessing();
        
        $swf = new swfheader();
        $swf->swfheader();
        $swf->loadswf($dl->localfile);

        $result['fname'] = $swf->fname ;				// SWF file analyzed
        $result['magic'] = $swf->magic ;				// Magic in a SWF file (FWS or CWS)
        $result['compressed'] = $swf->compressed ;		// Flag to indicate a compressed file (CWS)
        $result['version'] = $swf->version ;			// Flash version
        $result['size'] = $swf->size ;					// Uncompressed file size (in bytes)
        $result['width'] = $swf->width ;				// Flash movie native width
        $result['height'] = $swf->height ;				// Flash movie native height
        $result['valid'] = $swf->valid ;				// Valid SWF file
        $result['fps'] = $swf->fps ;					// Flash movie native frame-rate
        $result['frames'] = $swf->frames ;
        
        $this->log->m_log('SWF metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'swf';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
    }
    
    private function processMp4()
    {
        $this->log->m_log('Start download mp4');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_MP4;
        $dl->downloadProcessing();
        
        $getID3 = new getID3;
        $result = $getID3->analyze($dl->localfile);
        
        $this->log->m_log('MP4 metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'mp4';
       
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
    }
    
    private function processZip()
    {
        $this->log->m_log('Start download zip');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_ZIP;
        $dl->downloadProcessing();
        
        $za = new ZipArchive();
        
        $za->open($dl->localfile);
        $result['zip'] = $za;
        
        
        
        $zip = zip_open($dl->localfile);
        $cnt = 1;
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                $result['files'][$cnt]["Name"] = zip_entry_name($zip_entry);
                $result['files'][$cnt]["Actual Filesize"] = zip_entry_filesize($zip_entry);
                $result['files'][$cnt]["Compressed Size"] = zip_entry_compressedsize($zip_entry);
                $result['files'][$cnt]["Compression Method"] = zip_entry_compressionmethod($zip_entry);
                $cnt++;
            }
            zip_close($zip);
        }
        
        $this->log->m_log('ZIP metadata Ok');
        $saveData['meta_data'] = '';
        if (isset($result) && !empty($result)) {
            $saveData['meta_data'] = serialize($result);
        }
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'zip';
        
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
    }
    
    /**
     * Download and store jpg metadata and file
     * @return array
     */
    public function processSvg()
    {
        $this->log->m_log('Start download svg');
        $dl = new DownloadFileExtended();
        $dl->urlId = $this->urlId;
        $dl->target = $this->target;
        $dl->folder = FOLDER_SVG;
        $dl->downloadProcessing();
        
        $saveData['meta_data'] = '';
        $saveData['id'] = $dl->id;
        $saveData['local_location'] = $dl->localfile;
        $saveData['file_type'] = 'svg';
        
        // File data Save Database
        $dl->saveData = $saveData;
        return $dl->saveEnd();
        
    }
    
}

/**
 * File Download
 *
 * Saves the file to be processed to a database.
 * The download to the local machine will then take place.
 * Returns control to the function for the current file type.
 * It then saves the obtained values ​​in the database.
 *
 * @author szabo
 *
 */
class DownloadFileExtended {
    
    public $buffer = 1024;
    
    public $target;
    
    public $folder;
    
    public $localfile;
    
    public $saveData;
    
    public $MySql;
    
    public $id;
    
    public $log;
    
    public $urlId;
    
    public function __construct()
    {
        $this->log = new WLog();
    }
    
    public function downloadProcessing()
    {
        $this->preSaveDatabaseDownlodedFile();
        $this->downloadFile();
    }
    
    /**
     * Presave to database
     */
    public function preSaveDatabaseDownlodedFile()
    {
        $this->log->m_log('Start file download presave to database');
        $this->MySql = new DbMysql();
        $this->MySql->urlId = $this->urlId;
        $this->MySql->target = $this->target;
        $this->MySql->startDownloadFile();
        $this->id = $this->MySql->id;
    }
    
    public function downloadFile()
    {
        $this->log->m_log('Create folder: ' . FOLDER_DEFAULT . "/" . $this->folder . "/" . $this->id);
        if (!file_exists(FOLDER_DEFAULT . "/" . $this->folder . "/" . $this->id)) {
            mkdir(FOLDER_DEFAULT . "/" . $this->folder . "/" . $this->id, 0777, true);
            $this->log->m_log('Create folder success: ' . FOLDER_DEFAULT . "/" . $this->folder . "/" . $this->id);
        }
        
        
       /* $opts= array(
            'http' => array(
                'proxy' => 'tcp://192.168.5.254:3128'
            )
        );
        $context = stream_context_create($opts);*/ 
        
        //$downloadedFile = fopen($this->target, 'rb', false, $context);
        $downloadedFile = fopen($this->target, 'rb');
        if (!$downloadedFile) {
            $this->log->m_log('Error download file from url : ' . $this->target);
            return false;
        }
        $this->localfile = FOLDER_DEFAULT . "/" . $this->folder . "/" . $this->id . '/' . basename($this->target);
        $lFile = fopen($this->localfile, 'wb');
        if (!$lFile) {
            $this->log->m_log('Error open localfile : ' . $this->localfile);
            fclose($downloadedFile);
            return false;
        }
        
        while ($buffer = fread($downloadedFile, $this->buffer)) {
            fwrite($lFile, $buffer);
        }
        
        fclose($lFile);
        $this->log->m_log('localfile close success');
        fclose($downloadedFile);
        $this->log->m_log('targetUrlFile close success');
        
        return true;
    }
    
    public function saveEnd()
    {
        $this->MySql->data = $this->saveData;
        if ($this->MySql->endDownloadFile()) {
            $fileDownload['ok'] = 1;
        }
        $this->log->m_log('Downlod success');
        return $fileDownload;
    }
    
}


/**
 * File Download
 * @author szabo
 *
 */
class DownloadFile {
    
    public $buffer = 1024;
    
    public $location;
    
    public $localFile;
    
    public function __construct()
    {
        
    }
    
    public function downloadFile()
    {
        if (!file_exists($this->folder)) {
            mkdir($this->folder, 0777, true);
        }
        $downloadedFile = fopen($this->location, 'rb');
        if (!$downloadedFile) {
            return false;
        }
        
        $lFile = fopen(FOLDER_DEFAULT . '/' . $this->localFile, 'wb');
        if (!$lFile) {
            fclose($downloadedFile);
            return false;
        }
        
        while ($buffer = fread($downloadedFile, $this->buffer)) {
            fwrite($lFile, $buffer);
        }
        
        fclose($lFile);
        fclose($downloadedFile);
        
        return true;
    }
    
}
<?php
// import the Joserick PNGMetadata
require_once 'vendor/pdfinfo.php';
require_once 'vendor/PNGMetadata/src/PNGMetadata.php';
require_once 'vendor/docx_metadata.php';

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
        $pos = strpos($this->target, 'https://www.youtube.com');
        if ($pos !== false) {
            $this->processYoutube();
        } else {
            $info = pathinfo($this->target);
            if (isset($info["extension"])) {

                if ($info["extension"] == "pdf") {
                    return $this->processPdf();
                } elseif ($info["extension"] == "jpg") {
                    return $this->processJpg();
                } elseif ($info["extension"] == "png") {
                    return $this->processPng();
                } elseif ($info["extension"] == "pptx") {
                    return $this->processPptx();
                } elseif ($info["extension"] == "docx") {
                    return $this->processDocx();
                } elseif ($info["extension"] == "xlsx") {
                    return $this->processXlsx();
                }  else {
                    return $this->DownloadPage();
                }
            } else {
                return $this->DownloadPage();
            }
        }
    }
    
    /**
     * Website download from url.
     * @return array
     */
    public function DownloadPage()
    {
        $this->log->m_log('Start download (downloadFile) content');
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
        $this->log->m_log('End download (downloadFile) content');
        return $ret;
    }
    
    /**
     * Download PDF file and get info
     */
    public function processPdf()
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
        
        
        $result = exif_read_data($dl->localfile);
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
        $docxmeta->setDocument($dl->localfile);
        $result = $docxmeta->allData();
        
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
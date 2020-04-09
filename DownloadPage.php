<?php
/**
 * Download Actual URL
 * @author szabo
 *
 */
class DownloadPage {
    
    public $target; // Url to download
    
    public $referer;
    
    public $result;
    
    public function __construct()
    {
        
    }
    
    /**
     * Determines the type of link.
     * It can be a document, but it can even be a web page.
     */
    public function downloadData()
    {
        $info = pathinfo($this->target);
        if (isset($info["extension"])) {
            if ($info["extension"] == "pdf") {
                $this->processPdf();
            } else {
                $this->DownloadPage();
            }
        }
    }
    
    /**
     * Website download from url.
     * @return array
     */
    public function DownloadPage()
    {
        $handle = curl_init();
        //Define Settings Curl
        curl_setopt ( $handle, CURLOPT_HTTPGET, true );
        curl_setopt ( $handle, CURLOPT_HEADER, true );
        curl_setopt ( $handle, CURLOPT_COOKIEJAR, "cookie_jar.txt" );
        curl_setopt ( $handle, CURLOPT_COOKIEFILE, "cookies.txt" );
        curl_setopt ( $handle, CURLOPT_USERAGENT, "web-crawler-tutorial-test" );
        curl_setopt ( $handle, CURLOPT_TIMEOUT, 300);
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
        return $ret;
    }
    
    /**
     * Download PDF file and get info
     */
    public function processPdf()
    {
        
    }
    
    
    
}
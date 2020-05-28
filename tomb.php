<?php
/*require_once 'WebCrawler/vendor/docx_metadata.php';

$docxmeta = new docxmetadata();
$docxmeta->setDocument('members_Bioeast_NL_V2_Final_opened_Apr_8_2020.xlsx');
$result = $docxmeta->allData();
echo '<pre>';
echo 'lefut';
print_r($result);
echo '</pre>';*/

/*$string = 'a:8:{s:8:"FileName";s:54:"wisf_sr-n0ar7bevr64lbqai7ng6ycaaqd5wab1hlmnwqf453k.jpg";s:12:"FileDateTime";i:1588056606;s:8:"FileSize";i:12376;s:8:"FileType";i:2;s:8:"MimeType";s:10:"image/jpeg";s:13:"SectionsFound";s:7:"COMMENT";s:8:"COMPUTED";a:4:{s:4:"html";s:24:"width="200" height="200"";s:6:"Height";i:200;s:5:"Width";i:200;s:7:"IsColor";i:1;}s:7:"COMMENT";a:1:{i:0;s:57:"CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), quality = 82";}}';
echo '<pre>';
print_r(json_decode($string));
echo '</pre>';*/
/*$str = "http://maps.google.com/maps?q=Calle%20Joaqu%C3%ADn%20Rodrigo%2C%20S%2FN%2004720%20%E2%80%93%20Roquetas%20de%20Mar%20%28Almer%C3%ADa%29&amp;output=embed";

// Or we can write ltrim($str, $str[0]);
$str = ltrim($str, '//');

echo $str; */
/*echo '<pre>';
$file_headers = @get_headers('https://www.aki.gov.hu/');
var_dump($file_headers);
echo 'ok';*/
/*$file_headers = @get_headers('http://www.ns.nl');
var_dump($file_headers);
echo 'ok';
$tmp = parse_url('http://www.ns.nl');
$target = $tmp['scheme'] .'://'. $tmp['host'] . ltrim($file_headers[3], 'Location: ');
echo $target;
$file_headers2 = @get_headers($target);
var_dump($file_headers2);*/

/*$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://www.ns.nl',
    CURLOPT_HEADER => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_NOBODY => true));

$header = explode("\n", curl_exec($curl));
curl_close($curl);

print_r($header);*/


function check($url, $ignore = '')
{
    $agent = "Mozilla/4.0 (B*U*S)";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 45);
    
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5); //follow up to 10 redirections - avoids loops
    
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //fix for certificate issue
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //fix for certificate issue
    
    
    $page = curl_exec($ch);
    $err = curl_error($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $codes = array(
        0 => 'Domain Not Found',
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );
    
    $httpcode_out = 'http: ' . $httpcode . ' (' . $codes[$httpcode] . ')';
    $err = 'curl error: ' . $err;
    
    $out = array(
        $url,
        $httpcode_out,
        $err
    );
    
    if ($httpcode >= 200 && $httpcode < 307)
    {//good
        return array(
            'Work',
            $out
        );
    }
    else
    {//BAD
        return array(
            'Fail',
            $out
        );
    }
}
check('http://www.ns.nl');

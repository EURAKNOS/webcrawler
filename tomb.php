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
echo '<pre>';
$file_headers = @get_headers('https://www.aki.gov.hu/');
var_dump($file_headers);
echo 'ok';
/*$file_headers = @get_headers('http://www.ns.nl');
var_dump($file_headers);
echo 'ok';
$tmp = parse_url('http://www.ns.nl');
$target = $tmp['scheme'] .'://'. $tmp['host'] . ltrim($file_headers[3], 'Location: ');
echo $target;
$file_headers2 = @get_headers($target);
var_dump($file_headers2);*/
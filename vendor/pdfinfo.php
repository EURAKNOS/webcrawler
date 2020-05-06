<?php

/**
 * Get PDF META DATA
 */

class PDFInfo
{
    
    public $prop;
    
    public function load($file)
    {
        $f = fopen($file, 'rb');
        if (! $f) return false;
           
        // Read the last 16KB
        fseek($f, - 16384, SEEK_END);
        $s = fread($f, 16384);
        // Extract cross-reference table and trailer
        if (! preg_match("/xref[\r\n]+(.*)trailer(.*)startxref/s", $s, $a))
            return false;
            $xref = $a[1];
            $trailer = $a[2];
            
            // Extract Info object number
            if (! preg_match('/Info ([0-9]+) /', $trailer, $a))
                return false;
                $object_no = $a[1];
                
                // Extract Info object offset
                $lines = preg_split("/[\r\n]+/", $xref);
                if (isset($lines[1 + $object_no])) {
                    $line = $lines[1 + $object_no];
                    $offset = (int) $line;
                    if ($offset == 0)
                        return false;
                }
                if (isset($offset)) {
                    // Read Info object
                    fseek($f, $offset, SEEK_SET);
                    
                    $s = fread($f, 1024);
                }
                // Extract properties
                if (! preg_match('/<<(.*)>>/Us', $s, $a)) return false;
                    $n = preg_match_all('|/([a-z]+) ?\((.*)\)|Ui', $a[1], $a);
                    $prop = array();
                    for ($i = 0; $i < $n; $i ++)
                        $prop[$a[1][$i]] = $a[2][$i];
                        
                        
                        $string = file_get_contents($file);
                        
                        if (!isset($prop['Title'])) {
                            $start = strpos($string, "<dc:title>") + 10;
                            $length = strpos(substr($string, $start), '</dc:title>');
                            
                            $prop['Title'] = 'Untitled';
                            if ($length)
                            {
                                $prop['Title'] = strip_tags(substr($string, $start, $length));
                                $prop['Title'] = trim($this->pdfDecTxt($prop['Title']));
                            }
                        }
                        
                        if (!isset($prop['Author'])) {
                            $start = strpos($string, "<dc:creator>") + 12;
                            $length = strpos(substr($string, $start), '</dc:creator>');
                            $prop['Author'] = 'Unknown';
                            
                            if ($length)
                            {
                                $prop['Author'] = strip_tags(substr($string, $start, $length));
                                $prop['Author'] = trim($this->pdfDecTxt($prop['Author']));
                            }
                        }
                        
                        if (preg_match("/\/N\s+([0-9]+)/", $string, $found))
                        {
                            $prop['Pages'] = $found[1];
                        }
                        else
                        {
                            $pos = strpos($string, '/Type /Pages ');
                            if ($pos !== false)
                            {
                                $pos2 = strpos($string, '>>', $pos);
                                $string = substr($string, $pos, $pos2 - $pos);
                                $pos = strpos($string, '/Count ');
                                $prop['Pages'] = (int) substr($string, $pos+7);
                            }
                        }
                        
                        fclose($f);
                        return $prop;
    }
    
    private function pdfDecTxt($txt)
    {
        $len = strlen($txt);
        $out = '';
        $i = 0;
        while ($i<$len)
        {
            if ($txt[$i] == '\\')
            {
                $out .= chr(octdec(substr($txt, $i+1, 3)));
                $i += 4;
            }
            else
            {
                $out .= $txt[$i];
                $i++;
            }
        }
        
        if ($out[0] == chr(254))
        {
            $enc = 'UTF-16';
        }
        else
        {
            $enc = mb_detect_encoding($out);
        }
        
        return iconv($enc, 'UTF-8', $out);
    }
}
<?php

class ParsePage
{

    public $target;

    // Url to download
    public $referer;

    public $path;
    
    public $urlId;
    
    public $browser;

    public function __construct()
    {}

    /**
     * Processing the content of a website
     *
     * @return boolean
     */
    public function parsePage($first = false, $reSearch = false)
    {
        // Create mysql
        $log = new WLog();
        $log->m_log('Targer URL start process: ' . $this->target);
        $MySql = new DbMysql();
        $MySql->target = $this->target;
        $MySql->urlId = $this->urlId;
        
        // Parse URL and get Components
        $url_components = parse_url($this->target);
        if ($url_components === false) {
            $log->m_log('Target error (url_compnents): ' . $this->target);
            $MySql->path = $this->path;
            $MySql->endDownload();
            return false;
        }
        
        if ($first) {
            $MySql->saveUrl();
            $this->urlId = $MySql->urlId;
            session_start();
            $_SESSION['urlid'] = $this->urlId;
            session_write_close();
        }
        if ($reSearch === false) {
            $url_host = $url_components['host'];
            $url_path = '';
            if (array_key_exists('path', $url_components) == false) {
                // If not a valid path, mark as done
                $MySql->startDownload();
                return false;
            } else {
                $url_path = $url_components['path'];
            }
        } else {
            $url_path = $this->target;
        }
        // Download Page
        
        //echo "Downloading: $this->target\n<br>";

        $dwl = new DownloadPage();
        $dwl->target = $this->target;
        $dwl->referer = $this->referer;
        $dwl->urlId = $this->urlId;
        $dwl->browser = $this->browser;
        $contents = $dwl->downloadData();
        //echo "Done\n";
        // Check Status
        if (isset($contents['ok'])) {
            $MySql->path = $this->path;
            $MySql->endDownload();
            return true;
        } elseif (!isset($contents['headers']['status_info'][1]) || $contents['headers']['status_info'][1] != 200) {
            // If not ok, mark as downloaded but skip
            $MySql->path = $this->path;
            if ($reSearch === true) {
                $MySql->statusSaveResearch();
            } else {
                $MySql->statusSave();
            }
            return true;
        } elseif (isset($contents['error_page']) && $contents['error_page'] == 1) {
            $MySql->path = $this->path;
            if ($reSearch === true) {
                $MySql->statusSaveResearch();
            } else {
                $MySql->statusSave();
            }
            $log->m_log('Targer URL is bad: ' . $this->target);
            return true;
        }
       //print_r($contents);
        $MySql->target = $this->target;
        // Parse Contents
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($contents['body']);
        $finder = new DomXPath($doc);

        // Get title
        /*
         * $titleTags = $doc->getElementsByTagName('title');
         * if( count( $titleTags ) > 0 ) {
         * $this->result['title'] = '';
         *
         * $this->result['title'] = $titleTags[0]->nodeValue;
         * }
         */
        // Get Description ------------------------------------------
        $metaTags = $doc->getElementsByTagName('meta');
        foreach ($metaTags as $tag) {
            if (isset($_POST['meta-title'])) {
                if ($tag->getAttribute('name') == 'title') {
                    $this->result['meta-title'] = $tag->getAttribute('content');
                }
            }
            if (isset($_POST['meta-keywords'])) {
                if ($tag->getAttribute('name') == 'keywords') {
                    $this->result['meta-keywords'] = $tag->getAttribute('content');
                }
            }
            if (isset($_POST['meta-description'])) {
                if ($tag->getAttribute('name') == 'description') {
                    $this->result['meta-description'] = $tag->getAttribute('content');
                }
            }
        }

        // Get first h1 -------------------------
        if (isset($_POST['h1'])) {
            $this->result['h1'] = '';
            $h1Tags = $doc->getElementsByTagName('h1');
            if (count($h1Tags) > 0) {
                $this->result['h1'] = $h1Tags[0]->nodeValue;
            }
        }

        // Get first h2 -------------------------
        if (isset($_POST['h2'])) {
            $this->result['h2'] = '';
            $h2Tags = $doc->getElementsByTagName('h2');
            if (count($h2Tags) > 0) {
                $this->result['h2'] = $h2Tags[0]->nodeValue;
            }
        }

        // Get first h3 -------------------------
        if (isset($_POST['h3'])) {
            $this->result['h3'] = '';
            $h3Tags = $doc->getElementsByTagName('h3');
            if (count($h3Tags) > 0) {
                $this->result['h3'] = $h3Tags[0]->nodeValue;
            }
        }

        // Get Other Elements
        if (isset($_POST['class'])) {
            foreach ($_POST['class'] as $key => $item) {
                $classname = $item['name'];
                $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
                if (count($nodes) > 0){
                    for ($i = 0; $i < $nodes->length; $i++ ) {
                        $this->result[$item['name']][$i] = array(
                            'data' => $nodes->item($i)->nodeValue,
                            'title' => $item['title']
                        );
                    }
                }
            }
        }
        $MySql->path = $url_path;
        // save page content
        if ($contents['headers']['status_info'][1] == 200) {
            $MySql->data['page'] = $this->referer;
            $MySql->data['path'] = $this->path;
            $MySql->data['content'] = '';
            if (isset($this->result)) {
                $MySql->data['content'] = serialize($this->result);
            }
            try {
                $MySql->savePage();
            } catch (PDOException $e) {
                $log->m_log('Content save error PDOException exception:' . $this->target);
                $log->m_log($e);
            } catch (Throwable $t) {
                $log->m_log('Content save error Throwable exception:' . $this->target);
                $log->m_log($t);
            } catch (Exception $e) {
                $log->m_log('Content save error Exception exception:' . $this->target);
                $log->m_log($e);
            }
        }
        
        $log->m_log('Page parser end: ' . $this->target);
        $MySql->path = $this->path;
        $MySql->endDownload();
        // Get Links
        if ($reSearch === true) {
            return true;
        }
        $links = Array();
        $link_tags = $doc->getElementsByTagName('a');
        
        foreach ($link_tags as $tag) {
            if (($href_value = $tag->getAttribute('href'))) {
                
                $link_absolute = $this->relativeToAbsolute($href_value, $this->target);
                $link_parsed = parse_url($link_absolute);
                if ($link_parsed === null || $link_parsed === false) {
                    die('Unable to Parse Link URL');
                }
                if ((! array_key_exists('host', $link_parsed) || $link_parsed['host'] == "" || $link_parsed['host'] == $url_host) && array_key_exists('path', $link_parsed) && $link_parsed['path'] != "" && array_search($link_parsed['path'], $links) === false) {
                    $links[] = $this->urlClear($link_parsed['path']);
                } elseif ($link_absolute != $this->referer) {
                    //echo $link_absolute . '  -  ';
                    $links[] = $this->urlClear($link_absolute);
                }
            }
        }
       
        $link_tags = $doc->getElementsByTagName('img');
        foreach ($link_tags as $tag) {
            if (($href_value = $tag->getAttribute('src'))) {
                if (strpos($href_value, 'data:image') !== false) {
                    continue;
                }
                
                if (substr($href_value, 0, 3) === 'app') { //  angular JS (smarty-aki app)
                    $links[] = $this->urlClear($href_value);
                } else {
                
                    $link_absolute = $this->relativeToAbsolute($href_value, $this->target);
    
                    $link_parsed = parse_url($link_absolute);
                    if ($link_parsed === null || $link_parsed === false) {
                        die('Unable to Parse Link URL');
                    }
                    if ((! array_key_exists('host', $link_parsed) || $link_parsed['host'] == "" || $link_parsed['host'] == $url_host) && array_key_exists('path', $link_parsed) && $link_parsed['path'] != "" && array_search($link_parsed['path'], $links) === false) {
                        $links[] = $this->urlClear($link_parsed['path']);
                    }
                }
            }
        }
        
        $link_tags = $doc->getElementsByTagName('iframe');
        foreach ($link_tags as $tag) {
            if (($href_value = $tag->getAttribute('src'))) {
                $links[] = ltrim($this->urlClear($href_value), '//');
                
            }
        }
        
        //print_r($links);
        
        foreach ($links as $key => $string){
            $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
            preg_match_all($pattern, $string, $matches);
            if (isset($matches[0]) && $matches[0]) {
                unset($links[$key]);
            }
        }
        
        
        // Insert Links
        $MySql->referer = $this->referer;
        $MySql->links = $links;
        $MySql->saveLinks();
        return true;
    }
    
    public function urlClear($str) {
         return rtrim(str_replace(' ', '%20', htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")), '#');
    }
    

    public function relativeToAbsolute($relative, $base)
    {
        if ($relative == "" || $base == "")
            return "";
        
        // Some sites have a non-standard url for youtube content. This is what brings this part down.
        if ( strpos($relative, '/www.youtube.com/embed' )) {
            $str = ltrim($relative, '//');
            return 'https://' . $str;
        }
        // Check Base
        $base_parsed = parse_url($base);
        if (! array_key_exists('scheme', $base_parsed) || ! array_key_exists('host', $base_parsed) || ! array_key_exists('path', $base_parsed)) {
            echo "Base Path \"$base\" Not Absolute Link\n";
            return "";
        }
        // Parse Relative
        $relative_parsed = parse_url($relative);
        // If relative URL already has a scheme, it's already absolute
        if (array_key_exists('scheme', $relative_parsed) && $relative_parsed['scheme'] != '') {
            return $relative;
        }
        
        if (substr($relative, 0, 4) === 'www.') {
            return 'http://' . $relative;
        }
        
        // If only a query or a fragment, return base (without any fragment or query) + relative
        if (! array_key_exists('scheme', $relative_parsed) && ! array_key_exists('host', $relative_parsed) && ! array_key_exists('path', $relative_parsed)) {
            return $base_parsed['scheme'] . '://' . $base_parsed['host'] . $base_parsed['path'] . $relative;
        }

        // Remove non-directory portion from path
        $path = preg_replace('#/[^/]*$#', '', $base_parsed['path']);
        // If relative path already points to root, remove base return absolute path
       
        if ($relative[0] == '/') {
            $path = '';
        } elseif (strpos($relative_parsed['path'], $base_parsed['host']) !== false) {
            $tmp = str_replace($base_parsed['host'], '', $relative_parsed['path']);
            $relative = $tmp;
        }
        // Working Absolute URL
        $abs = '';
        // If user in URL
        if (array_key_exists('user', $base_parsed)) {
            $abs .= $base_parsed['user'];
            // If password in URL as well
            if (array_key_exists('pass', $base_parsed)) {
                $abs .= ':' . $base_parsed['pass'];
            }
            // Append location prefix
            $abs .= '@';
        }
        // Append Host
        $abs .= $base_parsed['host'];

        // If port in URL
        if (array_key_exists('port', $base_parsed)) {
            $abs .= ':' . $base_parsed['port'];
        }
        // Append New Relative Path
        $abs .= $path . '/' . $relative;
        // Replace any '//' or '/./' or '/foo/../' with '/'
        $regex = array(
            '#(/\.?/)#',
            '#/(?!\.\.)[^/]+/\.\./#'
        );
        for ($n = 1; $n > 0; $abs = preg_replace($regex, '/', $abs, - 1, $n)) {}
        // Return Absolute URL
        $abs = str_replace('/' . $base_parsed['host'], '', $abs);
        
        return $base_parsed['scheme'] . '://' . $abs;

    }
}
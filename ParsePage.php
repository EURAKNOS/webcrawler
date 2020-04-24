<?php

class ParsePage
{

    public $target;

    // Url to download
    public $referer;

    public $path;

    public function __construct()
    {}

    /**
     * Processing the content of a website
     *
     * @return boolean
     */
    public function parsePage()
    {
        // Create mysql
        $log = new WLog();
        $log->m_log('Targer URL start process: ' . $this->target);
        $MySql = new DbMysql();
        $MySql->target = $this->target;
        
        // Parse URL and get Components
        $url_components = parse_url($this->target);
        if ($url_components === false) {
            die('Unable to Parse URL');
        }
        $url_host = $url_components['host'];
        $url_path = '';
        if (array_key_exists('path', $url_components) == false) {
            // If not a valid path, mark as done
            $MySql->startDownload();
            return false;
        } else {
            $url_path = $url_components['path'];
        }
        // Download Page
        
        echo "Downloading: $this->target\n<br>";

        $dwl = new DownloadPage();
        $dwl->target = $this->target;

        $dwl->referer = $this->referer;
        $contents = $dwl->downloadData();
        //echo "Done\n";
        // Check Status
        if (isset($contents['ok'])) {
            $MySql->path = $this->path;
            $MySql->endDownload();
            return;
        } elseif ($contents['headers']['status_info'][1] != 200) {
            // If not ok, mark as downloaded but skip
            $MySql->path = $this->path;
            $MySql->statusSave();
            return false;
        }

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
                    $this->result[$item['name']] = array(
                        'data' => $nodes->item(0)->nodeValue,
                        'title' => $item['title']
                    );
                }
            }
        }
        $MySql->path = $url_path;
        // save page content
        if ($contents['headers']['status_info'][1] == 200) {
            $MySqlPage = new DbMysql();
            $MySqlPage->data['page'] = $this->referer;
            $MySqlPage->data['path'] = $this->path;
            $MySqlPage->data['content'] = '';
            if (isset($this->result)) {
                $MySqlPage->data['content'] = serialize($this->result);
            }
            $MySqlPage->savePage();
        }
        $log->m_log('Page parser end: ' . $this->target);
        $MySql->endDownload();
        // Get Links
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
                    $links[] = htmlspecialchars($link_parsed['path'], ENT_NOQUOTES, "UTF-8");
                }
            }
        }
        
        $link_tags = $doc->getElementsByTagName('img');
        foreach ($link_tags as $tag) {
            if (($href_value = $tag->getAttribute('src'))) {
                if (strpos($href_value, 'data:image') !== false) {
                    continue;
                }
                $link_absolute = $this->relativeToAbsolute($href_value, $this->target);
                $link_parsed = parse_url($link_absolute);
                if ($link_parsed === null || $link_parsed === false) {
                    die('Unable to Parse Link URL');
                }
                if ((! array_key_exists('host', $link_parsed) || $link_parsed['host'] == "" || $link_parsed['host'] == $url_host) && array_key_exists('path', $link_parsed) && $link_parsed['path'] != "" && array_search($link_parsed['path'], $links) === false) {
                    $links[] = htmlspecialchars($link_parsed['path'], ENT_NOQUOTES, "UTF-8");
                }
            }
        }
        
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

    public function relativeToAbsolute($relative, $base)
    {
        if ($relative == "" || $base == "")
            return "";
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
        // If only a query or a fragment, return base (without any fragment or query) + relative
        if (! array_key_exists('scheme', $relative_parsed) && ! array_key_exists('host', $relative_parsed) && ! array_key_exists('path', $relative_parsed)) {
            return $base_parsed['scheme'] . '://' . $base_parsed['host'] . $base_parsed['path'] . $relative;
        }
        // Remove non-directory portion from path
        $path = preg_replace('#/[^/]*$#', '', $base_parsed['path']);
        // If relative path already points to root, remove base return absolute path
        if ($relative[0] == '/') {
            $path = '';
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
        return $base_parsed['scheme'] . '://' . $abs;
    }
}
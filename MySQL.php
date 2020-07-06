<?php
/**
 * MySQL save data class
 * @author szabo
 *
 */
class DbMysql {
    /**
     * Target URL
     * @var string
     */
    public $target; // Url to download
    /**
     * The route you came from
     * @var string
     */
    public $url_path; 
    /**
     *  Url links collected for storage
     * @var array
     */
    public $links;
    /**
     * Data compiled for Select
     * @var array
     */
    public $data;
    /**
     * insterted ID
     * @var int
     */
    public $id;
    /**
     * Log modul
     * @var object
     */
    public $log;
    /**
     * Result data
     * @var array
     */
    public $resutl;
    /**
     * Url ID
     * @var int
     */
    public $urlId;
    
    /**
     * 
     * @var array
     */
    public $resultUrls;
    
    /**
     * Builds a connection to the mysql database
     */
    public function __construct()
    {    
        
        $this->log = new WLog();
        try {
            $this->db= new PDO("mysql:host=".DB_SERVER_HOST."; charset=utf8; dbname=".DB_SERVER_DATABASE."", DB_USER_NAME, DB_SERVER_PASSWORD);
            // set the PDO error mode to exception
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->exec("set names utf8");
            return $this->db;
        }
        catch(PDOException $e)
        {
            echo "Connecting error: " . $e->getMessage();
        }
    }
    
    /**
     * Stores a status
     */
    public function startDownload()
    {
         $data['path'] = $this->target;
         $data['download_time'] = time();
         $data['url_id'] = $this->urlId;
         $statement = $this->db->prepare("INSERT INTO ".PAGE_TABLE." (url_id, path, download_time) VALUES(:url_id, :path, :download_time) ON DUPLICATE KEY UPDATE download_time = NOW()");
         if(!$statement->execute($data)){
             $this->log->m_log('startDownload MySql function error');
             throw new Exception("An operation failed startDownload function");
         }
         $this->log->m_log('startDownload MySql function success');
    }
    
    /**
     * Stores a status
     */
    public function statusSave()
    {
        $data['path'] = $this->path;
        $data['download_time'] = time();
        $data['url_id'] = $this->urlId;
        $statement = $this->db->prepare("UPDATE ".PAGE_TABLE." SET download_time = :download_time WHERE path = :path AND url_id = :url_id");
        
        if(!$statement->execute($data)){
            $this->log->m_log('statusSave MySql function error');
            throw new Exception("An operation failed endDownload function");
        }
        $this->log->m_log('statusSave MySql function success');
    }
    
    /**
     * Stores a status
     */
    public function statusSaveResearch()
    {
        $data['path'] = $this->path;
        $data['url_id'] = $this->urlId;
        $data['download_time'] = time();
        $statement = $this->db->prepare("UPDATE ".PAGE_TABLE." SET download_time = :download_time, success = 1 WHERE path = :path AND url_id = :url_id");
        
        if(!$statement->execute($data)){
            $this->log->m_log('statusSave MySql function error');
            throw new Exception("An operation failed endDownload function");
        }
        $this->log->m_log('statusSave MySql function success');
    }
    
    /**
     * Stores the links on the page in a database.
     * These are called later by the system and handled depending on the type.
     */
    public function saveLinks()
    {
        foreach($this->links as $link) {
            $data = array();
            $data['path'] = $link;
            $data['url_id'] = $this->urlId;
            $statementSelect = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE path = :path AND url_id = :url_id");
            if(!$statementSelect->execute($data)){
                $this->log->m_log('saveLinks first MySql function error');
            }
                
            $rowCount = $statementSelect->rowCount();
            
            if ( $rowCount == 0 ) {
                $data['referer'] = $this->referer;
                $data['url_id'] = $this->urlId;
                $statement = $this->db->prepare("INSERT INTO ".PAGE_TABLE." (url_id, path, referer, download_time) VALUES(:url_id, :path, :referer, NULL)");
                if(!$statement->execute($data)){
                    $this->log->m_log('saveLinks MySql function error');
                    throw new Exception("An operation failed saveLinks function");
                }
            }
        }
        $this->log->m_log('saveLinks MySql function success');
        
    }
    
    /**
     * Stores a status
     */
    public function endDownload()
    {
        $data['path'] = $this->path;
        $data['download_time'] = time();
        $statement = $this->db->prepare("UPDATE ".PAGE_TABLE." SET download_time = :download_time, success = 1 WHERE path = :path");

        if(!$statement->execute($data)){
            $this->log->m_log('endDownload MySql function error');
            throw new Exception("An operation failed endDownload function");
        }
        $this->log->m_log('endDownload MySql function success: "UPDATE pages SET download_time = ' . $data['download_time'] . ' WHERE path = '.$data['path'].'"');
        
    }
    
    public function savePage()
    {
        $this->data['download_time'] = time();
        $this->data['url_id'] = $this->urlId;
        
        $statement = $this->db->prepare("INSERT INTO ".CONTENTS_TABLE." (`id`, `url_id`, `pages_id`, `page`, `path`, `content`, `download_time`) VALUES (NULL, :url_id, :pages_id, :page, :path, :content, :download_time)");
        if(!$statement->execute($this->data)){
            $this->log->m_log('savePage MySql function error');
            throw new Exception("An operation failed saveLinks function");
        }
        $this->id = $this->db->lastInsertId();
        $this->log->m_log('savePage MySql function success');
    }
    
    public function getLinks()
    {       
        $statement = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE path != '' AND download_time IS NULL ");
        $statement->execute();
        $rowCount = $statement->rowCount();
        if ( $rowCount > 0 ) {
            return $rowCount;
        }
        return false;
        
    }
    
    public function getLinksUnSuccess()
    {
        $data['url_id'] = $this->urlId;
        $statement = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE path != '' AND success = 0 AND url_id = :url_id");
        $statement->execute($data);
        $rowCount = $statement->rowCount();
        if ( $rowCount > 0 ) {
            return $rowCount;
        }
        return false;
        
    }
    
    public function getLinkRow()
    {
        $statement = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE path != '' AND url_id = :url_id AND download_time IS NULL ");
        $statement->execute(array('url_id' => $this->urlId));
       
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
       
    }
    
    public function getLinkRowUnSuccess()
    {
        $data['url_id'] = $this->urlId;
        $statement = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE path != '' AND url_id = :url_id AND success = 0");
        $statement->execute(array('url_id' => $this->urlId));
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
        
    }
    
    /**
     * Downloded file save
     * ID
     * Path: Where it was downloaded from
     * Local location: Where it was saved
     * 
     */    
    public function startDownloadFile()
    {
        $this->log->m_log('startDownloadFile MySql function start');
        $data['path'] = $this->target;
        $data['url_id'] = $this->urlId;
        $data['pages_id'] = $this->pagesId;
        $statement = $this->db->prepare("INSERT INTO ".FILES_TABLE." (id, url_id, pages_id, path) VALUES(NULL, :url_id, :pages_id, :path)");
        
        if(!$statement->execute($data)){
            $this->log->m_log('startDownloadFile MySql function error');
            throw new Exception("An operation failed saveLinks function");
        }
        $this->id = $this->db->lastInsertId();
        $this->log->m_log('startDownloadFile MySql function success');
    }
    
    public function endDownloadFile()
    {
        $this->data['download_time'] = time();
        $statement = $this->db->prepare("UPDATE ".FILES_TABLE." SET local_location = :local_location, file_type = :file_type, downloaded_time = :download_time, meta_data = :meta_data WHERE id = :id");
        if(!$statement->execute($this->data)){
            $this->log->m_log('endDownloadFile MySql function error');
            throw new Exception("An operation failed endDownloadFile function");
        }
        return true;
    }
    
    public function getDownlodedPages()
    {
        $statementSelect = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE 1 ");
        if(!$statementSelect->execute()){
            $this->log->m_log('Get downloded pages error');
        }
        
        $this->result = $statementSelect->fetchAll();
    }
    
    public function getAllDownlodedFiles()
    {
        $statementSelect = $this->db->prepare("SELECT * FROM " . FILES_TABLE . " WHERE downloaded_time IS NOT NULL ");
        if(!$statementSelect->execute()){
            $this->log->m_log('getAllDownlodedFiles MySql function error');
        }
        $this->result = $statementSelect->fetchAll();
    }
    
    public function getUrls()
    {
        $statementSelect = $this->db->prepare("SELECT * FROM " . URLS_TABLE . " WHERE deleted = 0 AND download_time IS NOT NULL");
        if(!$statementSelect->execute()){
            $this->log->m_log('getAllDownlodedFiles MySql function error');
        }
        $this->resultUrl = $statementSelect->fetchAll();
    }
    
    public function countFileElement($id)
    {
        $this->result = array();
        $data['id'] = $id; 
        $statementSelect = $this->db->prepare("SELECT COUNT(path) AS cp FROM " . CONTENTS_TABLE . " WHERE url_id = :id AND download_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['page'] = $statementSelect->fetch(PDO::FETCH_OBJ)->cp;
        
        $this->allCount = $this->result['page'];
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS cid, file_type FROM " . FILES_TABLE . " WHERE url_id = :id AND downloaded_time IS NOT NULL GROUP BY file_type");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $tmp = $statementSelect->fetchAll();
        
        foreach ($tmp as $item) {
            $this->result[$item['file_type']] = $item['cid'];
            $this->allCount += $item['cid'];
        }
    }
    
    public function percentage($id)
    {
        $this->result2 = array();
        $data['id'] = $id;
        $statementSelect = $this->db->prepare("SELECT COUNT(path) AS cp FROM " . CONTENTS_TABLE . " WHERE url_id = :id AND content != '' AND download_time > 0");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['page'] = $statementSelect->fetch(PDO::FETCH_OBJ)->cp;
        
        $this->allCount2 = $this->result2['page'];
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS cid, file_type FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND downloaded_time IS NOT NULL GROUP BY file_type");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $tmp = $statementSelect->fetchAll();
        
        foreach ($tmp as $item) {
            $this->result2[$item['file_type']] = $item['cid'];
            $this->allCount2 += $item['cid'];
        }
        
        
    }
    
    /**
     * 
     */
    public function saveUrl()
    {
        $this->log->m_log('saveUrl MySql function start');
        $data['url'] = $_POST['url'];
        $data['wname'] = $_POST['wname'];
        $data['download_time'] = time();
        $data['post_data'] = serialize($_POST);
        $statement = $this->db->prepare("INSERT INTO ".URLS_TABLE." (id, url, wname, download_time, post_data) VALUES(NULL, :url, :wname, :download_time, :post_data)");
        
        if(!$statement->execute($data)){
            $this->log->m_log('saveUrl MySql function error');
            throw new Exception("An operation failed saveUrl function");
        }
        $this->urlId = $this->db->lastInsertId();
        $this->log->m_log('saveUrl MySql function success');
    }
    
    /**
     *
     */
    public function endDownloadUrl($id)
    {
        $data['id'] = $id;
        $data['end_time'] = time();
        $statement = $this->db->prepare("UPDATE ".URLS_TABLE." SET download = 1, end_time = :end_time WHERE id = :id");
        if(!$statement->execute($data)){
            $this->log->m_log('endDownloadUrl MySql function error');
            throw new Exception("An operation failed endDownloadFile function");
        }
        return true;
    }
    
    public function exitsUrl($url)
    {
        $data['url'] = $url;
        $statementSelect = $this->db->prepare("SELECT * FROM " . URLS_TABLE . " WHERE url = :url AND deleted = 0");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('exitsUrl pages MySql function error');
        }
        $this->result = $statementSelect->fetch();
    }
    
    public function getCrawlingData($id)
    {
        $data['id'] = $id;
        $statementSelect = $this->db->prepare("SELECT * FROM " . URLS_TABLE . " WHERE id = :id AND deleted = 0");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('exitsUrl pages MySql function error');
        }
        $this->result = $statementSelect->fetch();
    }
    
    public function getAllFilesByUrlId($id)
    {
        $data['url_id'] = $id;
        $statementSelect = $this->db->prepare("SELECT id, file_type FROM " . FILES_TABLE . " WHERE url_id = :url_id ");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('getAllFilesByUrlId MySql function error');
        }
        return $statementSelect->fetchAll();
    }
    
    
    public function deleteWebPageData($id)
    {
        $data['url_id'] = $id;
        
        /*try{
            //We start our transaction.
            $this->db->beginTransaction();*/
        
            $statementSelect = $this->db->prepare("DELETE FROM " . PAGE_TABLE . " WHERE url_id = :url_id");
            if(!$statementSelect->execute($data)){
                $this->log->m_log('deleteWebPageData pages MySql function error');
            }
            
            $statementSelect = $this->db->prepare("DELETE FROM " . CONTENTS_TABLE . " WHERE url_id = :url_id");
            if(!$statementSelect->execute($data)){
                $this->log->m_log('deleteWebPageData contents MySql function error');
            }
            
            $statementSelect = $this->db->prepare("DELETE FROM " . FILES_TABLE . " WHERE url_id = :url_id");
            if(!$statementSelect->execute($data)){
                $this->log->m_log('deleteWebPageData files MySql function error');
            }
            
            
            $statement = $this->db->prepare("UPDATE ".URLS_TABLE." SET deleted = 1 WHERE id = :id");
            if(!$statement->execute(array('id'=>$id))){
                $this->log->m_log('endDownloadFile MySql function error');
                throw new Exception("An operation failed endDownloadFile function");
            }
            return true;
          
    }
    
    /**
     * All page content
     */
    public function getAllContent()
    {
        $this->result = array();
        $statementSelect = $this->db->prepare("SELECT COUNT(path) AS cp FROM " . CONTENTS_TABLE . " WHERE download_time IS NOT NULL");
        if(!$statementSelect->execute()){
            $this->log->m_log('getAllContent pages MySql function error');
        }
        $this->result['content'] = $statementSelect->fetch(PDO::FETCH_OBJ)->cp;
    }
    
    /**
     * All page content
     */
    public function getAllDistinctContent()
    {
        $statementSelect = $this->db->prepare("SELECT COUNT(distinct path) AS cp FROM " . CONTENTS_TABLE . " WHERE download_time IS NOT NULL");
        if(!$statementSelect->execute()){
            $this->log->m_log('getAllContent pages MySql function error');
        }
        $this->result['distinct_content'] = $statementSelect->fetch(PDO::FETCH_OBJ)->cp;
    }
    
    public function getAllContentWithMeta()
    {
        $this->result2 = array();
        $statementSelect = $this->db->prepare("SELECT COUNT(path) AS cp FROM " . CONTENTS_TABLE . " WHERE content != '' AND download_time > 0");
        if(!$statementSelect->execute()){
            $this->log->m_log('getAllContentWithMeta pages MySql function error');
        }
        $this->result2['content'] = $statementSelect->fetch(PDO::FETCH_OBJ)->cp;
    }
    
    public function countByTypeAllPage()
    {
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS cid, file_type FROM " . FILES_TABLE . " WHERE downloaded_time IS NOT NULL GROUP BY file_type");
        if(!$statementSelect->execute()){
            $this->log->m_log('countByTypeAllPage MySql function error');
        }
        $tmp = $statementSelect->fetchAll();
        foreach ($tmp as $item) {
            $this->result[$item['file_type']] = $item['cid'];
        }
    }
    
    public function countByTypeDistinctAllPage()
    {
        $statementSelect = $this->db->prepare("SELECT COUNT(distinct path) AS cid, file_type FROM " . FILES_TABLE . " WHERE downloaded_time IS NOT NULL GROUP BY file_type");
        if(!$statementSelect->execute()){
            $this->log->m_log('countByTypeDistinctAllPage MySql function error');
        }
        $tmp = $statementSelect->fetchAll();
        foreach ($tmp as $item) {
            $this->result['distinct_' . $item['file_type']] = $item['cid'];
        }
    }
    
    public function countByTypeAllPageWithMeta()
    {
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS cid, file_type FROM " . FILES_TABLE . " WHERE meta_data != '' AND downloaded_time IS NOT NULL GROUP BY file_type");
        if(!$statementSelect->execute()){
            $this->log->m_log('countByTypeAllPageWithMeta MySql function error');
        }
        $tmp = $statementSelect->fetchAll();
        
        foreach ($tmp as $item) {
            $this->result2[$item['file_type']] = $item['cid'];
        }
    }
    
    public function getAllUrlWithoutDelete()
    {
        $statementSelect = $this->db->prepare("SELECT * FROM " . URLS_TABLE . " WHERE deleted = 0");
        if(!$statementSelect->execute()){
            $this->log->m_log('getAllUrlWithoutDelete MySql function error');
        }
        $this->resultUrls = $statementSelect->fetchAll();
    }
    
    public function stopStatus()
    {
        $data['id'] = $_POST['data'];
        $data['end_time'] = time();
        $statement = $this->db->prepare("UPDATE ".URLS_TABLE." SET download = 2, stop = 1, end_time = :end_time WHERE id = :id");
        if(!$statement->execute($data)){
            $this->log->m_log('stopStatus MySql function error');
            throw new Exception("An operation failed stopStatus function");
        }
        return true;
    }
    
    public function startStatus($id)
    {
        $data['id'] = $id;
        $statement = $this->db->prepare("UPDATE ".URLS_TABLE." SET download = 0, stop = 0, end_time = 0 WHERE id = :id");
        if(!$statement->execute($data)){
            $this->log->m_log('startStatus MySql function error');
            throw new Exception("An operation failed startStatus function");
        }
        return true;
    }
    
    public function checkStop()
    {
        $data['id'] = $this->urlId;
        $statementSelect = $this->db->prepare("SELECT * FROM " . URLS_TABLE . " WHERE id = :id AND deleted = 0 AND stop = 1");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('checkStop pages MySql function error');
        }
        $result = $statementSelect->fetchAll();
       
        if(!empty($result)) return true; 
        else return false;
            
    }
    
    public function saveSpotifyMeta()
    {
        $this->data['download_time'] = time();
        $statement = $this->db->prepare("UPDATE ".FILES_TABLE." SET downloaded_time = :download_time, meta_data = :meta_data WHERE id = :id");
        if(!$statement->execute($this->data)){
            $this->log->m_log('saveSpotifyMeta MySql function error');
            throw new Exception("An operation failed saveSpotifyMeta function");
        }
        return true;
     }
     
     public function getEmptyMetaSpotifyUrls()
     {
         $statementSelect = $this->db->prepare("SELECT * FROM " . FILES_TABLE . " WHERE file_type = 'spotify' AND meta_data = ''");
         if(!$statementSelect->execute()){
             $this->log->m_log('getAllFilesByUrlId MySql function error');
         }
         return $statementSelect->fetchAll();
     }
     
     public function getAllFilesMetaByUrlId($id)
     {
         $data['url_id'] = $id;
         $statementSelect = $this->db->prepare("SELECT f.*, p.referer FROM " . FILES_TABLE . " AS f RIGHT JOIN " . PAGE_TABLE . " AS p ON f.pages_id = p.id WHERE f.url_id = :url_id ORDER BY f.file_type ");
         if(!$statementSelect->execute($data)){
             $this->log->m_log('getAllFilesMetaByUrlId MySql function error');
         }
         return $statementSelect->fetchAll();
     }
     
     /**
      * All page content
      */
     public function getMetaContent($id)
     {
         $data['url_id'] = $id;
         $this->result = array();
         $statementSelect = $this->db->prepare("SELECT c.*, p.referer FROM " . CONTENTS_TABLE . " AS c RIGHT JOIN " . PAGE_TABLE . " AS p ON c.pages_id = p.id WHERE c.url_id = :url_id ");
         if(!$statementSelect->execute($data)){
             $this->log->m_log('getAllContent pages MySql function error');
         }
         return $statementSelect->fetchAll();
     }
     
     public function getRefererUrl($urlId, $url)
     {
         $data['url_id'] = $urlId;
         $data['path'] = $url;
         $statementSelect = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE url_id = :url_id AND path = :path ");
         if(!$statementSelect->execute($data)){
             $this->log->m_log('GetRefererUrl pages error');
         }
         
         return $statementSelect->fetch();
     }
     
     public function getRefererUrlLike($urlId, $url)
     {
         $data['url_id'] = $urlId;
         $data['path'] = '%' . $url;
         $statementSelect = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE url_id = :url_id AND path LIKE :path ");
         if(!$statementSelect->execute($data)){
             $this->log->m_log('getRefererUrlLike pages error');
         }
         
         return $statementSelect->fetch();
     }
     
     public function updateLastParser($id)
     {
         $data['id'] = $id;
         $data['last_parser'] = time();
         $statement = $this->db->prepare("UPDATE ".URLS_TABLE." SET last_parser = :last_parser WHERE id = :id");
         if(!$statement->execute($data)){
             $this->log->m_log('updateLastParser MySql function error');
             throw new Exception("An operation failed updateLastParser function");
         }
         return true;
     }
     
     public function stuckProcessStop()
     {
         $statement = $this->db->prepare("UPDATE ".URLS_TABLE." SET download = 2 WHERE from_unixtime(`last_parser`) < (NOW() - INTERVAL 1 HOUR) AND download = 0");
         if(!$statement->execute()){
             $this->log->m_log('stuckProcessStop MySql function error');
             throw new Exception("An operation failed stuckProcessStop function");
         }
         return true;
     }
     
     public function getDataMetaTitleFilesByType($type)
     {
         $data['file_type'] = $type;
         $statementSelect = $this->db->prepare("SELECT u.url, u.wname, f.path, f.local_location, f.meta_data FROM " . FILES_TABLE . " AS f RIGHT JOIN " . PAGE_TABLE . " AS p ON f.pages_id = p.id RIGHT JOIN " . URLS_TABLE . " AS u ON p.url_id = u.id WHERE file_type = :file_type AND meta_data IS NOT NULL");
         if(!$statementSelect->execute($data)){
             $this->log->m_log('getDataMetaTitleFilesByType MySql function error');
         }
         return $statementSelect->fetchAll();
     }
}
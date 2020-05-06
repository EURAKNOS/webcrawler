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
        $statement = $this->db->prepare("UPDATE ".PAGE_TABLE." SET download_time = :download_time WHERE path = :path");
        
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
            $statementSelect = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE path = :path ");
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
        $statement = $this->db->prepare("UPDATE ".PAGE_TABLE." SET download_time = :download_time WHERE path = :path");

        if(!$statement->execute($data)){
            $this->log->m_log('endDownload MySql function error');
            throw new Exception("An operation failed endDownload function");
        }
        $this->log->m_log('endDownload MySql function success');
        
    }
    
    public function savePage()
    {
        $this->data['download_time'] = time();
        $this->data['url_id'] = $this->urlId;
        
        $statement = $this->db->prepare("INSERT INTO ".CONTENTS_TABLE." (`id`, `url_id`, `page`, `path`, `content`, `download_time`) VALUES (NULL, :url_id, :page, :path, :content, :download_time)");
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
    
    public function getLinkRow()
    {
        $statement = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE path != '' AND download_time IS NULL ");
        $statement->execute();
       
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
        $statement = $this->db->prepare("INSERT INTO ".FILES_TABLE." (id, url_id, path) VALUES(NULL, :url_id, :path)");
        
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
        $data['id'] = $id; 
        $statementSelect = $this->db->prepare("SELECT COUNT(path) AS cp FROM " . CONTENTS_TABLE . " WHERE url_id = :id AND download_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['page'] = $statementSelect->fetch(PDO::FETCH_OBJ)->cp;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS pdf FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'pdf' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['pdf'] = $statementSelect->fetch(PDO::FETCH_OBJ)->pdf;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS jpg FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'jpg' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['jpg'] = $statementSelect->fetch(PDO::FETCH_OBJ)->jpg;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS png FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'png' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['png'] = $statementSelect->fetch(PDO::FETCH_OBJ)->png;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS docx FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'docx' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['docx'] = $statementSelect->fetch(PDO::FETCH_OBJ)->docx;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS xlsx FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'xlsx' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['xlsx'] = $statementSelect->fetch(PDO::FETCH_OBJ)->xlsx;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS pptx FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'pptx' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['pptx'] = $statementSelect->fetch(PDO::FETCH_OBJ)->pptx;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS youtube_video FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'youtube_video' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['youtube_video'] = $statementSelect->fetch(PDO::FETCH_OBJ)->youtube_video;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS vimeo_video FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'vimeo_video' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['vimeo_video'] = $statementSelect->fetch(PDO::FETCH_OBJ)->vimeo_video;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS google_map FROM " . FILES_TABLE . " WHERE url_id = :id AND file_type = 'google_map' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result['google_map'] = $statementSelect->fetch(PDO::FETCH_OBJ)->google_map;
    }
    
    public function percentage($id)
    {
        $data['id'] = $id; 
        $statementSelect = $this->db->prepare("SELECT COUNT(path) AS cp FROM " . CONTENTS_TABLE . " WHERE url_id = :id AND content != '' AND download_time > 0");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['page'] = $statementSelect->fetch(PDO::FETCH_OBJ)->cp;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS pdf FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'pdf' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['pdf'] = $statementSelect->fetch(PDO::FETCH_OBJ)->pdf;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS jpg FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'jpg' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['jpg'] = $statementSelect->fetch(PDO::FETCH_OBJ)->jpg;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS png FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'png' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['png'] = $statementSelect->fetch(PDO::FETCH_OBJ)->png;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS docx FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'docx' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['docx'] = $statementSelect->fetch(PDO::FETCH_OBJ)->docx;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS xlsx FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'xlsx' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['xlsx'] = $statementSelect->fetch(PDO::FETCH_OBJ)->xlsx;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS pptx FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'pptx' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['pptx'] = $statementSelect->fetch(PDO::FETCH_OBJ)->pptx;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS youtube_video FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'youtube_video' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['youtube_video'] = $statementSelect->fetch(PDO::FETCH_OBJ)->youtube_video;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS vimeo_video FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'vimeo_video' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['vimeo_video'] = $statementSelect->fetch(PDO::FETCH_OBJ)->vimeo_video;
        
        $statementSelect = $this->db->prepare("SELECT COUNT(id) AS google_map FROM " . FILES_TABLE . " WHERE url_id = :id AND meta_data != '' AND file_type = 'google_map' AND downloaded_time IS NOT NULL");
        if(!$statementSelect->execute($data)){
            $this->log->m_log('countFileElement pages MySql function error');
        }
        $this->result2['google_map'] = $statementSelect->fetch(PDO::FETCH_OBJ)->google_map;
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
        $statement = $this->db->prepare("INSERT INTO ".URLS_TABLE." (id, url, wname, download_time) VALUES(NULL, :url, :wname, :download_time)");
        
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
        $statement = $this->db->prepare("UPDATE ".URLS_TABLE." SET download = 1 WHERE id = :id");
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
            
            
      /*  }
        //Our catch block will handle any exceptions that are thrown.
        catch(Exception $e){
            //An exception has occured, which means that one of our database queries
            //failed.
            //Print out the error message.
            echo $e->getMessage();
            $this->log->m_log($e->getMessage());
            //Rollback the transaction.
            $this->db->rollBack();
        }*/
    }
    
}
<?php
/**
 * MySQL save data class
 * @author szabo
 *
 */
class DbMysql {
    
    public $target; // Url to download
    
    public $url_path; // The route you came from
    
    public $links;  // Url links collected for storage
    
    public $selectLinksResult; // Selected url
    
    public $data;  // data
    
    public $id; // insterted ID
    
    public $log;
    
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
         $statement = $this->db->prepare("INSERT INTO ".PAGE_TABLE." (path, download_time) VALUES(:path, :download_time) ON DUPLICATE KEY UPDATE download_time = NOW()");
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
                $statement = $this->db->prepare("INSERT INTO ".PAGE_TABLE." (path, referer, download_time) VALUES(:path, :referer, NULL)");
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
        $statement = $this->db->prepare("INSERT INTO ".CONTENTS_TABLE." (`id`, `page`, `path`, `content`, `download_time`) VALUES (NULL, :page, :path, :content, :download_time)");
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
    public function saveFile()
    {
        $query = 'INSERT INTO ' . FILES_TABLE . ' (url, data, download_time) VALUES (\''. mysqli_real_escape_string( $this->mysql_conn, $this->target ). '\', ' .$this->data . ' NOW())';
        if( !mysqli_query($this->mysql_conn, $query) ) {
            $this->log->m_log('saveFile MySql function error');
            die( "savePage function, Error: Unable to perform Download Time Update Query (http status)\n" );
        }
        $this->log->m_log('saveFile MySql function success');
    }
    
    public function startDownloadFile()
    {
        $this->log->m_log('startDownloadFile MySql function start');
        $data['path'] = $this->target;
        $statement = $this->db->prepare("INSERT INTO ".FILES_TABLE." (id, path) VALUES(NULL, :path)");
        
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
    
}
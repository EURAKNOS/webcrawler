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
    
    /**
     * Builds a connection to the mysql database
     */
    public function __construct()
    {        
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
         
         $statement = $this->db->prepare("INSERT INTO ".PAGE_TABLE." (path, download_time) VALUES(:path, NOW()) ON DUPLICATE KEY UPDATE download_time = NOW()");
         if(!$statement->execute($data)){
             throw new Exception("An operation failed startDownload function");
         }
    }
    
    /**
     * Stores a status
     */
    public function statusSave()
    {
        $data['path'] = $this->path;
        
        $statement = $this->db->prepare("UPDATE ".PAGE_TABLE." SET download_time = NOW() WHERE path = :path");
        
        if(!$statement->execute($data)){
            throw new Exception("An operation failed endDownload function");
        }
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
             
            }
                
            $rowCount = $statementSelect->rowCount();
            
            if ( $rowCount == 0 ) {
                $data['referer'] = $this->referer;
                $statement = $this->db->prepare("INSERT INTO ".PAGE_TABLE." (path, referer, download_time) VALUES(:path, :referer, NULL)");
                if(!$statement->execute($data)){
                    throw new Exception("An operation failed saveLinks function");
                }
            }
        }
        
    }
    
    /**
     * Stores a status
     */
    public function endDownload()
    {
        $data['path'] = $this->path;
        
        $statement = $this->db->prepare("UPDATE ".PAGE_TABLE." SET download_time = NOW() WHERE path = :path");

        if(!$statement->execute($data)){
            throw new Exception("An operation failed endDownload function");
        }
        
    }
    
    public function savePage()
    {
        $statement = $this->db->prepare("INSERT INTO ".CONTENTS_TABLE." (`id`, `page`, `path`, `content`, `download_time`) VALUES (NULL, :page, :path, :content, NOW())");
        if(!$statement->execute($this->data)){
            throw new Exception("An operation failed saveLinks function");
        }
        $this->id = $this->db->lastInsertId();
    }
    
    public function getLinks()
    {       
        $statement = $this->db->prepare("SELECT * FROM " . PAGE_TABLE . " WHERE download_time IS NULL ");
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
            die( "savePage function, Error: Unable to perform Download Time Update Query (http status)\n" );
        }
    }
    
    public function startDownloadFile()
    {
        $data['path'] = $this->target;
        $statement = $this->db->prepare("INSERT INTO ".FILES_TABLE." (id, path) VALUES(NULL, :path)");
        if(!$statement->execute($data)){
            throw new Exception("An operation failed saveLinks function");
        }
        $this->id = $this->db->lastInsertId();
    }
    
    public function endDownloadFile()
    {
        $statement = $this->db->prepare("UPDATE ".FILES_TABLE." SET local_location = :local_location, file_type = :file_type, downloaded_time = NOW(), meta_data = :meta_data WHERE id = :id");
        if(!$statement->execute($this->data)){
            throw new Exception("An operation failed endDownloadFile function");
        }
        return true;
    }
    
}
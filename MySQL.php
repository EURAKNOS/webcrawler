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
    
    /**
     * Builds a connection to the mysql database
     */
    public function __construct()
    {
        $this->mysql_conn = mysqli_connect( MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE );
        if ( !$this->mysql_conn ) {
            echo "__construct function, Error: Unable to connect to MySQL." . PHP_EOL;
            echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
            echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
            exit;
        }
    }
    
    /**
     * Stores a status
     */
    public function startDownload()
    {
        $query = "INSERT INTO " . PAGE_TABLE . " (path, download_time) VALUES (\"". mysqli_real_escape_string( $this->mysql_conn, $this->target ). "\", NOW()) ON DUPLICATE KEY UPDATE download_time=NOW()";
         if( !mysqli_query($this->mysql_conn, $query) ) {
            die( "startDownload function, Error: Unable to perform Download Time Update Query (path)\n" );
         }
    }
    
    /**
     * Stores a status
     */
    public function statusSave()
    {
        $query = "INSERT INTO " . PAGE_TABLE . " (path, download_time) VALUES (\"". mysqli_real_escape_string( $this->mysql_conn, $this->url_path ). "\", NOW()) ON DUPLICATE KEY UPDATE download_time=NOW()";
        if( !mysqli_query($this->mysql_conn, $query) ) {
            die( "StatusSave function, Error: Unable to perform Download Time Update Query (http status)\n" );
        }
    }
    
    /**
     * Stores the links on the page in a database.
     * These are called later by the system and handled depending on the type.
     */
    public function saveLinks()
    {
        foreach($this->links as $link) {
            $link_escaped = mysqli_real_escape_string( $this->mysql_conn, $link );
            $query = "INSERT IGNORE INTO " . PAGE_TABLE . " (path, referer, download_time) VALUES (\"$link_escaped\", \"". mysqli_real_escape_string( $this->mysql_conn, $this->target ). "\", NULL)";
            if( !mysqli_query($this->mysql_conn, $query) ) {
                die( "saveLinks function, Error: Unable to perform Insert Link Value Query\n" );
            }
            
        }
        
    }
    
    /**
     * Stores a status
     */
    public function endDownload()
    {
        $query = "INSERT INTO pages (path, download_time) VALUES (\"". mysqli_real_escape_string( $this->mysql_conn, $this->url_path ). "\", NOW()) ON DUPLICATE KEY UPDATE path=\"". mysqli_real_escape_string( $this->mysql_conn, $this->url_path ). "\", download_time=NOW()";
        echo $query;
        if( !mysqli_query($this->mysql_conn, $query) ) {
            die( "endDownload function, Error: Unable to perform Download Time Update Query (http status)\n" );
        }
        
    }
    
    public function savePage()
    {
        $query = 'INSERT INTO page1 (url, data, download_time) VALUES (\''. mysqli_real_escape_string( $this->mysql_conn, $this->target ). '\', ' .$this->data . ' NOW())';
        if( !mysqli_query($this->mysql_conn, $query) ) {
            die( "savePage function, Error: Unable to perform Download Time Update Query (http status)\n" );
        }
    }
    
    public function getLinks()
    {
        $query = "SELECT * FROM " . PAGE_TABLE . " WHERE download_time IS NULL";        
        if (($this->selectLinksResult = mysqli_query($this->mysql_conn, $query)) !== false) {
            if (($rowCount = mysqli_num_rows($this->selectLinksResult)) > 0) {
                return $rowCount;       
            }
            return false;
        }
        return false;
    }
    
    public function getLinkRow()
    {
        if (($row = mysqli_fetch_assoc($this->selectLinksResult)) !== false) {
            return $row;
        }
        return false;
    }
    
    
}
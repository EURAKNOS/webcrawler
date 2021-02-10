<?php
session_start ();
session_write_close();
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'Detail.php';
set_time_limit (10000);

/**
 * Functions of buttons that send Ajax requests
 * STOP AND DELETE button
 *
 */
class ButtonsAjax {
    
    
    public function __construct()
    {
        $this->MySql = new DbMysql();
    }
    
    /**
     * Stops the download. It stores the required status in a database and creates a restart button in return.
     */
    public function stopCrawler()
    {
        $this->stopStatusDb();
    }
    
    private function stopStatusDb()
    {
        if ($this->MySql->stopStatus()) {
            echo json_encode(array('status' => 1, 'button' => '<span class="delete-button btn btn-sm btn-danger" data-id="'.$_POST['data'].'">Delete</span>'));
        }
        
    }
    
    /**
     * Deletes the received download data.
     */
    public function deleteCrawler()
    {
        $this->deleteFiles();
        $this->deleteStatusDb();
    }
    
    private function deleteFiles()
    {
        $result = $this->MySql->getAllFilesByUrlId($_POST['data']);
        
        foreach ($result as $value) {
            $dir = FOLDER_DEFAULT . DIRECTORY_SEPARATOR . $value['file_type'] . DIRECTORY_SEPARATOR . $value['id'];
            if (!is_dir($dir)) continue;
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);;
        }
        
    }
    
    /**
     * Sets the status value in the database to deleted
     */
    private function deleteStatusDb()
    {
        if ($this->MySql->deleteWebPageData($_POST['data'])) {
            echo json_encode(array('status' => 1));
        }
        
    }
    
}

$ajaxProcess = new ButtonsAjax();
if (isset($_POST['processFunction']) && $_POST['processFunction'] == 'stop') {
    $ajaxProcess->stopCrawler();
} elseif (isset($_POST['processFunction']) && $_POST['processFunction'] == 'delete') {
    $ajaxProcess->deleteCrawler();
}
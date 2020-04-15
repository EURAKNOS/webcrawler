<?php
require_once 'vendor/pdfinfo.php';
/**
 * FILE META DATA ANAYLZER
 * Retrieve file information. 
 * This is where all file types are analyzed.
 * @author szabo
 *
 */
class Filedata {
    
    /**
     * File to process
     * The location of the file must be specified here
     * @var string
     */
    public $fileLocation;  
    
    public $result;
    
    public function __construct()
    {
        
    }
    
    /**
     * Retrieve PDF meta data
     */
    public function PdfReviewing()
    {
        $p = new PDFInfo;
        $p->load($this->fileLocation);
        $this->result = $p->result;
    }
    
}
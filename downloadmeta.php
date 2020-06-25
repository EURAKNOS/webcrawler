<?php
if (isset($_GET['file']) && $_GET['file'] != '') {
    $fxls = $_GET['file'];
    if(!file_exists($fxls)){ // file does not exist
        die('file not found');
    } else {
        header('Content-disposition: attachment; filename='.$fxls);
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Length: ' . filesize($fxls));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        readfile($fxls);
    }
}
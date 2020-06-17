<?php

class WLog {
//define function name
    function m_log($arMsg)
    {
        //define empty string
        $stEntry="";
        //get the event occur date time,when it will happened
        $arLogData['event_datetime']='['.date('D Y-m-d h:i:s A', strtotime('+2 hours')).'] [client '.$_SERVER['REMOTE_ADDR'].']';
        //if message is array type
        if(is_array($arMsg))
        {
            //concatenate msg with datetime
            foreach($arMsg as $msg)
                $stEntry.=$arLogData['event_datetime']." ".$msg."\r\n";
        }
        else
        {   //concatenate msg with datetime
            
            $stEntry.=$arLogData['event_datetime']." ".$arMsg."\r\n";
        }
        //create file with current date name
        $stCurLogFileName='crawlerlog/log_'.date('Ymd').'.txt';
        //open the file append mode,dats the log file will create day wise
        $fHandler=fopen($stCurLogFileName,'a+');
        //write the info into the file
        fwrite($fHandler,$stEntry);
        //close handler
        fclose($fHandler);
    } 
    
    function contentLog($filename, $content)
    {
        if (!file_exists('contentlog')) {
            mkdir('contentlog', 0777, true);
        }
        //create file with current date name
        
        $stCurLogFileName = 'contentlog/' . time() . '.txt';
        //open the file append mode,dats the log file will create day wise
        $fHandler=fopen($stCurLogFileName,'a+');
        //write the info into the file
        $tmp = $filename . "\r\n";
        $tmp .= $content;
        fwrite($fHandler,$tmp);
        //close handler
        fclose($fHandler);
    }
}
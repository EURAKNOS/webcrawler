<?php
define('DB_SERVER_HOST', 'localhost');
define('DB_USER_NAME', 'webc');
define('DB_SERVER_PASSWORD', 'test');
define('DB_SERVER_DATABASE', 'webc');
define('PAGE_TABLE', 'pages');
define('FILES_TABLE', 'files');
define('CONTENTS_TABLE', 'contents');
define('ROOT_PATH', $_SERVER['REQUEST_URI']);

// Downloads FILE Folders
define('FOLDER_DEFAULT', 'download');
define('FOLDER_PDF', 'pdf');
define('FOLDER_JPG', 'jpg');
define('FOLDER_PNG', 'png');
define('FOLDER_DOCX', 'docx');
define('FOLDER_PPTX', 'pptx');
define('FOLDER_XLSX', 'xlsx');

set_time_limit(5000);

define('YOUTUBE_API_KEY', 'AIzaSyBTQ6j6WWNPJ2uLifGuYrJELsRFjDDQWIo');
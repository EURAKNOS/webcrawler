<?php
define('VERSION', '0.6.6');

define('DB_SERVER_HOST', 'localhost');
define('DB_USER_NAME', 'webc');
define('DB_SERVER_PASSWORD', 'test');
define('DB_SERVER_DATABASE', 'webc');
define('PAGE_TABLE', 'pages');
define('FILES_TABLE', 'files');
define('URLS_TABLE', 'urls');
define('CONTENTS_TABLE', 'contents');
define('ROOT_PATH', $_SERVER['REQUEST_URI']);

// Downloads FILE Folders
define('FOLDER_DEFAULT', 'download');
define('FOLDER_PDF', 'pdf');
define('FOLDER_JPG', 'jpg');
define('FOLDER_BMP', 'bmp');
define('FOLDER_PNG', 'png');
define('FOLDER_DOCX', 'docx');
define('FOLDER_PPTX', 'pptx');
define('FOLDER_XLSX', 'xlsx');
define('FOLDER_EPUB', 'epub');
define('FOLDER_SWF', 'swf');
define('FOLDER_ZIP', 'zip');
define('FOLDER_SVG', 'svg');
define('FOLDER_PPT', 'ppt');

define('FOLDER_META_EXPORT', 'meta');

set_time_limit(10000);

define('YOUTUBE_API_KEY', 'AIzaSyBTQ6j6WWNPJ2uLifGuYrJELsRFjDDQWIo');

define('VIMEO_API_KEY', '637ecf1ffbaedf398ecff5aaa31a27c4f780204c');
define('VIMEO_API_SECRET', 'MGdf88z3eDABtapf2uBgfG8gpK6tLsnIeGhAUkspXmiaPRg1g5oRyhCccdOWD1D6vcq8s629kDfXCbUjfEcQoaHLJh8KVWHGUrdWpx4NEUrzhpVwAP3oa1BtYHAqg9MA');
define('VIMEO_API_TOKEN', '8a984559b52476a22658d9a8aaada3e3');

define('SPOTIFY_CLIENT_ID', 'a86a99cc3c8f41e688a413e19c2ddf63');
define('SPOTIFY_CLIENT_SECRET', '870f5ca159b84d85a126b102f453b450');
define('SPOTIFY_REDIRECT_URI', 'http://onpremgw1.bioeast.eu/spotify.php');

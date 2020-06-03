<?php
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'WebCrawler.php';
require_once 'ParsePage.php';
require_once 'DownloadPage.php';
require_once 'MySQL.php';
require_once 'Log.php';
require_once 'vendor/spotify/vendor/autoload.php';
set_time_limit(500000);


$session = new SpotifyWebAPI\Session(
    SPOTIFY_CLIENT_ID,
    SPOTIFY_CLIENT_SECRET,
    SPOTIFY_REDIRECT_URI
    );

$api = new SpotifyWebAPI\SpotifyWebAPI();


$re = '/^(https:\/\/open.spotify.com\/episode\/)([a-zA-Z0-9]+)(.*)$/m';

$MySql = new DbMysql();
$urls = $MySql->getEmptyMetaSpotifyUrls(); 

if (isset($_GET['code'])) {
    $session->requestAccessToken($_GET['code']);
    $api->setAccessToken($session->getAccessToken());
    foreach ($urls as $value) {
        $MySql->data = array();
        preg_match_all($re, $value['path'], $matches, PREG_SET_ORDER, 0);
        $show = $api->getEpisode($matches[0][2]);
        
        $saveData['meta_data'] = serialize($show);
        $saveData['id'] = $value['id'];
        $MySql->data = $saveData;
        $MySql->saveSpotifyMeta();
    }

} else {
    $options = [
        'scope' => [
            'user-read-email',
        ],
    ];
    
    header('Location: ' . $session->getAuthorizeUrl($options));
    die();
}




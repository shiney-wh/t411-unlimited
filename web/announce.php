<?php


if (isset($_GET['event'])) {
    $_GET['event'] = 'complet';
}

if (isset($_GET['downloaded'])) {
    $_GET['downloaded'] = '0';
}

$uri = 'http://t411.download/2001df6ff7520eb3d26743ab7896667b/announce?';

print file_get_contents($uri . http_build_query($_GET));

<?php
$dir = __DIR__ . '/public/fonts';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

function downloadFile($url, $dest) {
    if (!file_exists($dest)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);
        file_put_contents($dest, $data);
    }
}

downloadFile('https://raw.githubusercontent.com/google/fonts/main/ofl/sarabun/Sarabun-Regular.ttf', $dir . '/Sarabun-Regular.ttf');
downloadFile('https://raw.githubusercontent.com/google/fonts/main/ofl/sarabun/Sarabun-Bold.ttf', $dir . '/Sarabun-Bold.ttf');

echo "Fonts downloaded successfully.\n";

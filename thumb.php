<?php
 include ('FFMpegThumbnailer/FFMpegThumbnailer.php');

/*
Title:      Thumb.php
URL:        http://github.com/jamiebicknell/Thumb
Author:     Jamie Bicknell
Twitter:    @jamiebicknell
*/

define('THUMB_CACHE',           './cache/');    // Path to cache directory (must be writeable)
define('THUMB_CACHE_AGE',       86400);         // Duration of cached files in seconds
define('THUMB_BROWSER_CACHE',   true);          // Browser cache true or false

$src = isset($_GET['src']) ? $_GET['src'] : false;
$size = isset($_GET['size']) ? str_replace(array('<', 'x'), '', $_GET['size']) != '' ? $_GET['size'] : 100 : 100;
$seek = isset($_GET['seek'])? $_GET['seek']:false;
$path = parse_url($src);

if (isset($path['scheme'])) {
    $base = parse_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    if (preg_replace('/^www\./i', '', $base['host']) == preg_replace('/^www\./i', '', $path['host'])) {
        $base = explode('/', preg_replace('/\/+/', '/', $base['path']));
        $path = explode('/', preg_replace('/\/+/', '/', $path['path']));
        $temp = $path;
        $part = count($base);
        foreach ($base as $k => $v) {
            if ($v == $path[$k]) {
                array_shift($temp);
            } else {
                if ($part - $k > 1) {
                    $temp = array_pad($temp, 0 - (count($temp) + ($part - $k) - 1), '..');
                    break;
                } else {
                    $temp[0] = './' . $temp[0];
                }
            }
        }
        $src = implode('/', $temp);
    }
}
if (!is_writable(THUMB_CACHE)) {
    die('Cache not writable');
}
if (isset($path['scheme']) || !file_exists($src)) {
    die('File cannot be found');
}

date_default_timezone_set('UTC');
$file_salt = 'v1.0.4';
$file_size = filesize($src);
$file_time = filemtime($src);
$file_date = gmdate('D, d M Y H:i:s T', $file_time);
$file_type = 'jpeg';
$file_hash = md5($file_salt . ($src.$size.$seek) . $file_time);
$file_temp = THUMB_CACHE . $file_hash . '.img.txt';
$file_name = basename(substr($src, 0, strrpos($src, '.')) . strtolower(strrchr($src, '.')));

if (!file_exists(THUMB_CACHE . 'index.html')) {
    touch(THUMB_CACHE . 'index.html');
}
if (($fp = fopen(THUMB_CACHE . 'index.html', 'r')) !== false) {
    if (flock($fp, LOCK_EX)) {
        if (time() - THUMB_CACHE_AGE > filemtime(THUMB_CACHE . 'index.html')) {
            $files = glob(THUMB_CACHE . '*.img.txt');
            if (is_array($files) && count($files) > 0) {
                foreach ($files as $file) {
                    if (time() - THUMB_CACHE_AGE > filemtime($file)) {
                        unlink($file);
                    }
                }
            }
            touch(THUMB_CACHE . 'index.html');
        }
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

if (THUMB_BROWSER_CACHE && (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH']))) {
    if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $file_date && $_SERVER['HTTP_IF_NONE_MATCH'] == $file_hash) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
        die();
    }
}

if (!file_exists($file_temp)) {
  $fft = new FFMpegThumbnailer\FFMpegThumbnailer( $src );
  $fft->setOutput( $file_temp )
  ->setOutputSize( intval($size) )
  ->setImageFormat( $file_type );
  if($seek)
    $fft->setSeekTime($seek);
  $fft->run();
}

header('Content-Type: image/' . $file_type);
header('Content-Length: ' . filesize($file_temp));
header('Content-Disposition: inline; filename="' . $file_name . '"');
header('Last-Modified: ' . $file_date);
header('ETag: ' . $file_hash);
header('Accept-Ranges: none');
if (THUMB_BROWSER_CACHE) {
    header('Cache-Control: max-age=604800, must-revalidate');
    header('Expires: ' . gmdate('D, d M Y H:i:s T', strtotime('+7 days')));
} else {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Expires: ' . gmdate('D, d M Y H:i:s T'));
    header('Pragma: no-cache');
}

readfile($file_temp);

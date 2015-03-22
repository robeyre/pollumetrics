<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

// width and height of the image
$width = 256;
$height = 256;

// how much blurring to do: higher number, more blur
$blur = 3;

// use cache
$cache = true;

// starting height and width for rendering
if ($blur) {
    $currentWidth = $width / \pow(2, $blur);
    $currentHeight = $width / \pow(2, $blur);
} else {
    $currentWidth = $width;
    $currentHeight = $width;
}

if (!isset($_GET['minLat']) || !isset($_GET['minLong']) || !isset($_GET['maxLat']) || !isset($_GET['maxLong'])) {
    // lat and long of the central point
    $centreLat = 51.5277435;
    $centreLong = -0.1284699;

    // lat and long bounding box
    $minLat = $centreLat - 0.1;
    $maxLat = $centreLat + 0.1;
    $minLong = $centreLong - 0.1;
    $maxLong = $centreLong + 0.1;
} else {
    $minLat = (float) $_GET['minLat'];
    $maxLat = (float) $_GET['maxLat'];
    $minLong = (float) $_GET['minLong'];
    $maxLong = (float) $_GET['maxLong'];

    if ($minLat > $maxLat) {
        list($minLat, $maxLat) = array($maxLat, $minLat);
    }
    if ($minLong > $maxLong) {
        list($minLong, $maxLong) = array($maxLong, $minLong);
    }
}

$cacheKey = \md5(\implode(':', array($minLat, $minLong, $maxLat, $maxLong)));
$cacheFile = __DIR__ . '/map/cache/' . $cacheKey . '.png';
if (!$cache || !\is_file($cacheFile) || (\filemtime($cacheFile) < (time() - 60*60))) {

    $latInterval = ($maxLat - $minLat) / $currentHeight;
    $longInterval = ($maxLong - $minLong) / $currentWidth;

    // load point data
    $points = array();
    $fh = \fopen(\dirname(__DIR__) . '/bin/current_site_levels.csv', 'rb');
    $header = \fgetcsv($fh);
    while ($row = \fgetcsv($fh)) {
        $row = \array_combine($header, $row);
        $row['Longitude'] = (float) $row['Longitude'];
        $row['Latitude'] = (float) $row['Latitude'];
        $k = \round($row['Latitude'], 4) . '/' . \round($row['Longitude'], 4);
        $points[$k] = $row;
    }
    \fclose($fh);
    /*
    $fh = \fopen(\dirname(__DIR__) . '/bin/forecast_site_levels.csv', 'rb');
    $header = \fgetcsv($fh);
    while ($row = \fgetcsv($fh)) {
        $row = \array_combine($header, $row);
        $row['Longitude'] = (float) $row['Longitude'];
        $row['Latitude'] = (float) $row['Latitude'];
        $k = \round($row['Latitude'], 4) . '/' . \round($row['Longitude'], 4);
        if (\array_key_exists($k, $points)) {
            $points[$k]['Forecast'] = $row['Index'];
        } else {
            $row['Forecast'] = $row['Index'];
            unset($row['Index']);
            $points[$k] = $row;
        }
    }
    \fclose($fh);
     */

    $img = \imagecreatetruecolor($currentWidth, $currentHeight);
    \imagealphablending($img, false);
    \imagesavealpha($img, true);

    for ($y = 0; $y < $currentHeight; $y++) {
        for ($x = 0; $x < $currentWidth; $x++) {
            $lat = $minLat + ($currentHeight - $y) * $latInterval;
            $long = $minLong + $x * $longInterval;

            $dist = array();
            foreach ($points as $p) {
                if (\array_key_exists('Index', $p)) {
                    $z = \sqrt(\pow($p['Longitude'] - $long, 2) + \pow($p['Latitude'] - $lat, 2));
                    $dist[\sprintf('%.05f', $z)] = $p;
                }
            }
            \ksort($dist);

            $p = \reset($dist);

            $rgb = array(
                'r' => 255,
                'g' => 255,
                'b' => 255,
            );
            switch ((int) $p['Index']) {
            case 1:
                $rgb = array(
                    'r' => \hexdec('9c'),
                    'g' => \hexdec('ff'),
                    'b' => \hexdec('9c'),
                );
                break;
            case 2:
                $rgb = array(
                    'r' => \hexdec('31'),
                    'g' => \hexdec('ff'),
                    'b' => \hexdec('00'),
                );
                break;
            case 3:
                $rgb = array(
                    'r' => \hexdec('31'),
                    'g' => \hexdec('cf'),
                    'b' => \hexdec('00'),
                );
                break;
            case 4:
                $rgb = array(
                    'r' => \hexdec('ff'),
                    'g' => \hexdec('ff'),
                    'b' => \hexdec('00'),
                );
                break;
            case 5:
                $rgb = array(
                    'r' => \hexdec('ff'),
                    'g' => \hexdec('cf'),
                    'b' => \hexdec('00'),
                );
                break;
            case 6:
                $rgb = array(
                    'r' => \hexdec('ff'),
                    'g' => \hexdec('9a'),
                    'b' => \hexdec('00'),
                );
                break;
            case 7:
                $rgb = array(
                    'r' => \hexdec('ff'),
                    'g' => \hexdec('64'),
                    'b' => \hexdec('64'),
                );
                break;
            case 8:
                $rgb = array(
                    'r' => \hexdec('ff'),
                    'g' => \hexdec('00'),
                    'b' => \hexdec('00'),
                );
                break;
            case 9:
                $rgb = array(
                    'r' => \hexdec('99'),
                    'g' => \hexdec('00'),
                    'b' => \hexdec('00'),
                );
                break;
            case 10:
                $rgb = array(
                    'r' => \hexdec('ce'),
                    'g' => \hexdec('30'),
                    'b' => \hexdec('ff'),
                );
                break;
            }

            $colour = \imagecolorallocatealpha($img, $rgb['r'], $rgb['g'], $rgb['b'], (int) 96 * (10 - $p['Index']) / 10);
            \imagesetpixel($img, $x, $y, $colour);
        }
    }

    // blur the image
    \imagefilter($img, \IMG_FILTER_GAUSSIAN_BLUR);

    // grow the image
    if ($blur) {
        for ($b = $blur; $b; $b--) {
            $nextWidth = $currentWidth * 2;
            $nextHeight = $currentHeight * 2;

            $nextImage = \imagecreatetruecolor($nextWidth, $nextHeight);
            \imagealphablending($nextImage, false);
            \imagesavealpha($nextImage, true);

            \imagecopyresized($nextImage, $img, 0, 0, 0, 0, $nextWidth, $nextHeight, $currentWidth, $currentHeight);
            \imagedestroy($img);

            $img = $nextImage;
            $currentWidth = $nextWidth;
            $currentHeight = $nextHeight;

            // blur the image
            \imagefilter($img, \IMG_FILTER_GAUSSIAN_BLUR);
        }
    }

    \imagepng($img, $cacheFile);
    \imagedestroy($img);
}

\header('Content-Type: image/png');
\header('Content-Length: ' . \filesize($cacheFile));
\readfile($cacheFile);


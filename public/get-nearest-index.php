<?php

if (!isset($_GET['lat']) || !isset($_GET['long'])) {
    $lat = 51.5277435;
    $long = -0.1284699;
} else {
    $lat = $_GET['lat'];
    $long = $_GET['long'];
}
$result = array();

$nearest = null;
$nearestRow = null;

$fh = \fopen(\dirname(__DIR__) . '/bin/current_site_levels.csv', 'rb');
$header = \fgetcsv($fh);
while ($row = \fgetcsv($fh)) {
    $row = \array_combine($header, $row);
    $dist = \sqrt(\pow($row['Latitude'] - $lat, 2) + \pow($row['Longitude'] - $long, 2));
    if (($nearest === null) || ($dist < $nearest)) {
        $nearest = $dist;
        $nearestRow = $row;
    }
}
\fclose($fh);

$result['current'] = $nearestRow['Index'];
$result['Site'] = $nearestRow['Site'];

$nearest = null;
$nearestRow = null;
$fh = \fopen(\dirname(__DIR__) . '/bin/forecast_site_levels.csv', 'rb');
$header = \fgetcsv($fh);
while ($row = \fgetcsv($fh)) {
    $row = \array_combine($header, $row);
    $dist = \sqrt(\pow($row['Latitude'] - $lat, 2) + \pow($row['Longitude'] - $long, 2));
    if (($nearest === null) || ($dist < $nearest)) {
        $nearest = $dist;
        $nearestRow = $row;
    }
}
\fclose($fh);

$result['forecast'] = $nearestRow['Index'];

$doc = @DOMDocument::loadHTML(
    \file_get_contents('http://uk-air.defra.gov.uk/latest/currentlevels')
);
function getIndex($str)
{
    if (\preg_match('/\(([0-9]+)/', $str, $match)) {
        return (int) $match[1];
    }
}
$xpath = new DOMXPath($doc);
$trs = $xpath->query('//tr[td/a[normalize-space(.) = "' . $result['Site'] . '"]]');
if ($trs->length) {
    $tr = $trs->item(0);
    $result['Ozone'] = getIndex((string) $xpath->evaluate('normalize-space(td[2])', $tr));
    $result['Nitrogen dioxide'] = getIndex((string) $xpath->evaluate('normalize-space(td[3])', $tr));
    $result['Sulphur dioxide'] = getIndex((string) $xpath->evaluate('normalize-space(td[4])', $tr));
    $result['PM2.5 Particles'] = getIndex((string) $xpath->evaluate('normalize-space(td[5])', $tr));
    $result['PM10 Particles'] = getIndex((string) $xpath->evaluate('normalize-space(td[6])', $tr));
}

\header('Content-Type: application/json');

$jsonStr = \json_encode($result);
if (!empty($_REQUEST['jsonp'])) {
    $jsonStr = $_REQUEST['jsonp'] . '(' . $jsonStr . ');';
}
\header('Content-Length: ' . \strlen($jsonStr));
echo $jsonStr;


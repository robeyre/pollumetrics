<?php

$lat = 54.4;
$long = 0;
$result = array();

$nearest = null;
$nearestRow = null;

$fh = \fopen(__DIR__ . '/current_site_levels.csv', 'rb');
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

$nearest = null;
$nearestRow = null;
$fh = \fopen(__DIR__ . '/forecast_site_levels.csv', 'rb');
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

print_r($result);

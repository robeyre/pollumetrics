<?php

function getLevels ($lat, $long) {
    static $currentSiteLevels;
    static $forecastSiteLevels;

    $result = array();

    $nearest = null;
    $nearestRow = null;

    if (empty($currentSiteLevels)) {
        $fh = \fopen(__DIR__ . '/current_site_levels.csv', 'rb');
        $header = \fgetcsv($fh);
        while ($row = \fgetcsv($fh)) {
            $row = \array_combine($header, $row);
            $currentSiteLevels[] = $row;
        }
        \fclose($fh);
    }
    foreach ($currentSiteLevels as $row) {
        $dist = \sqrt(\pow($row['Latitude'] - $lat, 2) + \pow($row['Longitude'] - $long, 2));
        if (($nearest === null) || ($dist < $nearest)) {
            $nearest = $dist;
            $nearestRow = $row;
        }
    }

    $result['current'] = $nearestRow['Index'];

    $nearest = null;
    $nearestRow = null;

    if (empty($forecastSiteLevels)) {
        $fh = \fopen(__DIR__ . '/forecast_site_levels.csv', 'rb');
        $header = \fgetcsv($fh);
        while ($row = \fgetcsv($fh)) {
            $row = \array_combine($header, $row);
            $forecastSiteLevels[] = $row;
        }
        \fclose($fh);
    }
    foreach ($forecastSiteLevels as $row) {
        $dist = \sqrt(\pow($row['Latitude'] - $lat, 2) + \pow($row['Longitude'] - $long, 2));
        if (($nearest === null) || ($dist < $nearest)) {
            $nearest = $dist;
            $nearestRow = $row;
        }
    }

    $result['forecast'] = $nearestRow['Index'];

    return $result;
}


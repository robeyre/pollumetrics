<?php
require __DIR__ . '/getLevels.php';

$min = array(
    'latitude' => 49.7,
    'longitude' => -8.2,
);
$max = array(
    'latitude' => 60.8,
    'longitude' => 1.7,
);
$intervals = array(
    'latitude' => 0.1,
    'longitude' => 0.1,
);

$fh = \fopen(__DIR__ . '/grid.csv', 'wb');
\fputcsv($fh, array('Latitude', 'Longitude', 'Current index', 'Forecast index'));
for ($lat = $min['latitude']; $lat <= $max['latitude']; $lat += $intervals['latitude']) {
    for ($long = $min['longitude']; $long <= $max['longitude']; $long += $intervals['longitude']) {
        $levels = getLevels($lat, $long);
        $row = array(
            'latitude' => $lat,
            'longitude' => $long,
            'current' => $levels['current'],
            'forecast' => $levels['forecast'],
        );
        \fputcsv($fh, $row);
    }
}
\fclose($fh);


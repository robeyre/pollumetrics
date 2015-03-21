<?php

$xml = \file_get_contents('http://uk-air.defra.gov.uk/assets/rss/forecast.xml');
$doc = \simplexml_load_string($xml);
$fh = \fopen(__DIR__ . '/forecast_site_levels.csv', 'wb');
\fputcsv($fh, array('Site', 'Latitude', 'Longitude', 'Index', 'Time'));
foreach ($doc->channel->item as $item) {
    $title = \trim($item->title);
    $description = \html_entity_decode((string) $item->description, ENT_COMPAT, 'UTF-8');
    $time = new \DateTime((string) $item->pubDate);
    $match = null;
    print_r($description);
    if (\preg_match('/Location: ([-0-9]+)°([0-9]+)´([.0-9]+)"N.*([-0-9]+)°([0-9]+)´([.0-9]+)"W.*index levels are forecast to be.*?([A-Za-z]+): ([0-9]+).*?([A-Za-z]+): ([0-9]+)/', $description, $match)) {
        $lat = $match[1] + $match[2]/60 + $match[3]/3600;
        $long = -1 * ($match[4] + $match[5]/60 + $match[6]/3600);
        $index = $match[10];
        \fputcsv($fh, array(
            'site' => $title,
            'latitude' => $lat,
            'longitude' => $long,
            'index' => $index,
            'time' => $time->format('Y-m-d H:i:s'),
        ));
    }
}
\fclose($fh);



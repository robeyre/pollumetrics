<?php

$xml = \file_get_contents('http://uk-air.defra.gov.uk/assets/rss/current_site_levels.xml');
$doc = \simplexml_load_string($xml);
$fh = \fopen(__DIR__ . '/current_site_levels.csv', 'wb');
\fputcsv($fh, array('Site', 'Latitude', 'Longitude', 'Index', 'Time'));
foreach ($doc->channel->item as $item) {
    $title = \trim($item->title);
    $description = \html_entity_decode((string) $item->description, ENT_COMPAT, 'UTF-8');
    $time = new \DateTime((string) $item->pubDate);
    $match = null;
    if (\preg_match('/Location: ([-0-9]+)°([0-9]+)´([.0-9]+)"N.*([-0-9]+)°([0-9]+)´([.0-9]+)"W.*at index ([0-9]+)/', $description, $match)) {
        $lat = $match[1] + $match[2]/60 + $match[3]/3600;
        $long = $match[4] + $match[5]/60 + $match[6]/3600;
        $index = $match[7];
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


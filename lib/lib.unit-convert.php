<?php
/*
 * lib.unit-convert.php by Bruno Adele (bruno.adele@jesuislibre.org) (2010) in GPL Licence
 *
*/
// Problem with global variable ?
function get_unitSpeed () {
    return array('KT' => 1.852, 'MPS' => 3.6 , 'KMH' => 1, 'MPH' => 1.609344); //convert to km/h
}

function get_unitLength() {
    return array('Mile' => 1609.344, 'Meter' => 1, 'Kilometer' => 1000); //convert to meter
}

function get_unitWindSpeed() {
    return array ('MPH' => 1, 'KMH' => 0.62137119);
}

function get_unitWindConvert() {
    return 'KMH';
}

/*
 * Convert a unit length
*/
function convertLength($part, $unit) {
    $unit_length = get_unitLength();

    $length = round($part * $unit_length[$unit]);

    return $length;
}

function convertTempToF ($tempC) {
    return round(1.8 * $tempC + 32);
}

function convertTempToC($tempF) {
    return round(($tempF - 32)/1.8);
}

?>

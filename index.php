<?php

use Converter\Converter;

require_once 'vendor/autoload.php';

$csvFile = 'files/csv-example.csv';
$jsonFile = 'files/json-example.json';


$conversor = new Converter();
$conversor->fromCSVToJSON($csvFile);
// $conversor->fromJSONToCSV($jsonFile);

<?php

use Converter\Converter;
use Converter\FileType;

require_once 'vendor/autoload.php';

$csvFile = 'example/csv-example.csv';
$jsonFile = 'example/json-example.json';


$conversor = new Converter();
// $conversor->fromCSVToJSON($csvFile);
// $conversor->fromJSONToCSV($jsonFile);

$conversor->toSQL('users', $csvFile, FileType::CSV);
// $conversor->toSQL('users', $jsonFile, FileType::JSON);

<?php

namespace Converter;

class Converter
{
    private function openFile(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \Exception('O arquivo não existe ou não é acessível');
        }

        $fd = fopen($path, 'r');

        if ($fd === false) {
            throw new \Exception('Não foi possível abrir o arquivo');
        }

        return $fd;
    }


    private function canReadFile(string $path)
    {
        return file_exists($path) && is_readable($path);
    }

    private function readCSV($file, string $delimiter): iterable
    {
        $header = fgetcsv(stream: $file, separator:$delimiter);

        while (!feof($file)) {
            $line = fgetcsv(stream: $file, separator: $delimiter);
            yield array_combine($header, $line);
        }

        fclose($file);
    }

    public function fromCSVToJSON(string $path, string $outputFile = 'output.json', string $delimiter = ',')
    {
        $file = $this->openFile($path);

        $outputFD = fopen($outputFile, 'w');

        if ($outputFD === false) {
            throw new \Exception('Não foi possível abrir o arquivo');
        }

        fwrite($outputFD, "[\n");

        foreach ($this->readCSV($file, $delimiter) as $line) {
            $outputLine = json_encode($line, JSON_PRETTY_PRINT) . ",\n";
            fwrite($outputFD, $outputLine);
        }

        //Remove last comma
        fseek($outputFD, -2, SEEK_CUR);
        fwrite($outputFD, "\n]");
        fclose($outputFD);
    }

    public function fromJSONToCSV(string $path, string $outputFile = 'output.csv', string $delimiter = ',')
    {
        $json = $this->readJSON($path);
        $outputFD = fopen($outputFile, 'w+');

        $headers = array_keys($json[0]);
        fputcsv($outputFD, $headers);

        foreach ($json as $item) {
            fputcsv($outputFD, $item, $delimiter);
        }

        fclose($outputFD);
    }


    private function readJSON(string $path): iterable
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \Exception('O arquivo não existe ou não é acessível');
        }

        $jsonContent = file_get_contents($path);

        $decoded = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('O json informado esta inválido.');
        }

        return $decoded;
    }
}

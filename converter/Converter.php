<?php

namespace Converter;

class Converter
{
    private function openFileToRead(string $path)
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

    private function readCSV($file, string $delimiter): iterable
    {
        $header = fgetcsv(stream: $file, separator:$delimiter);

        while (!feof($file)) {
            $line = fgetcsv(stream: $file, separator: $delimiter);
            yield array_combine($header, $line);
        }

        fclose($file);
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

    public function fromCSVToJSON(string $path, string $outputFile = 'output.json', string $delimiter = ',')
    {
        $file = $this->openFileToRead($path);

        $outputFD = fopen($outputFile, 'w');

        if ($outputFD === false) {
            throw new \Exception('Não foi possível abrir o arquivo para gravação');
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
        $outputFD = fopen($outputFile, 'w');

        if ($outputFD === false) {
            throw new \Exception('Não foi possível abrir o arquivo para gravação');
        }

        $headers = array_keys($json[0]);
        fputcsv($outputFD, $headers);

        foreach ($json as $item) {
            fputcsv($outputFD, $item, $delimiter);
        }

        fclose($outputFD);
    }

    public function toSQL(string $table, string $path, FileType $type, string $outputFile = 'output.sql', string $delimiter = ',')
    {
        $outputFD = fopen($outputFile, 'w');

        if ($outputFD === false) {
            throw new \Exception('Não foi possível abrir o arquivo para gravação');
        }

        $content = match ($type->value) {
            FileType::CSV->value => $this->readCSV($this->openFileToRead($path), $delimiter),
            FileType::JSON->value => $this->readJSON($path),
            default => throw new \Exception("Tipo {$type->value} não suportado para conversão")
        };

        $template = "INSERT INTO {$table} (:columns) VALUES (:values);" . PHP_EOL;

        foreach ($content as $item) {
            $columns = array_keys($item);
            $values = array_values($item);

            $enquoteValues = array_reduce($values, function ($carry, $value) {
                $formatedValue = null;

                if (is_numeric($value)) {
                    $formatedValue = number_format((float) $value, 2, '.', '');
                } else {
                    $formatedValue = trim("'$value'");
                }

                return $carry . $formatedValue . ',';
            });

            $columns = implode(',', $columns);
            $enquoteValues = mb_substr($enquoteValues, 0, -1);

            $query = str_replace([':columns', ':values'], [$columns, $enquoteValues], $template);

            fwrite($outputFD, $query);
        }

        fclose($outputFD);
    }
}

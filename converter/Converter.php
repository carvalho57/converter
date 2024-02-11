<?php

namespace Converter;

class Converter
{
    private array $fileLines = [];


    public function readCsv(string $filename, string $delimiter = ','): self
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \Exception('O arquivo não existe ou não é acessível');
        }

        $fd = fopen($filename, 'r');

        if ($fd === false) {
            throw new \Exception('Não foi possível abrir o arquivo');
        }

        $header = fgetcsv($fd, 1000, $delimiter);

        while (($line = fgetcsv($fd, 1000, $delimiter)) !== false) {
            $this->fileLines[] = array_combine($header, $line);
        }

        return $this;
    }



    public static function fromCsvToJson(string $filename)
    {
        $converter = new static();
        return $converter->readCsv($filename)->toJson();
    }


    public function toJson(): string
    {
        return json_encode($this->fileLines, JSON_PRETTY_PRINT);
    }
}

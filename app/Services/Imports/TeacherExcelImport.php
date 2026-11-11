<?php

namespace App\Services\Imports;

use Illuminate\Validation\ValidationException;
use ZipArchive;

class TeacherExcelImport
{
    public function csv(string $path)
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) $this->fail('Le fichier Excel est illisible. Téléchargez à nouveau le modèle.');
        $output = fopen('php://temp', 'r+');
        try {
            $strings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach ($this->xml($zip, 'xl/sharedStrings.xml')->si as $item) {
                    $strings[] = $this->text($item);
                }
            }
            $sheet = $this->xml($zip, 'xl/worksheets/sheet1.xml');
            $width = null;
            $count = 0;
            foreach ($sheet->sheetData->row as $row) {
                $values = [];
                foreach ($row->c as $cell) {
                    $reference = (string) $cell['r'];
                    if (! preg_match('/^([A-Z]+)[0-9]+$/', $reference, $match)) $this->fail('Adresse de cellule Excel invalide.');
                    $index = 0;
                    foreach (str_split($match[1]) as $letter) $index = $index * 26 + ord($letter) - 64;
                    if ($index > 100) $this->fail('Le fichier Excel contient trop de colonnes. Utilisez le modèle fourni.');
                    if (isset($cell->f)) $this->fail('Cellule '.$reference.' : remplacez la formule par une valeur saisie.');
                    $type = (string) $cell['t'];
                    $values[$index - 1] = match ($type) {
                        'inlineStr' => $this->text($cell->is),
                        's' => $strings[(int) $cell->v] ?? '',
                        default => (string) $cell->v,
                    };
                }
                if (! array_filter($values, fn ($value) => trim($value) !== '')) continue;
                if (++$count > 501) $this->fail('Le maximum est de 500 enseignants par fichier.');
                $width ??= max(array_keys($values)) + 1;
                $columns = array_fill(0, max($width, max(array_keys($values)) + 1), '');
                foreach ($values as $index => $value) $columns[$index] = $value;
                fputcsv($output, $columns, ';', '"', '');
            }
            if (! $count) $this->fail('Le fichier Excel est vide.');
            rewind($output);
            return $output;
        } catch (\Throwable $error) {
            fclose($output);
            throw $error;
        } finally {
            $zip->close();
        }
    }

    private function xml(ZipArchive $zip, string $part): \SimpleXMLElement
    {
        $stat = $zip->statName($part);
        if (! $stat || $stat['size'] > 8 * 1024 * 1024) $this->fail('Structure Excel invalide ou trop volumineuse. Utilisez le modèle fourni.');
        $contents = $zip->getFromName($part);
        if ($contents === false || stripos($contents, '<!DOCTYPE') !== false) $this->fail('Structure Excel non prise en charge.');
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($contents, \SimpleXMLElement::class, LIBXML_NONET);
            if ($xml === false) $this->fail('Le contenu du fichier Excel est illisible.');
            return $xml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function text(\SimpleXMLElement $element): string
    {
        $parts = $element->xpath('.//*[local-name()="t"]');
        return implode('', array_map(fn ($part) => (string) $part, $parts ?: []));
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['csv' => $message]);
    }
}

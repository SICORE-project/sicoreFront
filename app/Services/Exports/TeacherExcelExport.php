<?php

namespace App\Services\Exports;

use RuntimeException;
use Throwable;
use XMLWriter;
use ZipArchive;

class TeacherExcelExport
{
    public function create(iterable $rows, array $headers = ['Matricule', 'Nom', 'Prénom', 'IA', 'Corps', 'Statut']): string
    {
        $path = tempnam(sys_get_temp_dir(), 'teachers_xlsx_');
        $sheet = tempnam(sys_get_temp_dir(), 'teachers_sheet_');
        $zip = new ZipArchive;
        $opened = false;

        try {
            if ($path === false || $sheet === false || $zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Impossible de créer le fichier Excel.');
            }

            $opened = true;
            $xml = new XMLWriter;
            $xml->openUri($sheet);
            $xml->startDocument('1.0', 'UTF-8');
            $xml->startElement('worksheet');
            $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $xml->writeRaw('<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="'.count($headers).'" width="25" customWidth="1" style="1"/></cols>');
            $xml->startElement('sheetData');
            $this->row($xml, $headers, 1);
            $count = 1;
            foreach ($rows as $row) {
                if (++$count > 1048576) {
                    throw new RuntimeException('La liste dépasse la capacité Excel. Veuillez affiner les filtres.');
                }
                $this->row($xml, $row, $count);
            }
            $xml->endElement();
            $xml->writeRaw('<autoFilter ref="A1:'.$this->column(count($headers) - 1).$count.'"/>');
            $xml->endElement();
            $xml->endDocument();
            $xml->flush();

            $parts = [
                'xl/styles.xml' => '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="49" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
                '[Content_Types].xml' => '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
                '_rels/.rels' => '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
                'xl/workbook.xml' => '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Enseignants" sheetId="1" r:id="rId1"/></sheets></workbook>',
                'xl/_rels/workbook.xml.rels' => '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            ];
            foreach ($parts as $name => $contents) {
                if (! $zip->addFromString($name, $contents)) {
                    throw new RuntimeException('Impossible de créer le fichier Excel.');
                }
            }
            if (! $zip->addFile($sheet, 'xl/worksheets/sheet1.xml') || ! $zip->close()) {
                throw new RuntimeException('Impossible de finaliser le fichier Excel.');
            }

            $opened = false;

            return $path;
        } catch (Throwable $exception) {
            if ($opened) {
                $zip->close();
            }
            if ($path !== false && is_file($path)) {
                unlink($path);
            }
            throw $exception;
        } finally {
            if ($sheet !== false && is_file($sheet)) {
                unlink($sheet);
            }
        }
    }

    private function column(int $index): string
    {
        $name = '';
        do {
            $name = chr(65 + $index % 26).$name;
            $index = intdiv($index, 26) - 1;
        } while ($index >= 0);
        return $name;
    }

    private function row(XMLWriter $xml, array $values, int $number): void
    {
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $number);
        foreach ($values as $index => $value) {
            $xml->startElement('c');
            $xml->writeAttribute('r', $this->column($index).$number);
            // Inline strings preserve leading zeroes and never execute spreadsheet formulas.
            $xml->writeAttribute('t', 'inlineStr');
            $xml->startElement('is');
            $xml->startElement('t');
            $xml->writeAttribute('xml:space', 'preserve');
            $xml->text(preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', (string) $value) ?? '');
            $xml->endElement();
            $xml->endElement();
            $xml->endElement();
        }
        $xml->endElement();
    }
}

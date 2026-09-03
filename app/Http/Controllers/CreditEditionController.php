<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreditEditionController extends Controller
{
    public function previewDelegations(): View
    {
        return $this->preview(
            'credit-edition-delegations',
            'État des délégations de crédits',
            route('credits.edition-delegations')
        );
    }

    public function showDelegation(string $reference): View
    {
        $report = $this->report('credit-edition-delegations');
        $rows = array_values(array_filter(
            $report['rows'],
            fn (array $row): bool => (string) ($row[0] ?? '') === $reference
        ));

        abort_if($rows === [], 404, 'Délégation de crédits introuvable.');

        return view('pages.credits.report', [
            'title' => 'Détail de la délégation '.$reference,
            'subtitle' => 'Situation financière de la délégation sélectionnée',
            'columns' => $this->exportColumns($report),
            'rows' => $this->plainRows($rows),
            'backUrl' => route('credits.edition-delegations'),
        ]);
    }

    public function exportDelegations(): StreamedResponse
    {
        $report = $this->report('credit-edition-delegations');

        return $this->excelResponse(
            'delegations-credits-2026.xls',
            'Délégations',
            $this->exportColumns($report),
            $this->plainRows($report['rows'])
        );
    }

    public function exportEngagementsPdf(?int $row = null): Response
    {
        [$columns, $rows] = $this->engagementRows($row);
        $filename = $row === null ? 'engagements-credits-2026.pdf' : 'engagement-credit-'.($row + 1).'.pdf';

        return response($this->makePdf('État des engagements de crédits', $columns, $rows), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportEngagementsExcel(?int $row = null): StreamedResponse
    {
        [$columns, $rows] = $this->engagementRows($row);
        $filename = $row === null ? 'engagements-credits-2026.xls' : 'engagement-credit-'.($row + 1).'.xls';

        return $this->excelResponse($filename, 'Engagements', $columns, $rows);
    }

    private function preview(string $slug, string $title, string $backUrl): View
    {
        $report = $this->report($slug);

        return view('pages.credits.report', [
            'title' => $title,
            'subtitle' => 'Document prêt à être imprimé ou enregistré en PDF',
            'columns' => $this->exportColumns($report),
            'rows' => $this->plainRows($report['rows']),
            'backUrl' => $backUrl,
        ]);
    }

    /** @return array{0: array<int, string>, 1: array<int, array<int, string>>} */
    private function engagementRows(?int $row): array
    {
        $report = $this->report('credit-edition-engagements');
        $rows = $this->plainRows($report['rows']);

        if ($row !== null) {
            abort_unless(array_key_exists($row, $rows), 404, 'Engagement de crédits introuvable.');
            $rows = [$rows[$row]];
        }

        return [$this->exportColumns($report), $rows];
    }

    /** @return array<string, mixed> */
    private function report(string $slug): array
    {
        $report = config('module-pages.'.$slug);
        abort_unless(is_array($report), 404, 'Édition de crédits introuvable.');

        return $report;
    }

    /** @param array<string, mixed> $report
     * @return array<int, string>
     */
    private function exportColumns(array $report): array
    {
        return array_values(array_slice($report['columns'] ?? [], 0, -1));
    }

    /** @param array<int, array<int, mixed>> $rows
     * @return array<int, array<int, string>>
     */
    private function plainRows(array $rows): array
    {
        return array_map(
            fn (array $row): array => array_map(
                fn (mixed $cell): string => trim(html_entity_decode(strip_tags((string) $cell), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                array_slice($row, 0, -1)
            ),
            array_values($rows)
        );
    }

    /** @param array<int, string> $columns
     * @param  array<int, array<int, string>>  $rows
     */
    private function excelResponse(string $filename, string $sheet, array $columns, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($sheet, $columns, $rows): void {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
            echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
            echo '<Styles><Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#DFF3E7" ss:Pattern="Solid"/></Style></Styles>';
            echo '<Worksheet ss:Name="'.$this->xml($sheet).'"><Table>';
            $this->excelRow($columns, 'Header');
            foreach ($rows as $row) {
                $this->excelRow($row);
            }
            echo '</Table></Worksheet></Workbook>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /** @param array<int, string> $cells */
    private function excelRow(array $cells, ?string $style = null): void
    {
        echo '<Row>';
        foreach ($cells as $cell) {
            $styleAttribute = $style ? ' ss:StyleID="'.$style.'"' : '';
            echo '<Cell'.$styleAttribute.'><Data ss:Type="String">'.$this->xml($cell).'</Data></Cell>';
        }
        echo '</Row>';
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** @param array<int, string> $columns
     * @param  array<int, array<int, string>>  $rows
     */
    private function makePdf(string $title, array $columns, array $rows): string
    {
        $lines = [$title, 'SICORE - Généré le '.now()->format('d/m/Y à H:i'), ''];
        foreach ($rows as $index => $row) {
            $lines[] = 'Enregistrement '.($index + 1);
            foreach ($columns as $columnIndex => $column) {
                $lines[] = $column.' : '.($row[$columnIndex] ?? '');
            }
            $lines[] = '';
        }

        $pages = array_chunk($lines, 29);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $kids = [];

        foreach ($pages as $pageIndex => $pageLines) {
            $pageObject = 4 + ($pageIndex * 2);
            $contentObject = $pageObject + 1;
            $kids[] = $pageObject.' 0 R';
            $content = $this->pdfPage($pageLines, $pageIndex + 1, count($pages));
            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] ';
            $objects[$pageObject] .= '/Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentObject.' 0 R >>';
            $objects[$contentObject] = '<< /Length '.strlen($content).' >>'."\nstream\n".$content."\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($number = 1; $number <= count($objects); $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= 'startxref'."\n".$xref."\n%%EOF";

        return $pdf;
    }

    /** @param array<int, string> $lines */
    private function pdfPage(array $lines, int $page, int $total): string
    {
        $commands = ['BT', '/F1 16 Tf', '50 790 Td'];
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $commands[] = '0 -23 Td';
            }
            $commands[] = '('.$this->pdfText($line).') Tj';
            if ($index === 0) {
                $commands[] = '/F1 10 Tf';
            }
        }
        $commands[] = 'ET';
        $commands[] = 'BT /F1 9 Tf 500 25 Td (Page '.$page.'/'.$total.') Tj ET';

        return implode("\n", $commands);
    }

    private function pdfText(string $value): string
    {
        $value = mb_convert_encoding($value, 'Windows-1252', 'UTF-8');

        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $value);
    }
}

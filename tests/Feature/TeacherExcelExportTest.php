<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use ZipArchive;

class TeacherExcelExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']]);
    }

    public function test_export_contains_all_filtered_pages_and_preserves_text(): void
    {
        Http::fakeSequence()
            ->push(['data' => [['matricule' => '00123', 'nom' => 'Ndiaye', 'prenom' => 'Éva & Ali', 'ia' => ['libelle' => 'Dakar'], 'corps' => ['libelle' => 'Professeur'], 'statut' => 'actif']], 'meta' => ['last_page' => 2]])
            ->push(['data' => [['matricule' => '00456', 'nom' => '=1+1']], 'meta' => ['last_page' => 2]]);

        $response = $this->get(route('enseignants.export', ['search' => 'Ali', 'ia_id' => 2, 'page' => 9]));
        $response->assertOk()->assertDownload()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($path));
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $this->assertNotFalse(simplexml_load_string($zip->getFromIndex($i)));
            }
            $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
            $this->assertCount(3, $sheet->sheetData->row);
            $this->assertSame('00123', (string) $sheet->sheetData->row[1]->c[0]->is->t);
            $this->assertSame('Éva & Ali', (string) $sheet->sheetData->row[1]->c[2]->is->t);
            $this->assertSame('=1+1', (string) $sheet->sheetData->row[2]->c[1]->is->t);
            $this->assertSame('inlineStr', (string) $sheet->sheetData->row[2]->c[1]['t']);
            $zip->close();
        } finally {
            unlink($path);
        }
        Http::assertSentCount(2);
        foreach ([1, 2] as $page) {
            Http::assertSent(fn ($request) => $request['page'] == $page && $request['search'] === 'Ali' && $request['ia_id'] == 2 && $request['per_page'] == 100);
        }
    }

    public function test_failure_on_a_later_page_does_not_download_a_partial_file(): void
    {
        Http::fakeSequence()->push(['data' => [['nom' => 'Test']], 'meta' => ['last_page' => 2]])
            ->push(['message' => 'Service indisponible'], 500);
        $this->get(route('enseignants.export', ['ia_id' => 2]))
            ->assertRedirect(route('enseignants.index', ['ia_id' => 2]))->assertSessionHas('error', 'Service indisponible');
    }

    public function test_expired_session_redirects_to_login(): void
    {
        Http::fake(['*' => Http::response([], 401)]);
        $this->get(route('enseignants.export'))->assertRedirect(route('login'))->assertSessionMissing('access_token');
    }

    public function test_empty_export_contains_column_headers(): void
    {
        Http::fake(['*' => Http::response(['data' => [], 'meta' => ['last_page' => 1]])]);
        $response = $this->get(route('enseignants.export'))->assertOk()->assertDownload();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $zip = new ZipArchive;
            $zip->open($path);
            $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
            $this->assertCount(1, $sheet->sheetData->row);
            $this->assertSame('Prénom', (string) $sheet->sheetData->row[0]->c[2]->is->t);
            $zip->close();
        } finally {
            unlink($path);
        }
    }
}

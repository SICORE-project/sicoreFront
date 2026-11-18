<?php
namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TeacherImportTest extends TestCase
{
    public function test_excel_upload_preserves_leading_zeroes_in_forwarded_csv(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => [
            'role_slug' => 'admin', 'permissions' => ['enseignants.read', 'enseignants.create'],
        ]]);
        $path = app(\App\Services\Exports\TeacherExcelExport::class)->create([
            ['001234/F', '001234567890123', '00001', '000123456789', '1990-01-31'],
        ], ['matricule', 'cni', 'code_guichet', 'numero_compte', 'date_naissance']);
        try {
            $contents = file_get_contents($path);
            $csv = app(\App\Services\Imports\TeacherExcelImport::class)->csv($path);
            $header = fgetcsv($csv, 0, ';', '"', '');
            $values = fgetcsv($csv, 0, ';', '"', '');
            fclose($csv);
            $this->assertSame(['001234/F', '001234567890123', '00001', '000123456789', '1990-01-31'], $values);
            Http::fake(['*/admin/personnel/enseignants/import' => function ($request) {
                $this->assertStringContainsString('000123456789', $request->body());
                return Http::response(['message' => 'Import terminé.'], 201);
            }]);
            $this->from(route('enseignants.index'))->post(route('enseignants.import.store'), [
                'csv' => \Illuminate\Http\UploadedFile::fake()->createWithContent('enseignants.xlsx', $contents),
            ])->assertRedirect(route('enseignants.index'))->assertSessionHas('success');
            Http::assertSent(fn ($request) => $request->hasFile('csv'));
        } finally {
            unlink($path);
        }
    }
    private function templateHeaders($response): string
    {
        $response->assertDownload('modele-enseignants.xlsx');
        $zip = new \ZipArchive;
        $path = $response->baseResponse->getFile()->getPathname();
        $this->assertTrue($zip->open($path));
        $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $this->assertSame('1', (string) $sheet->cols->col['style']);
        $this->assertSame('1', (string) $sheet->sheetData->row[1]->c[0]['s']);
        $this->assertSame('L2:L501', (string) $sheet->dataValidations->dataValidation[0]['sqref']);
        $this->assertStringContainsString('numFmtId="49"', $zip->getFromName('xl/styles.xml'));
        $headers = [];
        foreach ($sheet->sheetData->row[0]->c as $cell) $headers[] = (string) $cell->is->t;
        $zip->close();
        unlink($path);
        return implode(';', $headers);
    }

    public function test_import_error_has_a_red_alert_and_explicit_message(): void
    {
        $errors = new \Illuminate\Support\ViewErrorBag;
        $errors->put('default', new \Illuminate\Support\MessageBag(['csv' => 'Colonne obligatoire manquante : matricule.']));
        $this->view('pages.enseignants.import-error', ['errors' => $errors])
            ->assertSee('Import impossible')->assertSee('Colonne obligatoire manquante : matricule.')
            ->assertSee('role="alert"', false)->assertSee('color: #b91c1c', false);
    }

    public function test_server_failure_explains_interruption_without_claiming_nothing_was_imported(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => [
            'role_slug' => 'admin', 'permissions' => ['enseignants.read', 'enseignants.create'],
        ]]);
        Http::fake(['*/admin/personnel/enseignants/import' => Http::response(['message' => 'Une erreur interne est survenue.'], 500)]);
        $this->from(route('enseignants.index'))->post(route('enseignants.import.store'), [
            'csv' => UploadedFile::fake()->createWithContent('enseignants.csv', "matricule;nom\n"),
        ])->assertRedirect(route('enseignants.index'))->assertSessionHasErrors('csv');
        $this->assertStringContainsString('Vérifiez la liste', session('errors')->first('csv'));
    }
    public function test_partial_import_report_is_preserved_and_displays_each_line_and_cause(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => [
            'role_slug' => 'admin', 'permissions' => ['enseignants.read', 'enseignants.create'],
        ]]);
        $report = ['message' => '1 enseignant inséré. 1 ligne bloquée.', 'status' => 'created',
            'count' => 1, 'blocked_count' => 1,
            'accepted' => [['line' => 2, 'matricule' => '202600001/A', 'prenom' => 'Awa', 'nom' => 'Diop']],
            'blocked' => [['line' => 3, 'matricule' => '202600002/B', 'prenom' => 'Fatou', 'nom' => 'Fall',
                'errors' => ['cni' => ['La CNI doit contenir entre 13 et 15 chiffres.']]]]];
        Http::fake(['*/admin/personnel/enseignants/import' => Http::response($report, 201)]);
        $this->from(route('enseignants.index'))->post(route('enseignants.import.store'), [
            'csv' => UploadedFile::fake()->createWithContent('enseignants.csv', "matricule;nom\n"),
        ])->assertRedirect(route('enseignants.index'))->assertSessionHas('teacher_import_report', $report);
        $this->view('pages.enseignants.import-report')->assertSee('Ligne 2')->assertSee('Ligne 3')
            ->assertSee('202600001/A')->assertSee('202600002/B')
            ->assertSee('La CNI doit contenir entre 13 et 15 chiffres.');
    }

    public function test_ia_manager_can_download_template_and_forward_csv(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['role_slug' => 'gestionnaire_ia', 'permissions' => ['enseignants.read']]]);
        $response = $this->get(route('enseignants.import.template'))->assertOk();
        $headers = $this->templateHeaders($response);
        $this->assertStringContainsString('statut_enseignant', $headers);
        $this->assertStringContainsString('numero_compte', $headers);
        Http::fake(['*/ia/teachers/import' => Http::response(['message' => '1 enseignant importé.'], 201)]);
        $this->from(route('enseignants.index'))->post(route('enseignants.import.store'), [
            'csv' => UploadedFile::fake()->createWithContent('enseignants.csv', "matricule;nom\n202600001/A;Diop\n"),
        ])->assertRedirect(route('enseignants.index'))->assertSessionHas('success', '1 enseignant importé.');
        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_ends_with($request->url(), '/ia/teachers/import') && $request->hasFile('csv'));
    }

    public function test_teacher_cannot_download_import_template(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['role_slug' => 'enseignant']]);
        $this->get(route('enseignants.import.template'))->assertForbidden();
    }
    public function test_all_import_roles_can_download_complete_template(): void
    {
        foreach (['admin', 'super_admin', 'gestionnaire_ia'] as $role) {
            $this->withSession(['access_token' => 'token', 'sicore_user' => ['role_slug' => $role, 'permissions' => ['enseignants.read', 'enseignants.create']]]);
            $csv = $this->templateHeaders($this->get(route('enseignants.import.template'))->assertOk());
            foreach (['categorie', 'corps', 'indice', 'ief', 'situation_matrimoniale', 'nombre_enfants'] as $field) {
                $this->assertStringContainsString($field, $csv);
            }
            $this->assertCount(16, str_getcsv(trim($csv), ';', '"', ''));
            foreach (['nombre_femmes', 'conjoint_travaille', 'date_recrutement', 'diplome', 'lieu_service', 'date_fin_contrat', 'annee_recrutement', 'cle_rib', 'ia_id', 'ief_id', 'corps_id', 'categorie_id', 'diplome_id', 'lieu_service_id', 'statut_enseignant_id'] as $removed) {
                $this->assertNotContains($removed, str_getcsv(trim($csv), ';', '"', ''));
            }
        }
    }

    public function test_unauthorized_roles_cannot_upload_import(): void
    {
        Http::fake();
        foreach (['enseignant', 'gestionnaire_ief'] as $role) {
            $this->withSession(['access_token' => 'token', 'sicore_user' => ['role_slug' => $role, 'permissions' => ['enseignants.read', 'enseignants.create']]]);
            $this->post(route('enseignants.import.store'), [
                'csv' => UploadedFile::fake()->createWithContent('enseignants.csv', "matricule;nom\n"),
            ])->assertForbidden();
        }
        Http::assertNotSent(fn ($request) => $request->method() === 'POST' && str_contains($request->url(), '/import'));
    }

    public function test_backend_line_error_is_returned_to_import_form(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['role_slug' => 'admin', 'permissions' => ['enseignants.read', 'enseignants.create']]]);
        Http::fake(['*/admin/personnel/enseignants/import' => Http::response([
            'errors' => ['csv' => ['Ligne 3 : Ce matricule existe d?j?.']],
        ], 422)]);
        $this->from(route('enseignants.index'))->post(route('enseignants.import.store'), [
            'csv' => UploadedFile::fake()->createWithContent('enseignants.csv', "matricule;nom\n"),
        ])->assertRedirect(route('enseignants.index'))->assertSessionHasErrors('csv');
    }
}

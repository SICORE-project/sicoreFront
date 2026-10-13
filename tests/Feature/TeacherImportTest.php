<?php
namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TeacherImportTest extends TestCase
{
    public function test_ia_manager_can_download_template_and_forward_csv(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['role_slug' => 'gestionnaire_ia']]);
        $response = $this->get(route('enseignants.import.template'))->assertOk();
        $this->assertStringContainsString('statut_enseignant_id', $response->streamedContent());
        $this->assertStringContainsString('numero_compte', $response->streamedContent());
        Http::fake(['*/admin/personnel/enseignants/import' => Http::response(['message' => '1 enseignant importé.'], 201)]);
        $this->from(route('enseignants.index'))->post(route('enseignants.import.store'), [
            'csv' => UploadedFile::fake()->createWithContent('enseignants.csv', "matricule;nom\n202600001/A;Diop\n"),
        ])->assertRedirect(route('enseignants.index'))->assertSessionHas('success', '1 enseignant importé.');
        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_ends_with($request->url(), '/admin/personnel/enseignants/import') && $request->hasFile('csv'));
    }

    public function test_teacher_cannot_download_import_template(): void
    {
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['role_slug' => 'enseignant']]);
        $this->get(route('enseignants.import.template'))->assertForbidden();
    }
}

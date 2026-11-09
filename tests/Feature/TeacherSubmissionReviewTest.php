<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TeacherSubmissionReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['role' => 'super_admin', 'role_slug' => 'super_admin']]);
    }

    public function test_dage_can_consult_and_validate_pending_teacher(): void
    {
        Http::fake([
            '*/admin/personnel/enseignants/submissions' => Http::response(['data' => ['data' => [
                ['id' => 5, 'matricule' => '123456789/A', 'status' => 'pending',
                    'payload' => ['nom' => 'Diop', 'prenom' => 'Awa', 'ia' => ['libelle' => 'Dakar']]],
            ]]]),
            '*/admin/personnel/enseignants/submissions/5/approve' => Http::response(['message' => 'Enseignant valide.']),
        ]);
        $this->get(route('enseignants.submissions'))->assertOk()->assertSee('Awa')
            ->assertSee('>Valider</button>', false)->assertSee('teacher-profile.js', false);
        $this->from(route('enseignants.submissions'))->post(route('enseignants.submissions.approve', 5))
            ->assertRedirect(route('enseignants.submissions'))->assertSessionHas('success');
        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_ends_with($request->url(), '/submissions/5/approve'));
    }
}

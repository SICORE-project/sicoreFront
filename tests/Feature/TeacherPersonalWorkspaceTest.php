<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TeacherPersonalWorkspaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token'=>'test','sicore_user'=>['id'=>1,'name'=>'Enseignant Test','role'=>'Enseignant','role_slug'=>'enseignant','permissions'=>['*']]]);
        Http::fake([
            '*/enseignant/dossier*'=>Http::response(['data'=>['matricule'=>'T001','prenom'=>'Test','nom'=>'Enseignant','bulletins_disponibles'=>1]]),
            '*/enseignant/bulletins/1/pdf*'=>Http::response('%PDF-1.4 test',200,['Content-Type'=>'application/pdf']),
            '*/enseignant/bulletins/2/pdf*'=>Http::response([],404),
            '*/enseignant/bulletins*'=>Http::response(['data'=>['data'=>[],'current_page'=>1,'last_page'=>1,'per_page'=>10,'total'=>0],'annees'=>[2026],'periodes'=>[]]),
        ]);
    }
    public function test_dashboard_and_sidebar_are_personal(): void
    {
        $this->get('/dashboard')->assertOk()->assertSee('T001')->assertSee('Mes informations')->assertSee('Mes bulletins de salaire')->assertDontSee('Gestion de la paie')->assertDontSee('Paramétrage');
    }
    public function test_management_is_forbidden_even_with_permissions(): void
    {
        $this->get('/utilisateurs')->assertForbidden();
        $this->get('/enseignants')->assertForbidden();
        Http::assertNothingSent();
    }
    public function test_filters_and_private_pdf_proxy(): void
    {
        $this->get('/mon-espace/bulletins?annee=2026')->assertOk()->assertSee('Aucun bulletin');
        Http::assertSent(fn($request)=>str_contains($request->url(),'enseignant/bulletins') && (string)$request['annee']==='2026');
        $this->get('/mon-espace/bulletins/1/pdf?download=1')->assertOk()->assertHeader('Content-Type','application/pdf')->assertHeader('Content-Disposition','attachment; filename="bulletin-1.pdf"');
        $this->get('/mon-espace/bulletins/2/pdf')->assertNotFound();
    }
    public function test_missing_dossier_has_a_clear_message(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*'=>Http::response([],403)]);
        $this->get('/dashboard')->assertOk()->assertSee('Contactez un administrateur');
    }
}
<?php
namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecruitmentInterfaceTest extends TestCase
{
    private function login(array $permissions): static
    {
        Http::preventStrayRequests();
        return $this->withSession(['access_token'=>'test','sicore_user'=>[
            'role_slug'=>'agent_drh','name'=>'Agent DRH','permissions'=>$permissions,
            'drh'=>['perimetre'=>['type'=>'national','id'=>null]],
        ]]);
    }
    public function test_import_screen_has_template_and_hides_teacher_menu(): void
    {
        Http::fake(['*'=>Http::response(['data'=>[]])]);
        $this->login(['recruitment.read','recruitment.import'])->get(route('recruitment.index'))->assertOk()
            ->assertSee('Importer une liste')->assertSee('Aucun lot de recrutement dans votre périmètre.');
        $this->get(route('recruitment.create'))->assertOk()->assertSee('Importer votre fichier existant');
    }
    public function test_lot_displays_inactive_recruits_os_upload_and_history(): void
    {
        Http::fake(['*'=>Http::response(['data'=>[
            'batch'=>['id'=>1,'reference'=>'LOT-1','recruited_at'=>'2020-01-01','transmitted_at'=>null,'has_os'=>false],
            'members'=>[['id'=>1,'matricule'=>'A001','prenom'=>'Awa','nom'=>'Diop','engagement'=>'vacataire','alerted_at'=>null,'est_actif'=>false,'service_date'=>null]],
            'history'=>[],
        ]])]);
        $this->login(['recruitment.read','recruitment.import'])->get(route('recruitment.show',1))->assertOk()
            ->assertSee('Awa Diop')->assertSee('Inactif')
            ->assertDontSee('Valider et activer')->assertDontSee('Valider la décision');
    }
    public function test_import_forwards_file_and_validation_errors(): void
    {
        Http::fake(['*/recruitment/batches'=>Http::response(['message'=>'Erreur','errors'=>['ligne_2'=>['Matricule répété.']]],422)]);
        $this->login(['recruitment.import'])->post(route('recruitment.preview'),[
            'reference'=>'LOT-1','recruited_at'=>'2020-01-01','file'=>UploadedFile::fake()->create('liste.csv',1,'text/csv'),
        ])->assertSessionHasErrors('ligne_2');
        Http::assertSent(fn ($request)=>$request->hasHeader('Authorization','Bearer test'));
    }
}

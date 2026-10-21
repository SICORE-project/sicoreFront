<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TeacherAccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']]);
        Http::preventStrayRequests();
    }

    public function test_user_list_offers_creation_and_resending_for_teacher_accounts(): void
    {
        Http::fake([
            '*/admin/users?*' => Http::response(['data' => [[
                'id' => 4, 'nom' => 'Ndiaye', 'prenom' => 'Awa', 'email' => 'awa@example.test',
                'enseignant_id' => 7, 'statut' => 'actif', 'password_defined' => false, 'is_online' => true,
                'role' => ['nom' => 'Enseignant', 'slug' => 'enseignant'],
            ]]]),
            '*' => Http::response(['data' => []]),
        ]);
        $this->get(route('utilisateurs.index'))->assertOk()
            ->assertSee(route('utilisateurs.teacher.create'))
            ->assertSee('user-online-indicator')
            ->assertSee('En attente de vérification')
            ->assertDontSee('<span class="badge badge-active">Actif</span>', false)
            ->assertSee(route('utilisateurs.teacher.resend', 4));
    }

    public function test_invitation_icon_is_hidden_after_password_is_defined(): void
    {
        Http::fake([
            '*/admin/users?*' => Http::response(['data' => [[
                'id' => 4, 'nom' => 'Ndiaye', 'prenom' => 'Awa', 'email' => 'awa@example.test',
                'enseignant_id' => 7, 'statut' => 'actif', 'password_defined' => true,
                'role' => ['nom' => 'Enseignant', 'slug' => 'enseignant'],
            ]]]),
            '*' => Http::response(['data' => []]),
        ]);
        $this->get(route('utilisateurs.index'))->assertOk()
            ->assertDontSee(route('utilisateurs.teacher.resend', 4))
            ->assertSee('<span class="badge badge-active">Actif</span>', false)
            ->assertSee('data-user-action="view"', false);
    }

    public function test_creation_page_offers_search_and_email_without_password_or_role_input(): void
    {
        $this->get(route('utilisateurs.teacher.create'))->assertOk()
            ->assertSee('Matricule, nom ou prénom')->assertSee('teacher-account.js')
            ->assertDontSee('name="password"', false)->assertDontSee('name="role_id"', false);
        Http::assertNothingSent();
    }

    public function test_candidates_are_searched_and_paginated_through_backend(): void
    {
        Http::fake(['*/admin/users/teacher-candidates*' => Http::response(['data' => [], 'total' => 0, 'current_page' => 2, 'last_page' => 2])]);
        $this->getJson(route('utilisateurs.teacher.candidates', ['search' => 'Awa Ndiaye', 'page' => 2]))
            ->assertOk()->assertJsonPath('current_page', 2);
        Http::assertSent(fn ($request) => $request['search'] === 'Awa Ndiaye' && $request['page'] == 2 && $request->hasHeader('Authorization', 'Bearer token'));
    }

    public function test_success_redirects_to_users_and_does_not_forward_arbitrary_role(): void
    {
        Http::fake(['*/admin/users/teacher-accounts' => Http::response(['email_sent' => true, 'message' => 'Invitation envoyée'], 201)]);
        $this->post(route('utilisateurs.teacher.store'), ['enseignant_id' => 4, 'email' => 'awa@example.test', 'role_id' => 1])
            ->assertRedirect(route('utilisateurs.index'))->assertSessionHas('success', 'Invitation envoyée');
        Http::assertSent(fn ($request) => $request->data() === ['enseignant_id' => 4, 'email' => 'awa@example.test']);
    }

    public function test_mail_failure_is_a_warning_and_does_not_prompt_duplicate_creation(): void
    {
        Http::fake(['*/admin/users/teacher-accounts' => Http::response(['email_sent' => false, 'message' => 'Compte créé, envoi impossible'], 201)]);
        $this->post(route('utilisateurs.teacher.store'), ['enseignant_id' => 4, 'email' => 'awa@example.test'])
            ->assertRedirect(route('utilisateurs.index'))->assertSessionHas('warning', 'Compte créé, envoi impossible');
    }

    public function test_validation_error_preserves_email_and_displays_backend_error(): void
    {
        Http::fake(['*/admin/users/teacher-accounts' => Http::response(['errors' => ['email' => ['Adresse déjà utilisée.']]], 422)]);
        $this->from(route('utilisateurs.teacher.create'))->post(route('utilisateurs.teacher.store'), ['enseignant_id' => 4, 'email' => 'awa@example.test'])
            ->assertRedirect(route('utilisateurs.teacher.create'))->assertSessionHasErrors('email')->assertSessionHasInput('email', 'awa@example.test');
    }

    public function test_resend_uses_existing_account(): void
    {
        Http::fake(['*/admin/users/4/teacher-invitation' => Http::response(['message' => 'Invitation renvoyée'])]);
        $this->from(route('utilisateurs.index'))->post(route('utilisateurs.teacher.resend', 4))
            ->assertRedirect(route('utilisateurs.index'))->assertSessionHas('success', 'Invitation renvoyée');
        Http::assertSentCount(1);
    }
}

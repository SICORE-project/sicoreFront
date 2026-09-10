<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valide le formulaire de connexion avant tout appel au backend.
 *
 * Cette classe contient uniquement les règles du formulaire. La vérification
 * du mot de passe reste exclusivement réalisée par sicoreBack.
 */
class LoginRequest extends FormRequest
{
    /** La page de connexion est publique : aucune session préalable n'est requise. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Définit les données acceptées par POST /login.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /** Retourne des messages simples et compréhensibles dans le formulaire. */
    public function messages(): array
    {
        return [
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.email' => 'L’adresse e-mail saisie n’est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }
}

<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;

class ConvocationService
{
    public function __construct(
        protected ApiClient $api
    ) {}

    /**
     * Enveloppe uniforme autour des reponses Http::response() de l'ApiClient,
     * sur le meme principe que AuthService::login().
     */
    protected function wrap($response): array
    {
        if (! $response->successful()) {
            return [
                'success' => false,
                'status' => $response->status(),
                'message' => $response->json('message') ?? 'Le service SICORE Backend est injoignable.',
                'errors' => $response->json('errors'),
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'status' => $response->status(),
            'message' => $response->json('message'),
            'data' => $response->json('data'),
        ];
    }

    public function liste(array $filtres = []): array
    {
        return $this->wrap($this->api->get('convocations', $filtres));
    }

    public function trouver(int|string $id): array
    {
        return $this->wrap($this->api->get("convocations/{$id}"));
    }

    public function creer(array $donnees): array
    {
        return $this->wrap($this->api->post('convocations', $donnees));
    }

    public function mettreAJour(int|string $id, array $donnees): array
    {
        return $this->wrap($this->api->put("convocations/{$id}", $donnees));
    }

    public function supprimer(int|string $id): array
    {
        return $this->wrap($this->api->delete("convocations/{$id}"));
    }

    public function beneficiaires(int|string $id): array
    {
        return $this->wrap($this->api->get("convocations/{$id}/beneficiaires"));
    }

    /**
     * Cree les centres d'examen d'une convocation. Renvoie les centres
     * crees dans le meme ordre que $centres, avec leur id reel en base -
     * necessaire pour rattacher ensuite chaque beneficiaire au bon centre.
     */
    public function creerCentres(int|string $id, array $centres): array
    {
        return $this->wrap($this->api->post("convocations/{$id}/centres", [
            'centres' => $centres,
        ]));
    }

    public function ajouterBeneficiaires(int|string $id, array $enseignantIds): array
    {
        return $this->wrap($this->api->post("convocations/{$id}/beneficiaires", [
            'enseignant_ids' => $enseignantIds,
        ]));
    }

    /**
     * Nouveau format : chaque beneficiaire porte sa propre fonction pour
     * CETTE convocation (President de jury, Surveillant/correcteur, ...).
     * $beneficiaires = [['enseignant_id' => 1, 'fonction' => '...'], ...]
     */
    public function ajouterBeneficiairesAvecFonction(int|string $id, array $beneficiaires): array
    {
        return $this->wrap($this->api->post("convocations/{$id}/beneficiaires", [
            'beneficiaires' => $beneficiaires,
        ]));
    }

    /**
     * Liste des types de convocation actifs (jury_examen, formation,
     * mission, reunion...), pour alimenter le select du formulaire de
     * création (etape 1).
     */
    public function typesConvocation(): array
    {
        return $this->wrap($this->api->get('types-convocation'));
    }

    public function rechercherEnseignants(?string $recherche = null): array
    {
        return $this->wrap($this->api->get('enseignants', array_filter([
            'search' => $recherche,
            'per_page' => 10,
        ])));
    }

    public function deposerFichier(int|string $id, \Illuminate\Http\UploadedFile $fichier): array
    {
        return $this->wrap($this->api->postMultipart("convocations/{$id}/fichier", [], [
            [
                'name' => 'fichier',
                'contents' => fopen($fichier->getRealPath(), 'r'),
                'filename' => $fichier->getClientOriginalName(),
            ],
        ]));
    }

    public function genererPdf(int|string $id): array
    {
        return $this->wrap($this->api->get("convocations/{$id}/pdf"));
    }

    /**
     * Retourne la reponse HTTP brute (non wrap()) car le controleur a besoin
     * du corps binaire du PDF pour le retransmettre au navigateur.
     */
    public function telechargerPdf(int|string $id)
    {
        return $this->api->get("convocations/{$id}/download");
    }

    public function envoyer(int|string $id, array $donnees): array
    {
        return $this->wrap($this->api->post("convocations/{$id}/envoyer", $donnees));
    }

    public function relancer(int|string $id, array $donnees): array
    {
        return $this->wrap($this->api->post("convocations/{$id}/relancer", $donnees));
    }

    public function suivi(int|string $id): array
    {
        return $this->wrap($this->api->get("convocations/{$id}/suivi"));
    }
}
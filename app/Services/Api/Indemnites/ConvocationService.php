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

    /**
     * Telecharge le PDF de la convocation (genere par le back au vol s'il
     * n'existe pas encore). Renvoie la reponse HTTP BRUTE (pas wrap()) :
     * c'est un fichier binaire, pas du JSON, on a besoin du corps et des
     * en-tetes (Content-Type/Content-Disposition) tels quels pour les
     * relayer au navigateur depuis ConvocationsController::downloadPdf().
     */
    public function telechargerPdf(int|string $id)
    {
        return $this->api->get("convocations/{$id}/download");
    }

    public function creer(array $donnees): array
    {
        return $this->wrap($this->api->post('convocations', $donnees));
    }

    public function mettreAJour(int|string $id, array $donnees): array
    {
        return $this->wrap($this->api->put("convocations/{$id}", $donnees));
    }

    /**
     * Fiche "Modifier" alignee sur l'assistant de creation ("IL FAUT QUE
     * EDIT SOIT EXACTEMENT COMME CREATE MAIS PREREMPLI") : UN seul appel
     * remplace toute la structure de la convocation (infos generales +
     * centres + leurs metiers + membres du jury) - cf.
     * ConvocationSyncController::sync() cote back.
     */
    public function mettreAJourStructure(int|string $id, array $donnees): array
    {
        return $this->wrap($this->api->put("convocations/{$id}/structure", $donnees));
    }

    public function supprimer(int|string $id): array
    {
        return $this->wrap($this->api->delete("convocations/{$id}"));
    }

    public function beneficiaires(int|string $id): array
    {
        return $this->wrap($this->api->get("convocations/{$id}/beneficiaires"));
    }

    public function centres(int|string $id): array
    {
        return $this->wrap($this->api->get("convocations/{$id}/centres"));
    }

    public function mettreAJourCentre(int|string $convocationId, int|string $centreId, array $donnees): array
    {
        return $this->wrap($this->api->put("convocations/{$convocationId}/centres/{$centreId}", $donnees));
    }

    public function supprimerCentre(int|string $convocationId, int|string $centreId): array
    {
        return $this->wrap($this->api->delete("convocations/{$convocationId}/centres/{$centreId}"));
    }

    public function mettreAJourBeneficiaire(int|string $convocationId, int|string $enseignantId, array $donnees): array
    {
        return $this->wrap($this->api->put("convocations/{$convocationId}/beneficiaires/{$enseignantId}", $donnees));
    }

    public function supprimerBeneficiaire(int|string $convocationId, int|string $enseignantId): array
    {
        return $this->wrap($this->api->delete("convocations/{$convocationId}/beneficiaires/{$enseignantId}"));
    }

    /**
     * Metiers d'UN centre (un centre peut en couvrir plusieurs, chacun
     * avec ses propres membres du jury).
     */
    public function ajouterMetier(int|string $convocationId, int|string $centreId, array $donnees): array
    {
        return $this->wrap($this->api->post("convocations/{$convocationId}/centres/{$centreId}/metiers", $donnees));
    }

    public function mettreAJourMetier(int|string $convocationId, int|string $centreId, int|string $metierId, array $donnees): array
    {
        return $this->wrap($this->api->put("convocations/{$convocationId}/centres/{$centreId}/metiers/{$metierId}", $donnees));
    }

    public function supprimerMetier(int|string $convocationId, int|string $centreId, int|string $metierId): array
    {
        return $this->wrap($this->api->delete("convocations/{$convocationId}/centres/{$centreId}/metiers/{$metierId}"));
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

    /**
     * Import d'une convocation depuis le modèle Word rempli (voir
     * telechargerModeleWord() ci-dessous). $utilisateurId est rattaché à
     * la convocation créée (colonne non nullable en base). Le type de
     * convocation est choisi dans le formulaire d'import (pas dans le
     * document Word) et transmis ici séparément.
     */
    public function importer(\Illuminate\Http\UploadedFile $fichier, int|string $utilisateurId, int|string $typeConvocationId): array
    {
        return $this->wrap($this->api->postMultipart('convocations/import', [
            'utilisateur_id' => $utilisateurId,
            'type_convocation_id' => $typeConvocationId,
        ], [
            [
                'name' => 'fichier',
                'contents' => fopen($fichier->getRealPath(), 'r'),
                'filename' => $fichier->getClientOriginalName(),
            ],
        ]));
    }

    /**
     * Réponse brute (pas de wrap() : le corps n'est pas du JSON mais le
     * contenu binaire du modèle .docx) — le contrôleur relaie ce corps et
     * les headers reçus tels quels au navigateur.
     */
    public function telechargerModeleWord(): \Illuminate\Http\Client\Response
    {
        return $this->api->get('convocations/modele-word');
    }

}
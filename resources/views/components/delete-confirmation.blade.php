<dialog class="delete-confirmation" id="delete-confirmation" aria-labelledby="delete-confirmation-title" aria-describedby="delete-confirmation-description">
    <button type="button" class="delete-confirmation-close" data-delete-cancel aria-label="Fermer">&times;</button>
    <span class="delete-confirmation-icon" aria-hidden="true"><i class="fa-solid fa-trash-can"></i></span>
    <h2 id="delete-confirmation-title">Confirmer la suppression</h2>
    <p id="delete-confirmation-description">Voulez-vous supprimer cet élément ?</p>
    <p class="delete-confirmation-note">Cette action retirera l’élément de la liste.</p>
    <div class="delete-confirmation-actions">
        <button class="btn-secondary" type="button" data-delete-cancel autofocus>Annuler</button>
        <button class="btn-primary" type="button" data-delete-accept><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Supprimer</button>
    </div>
</dialog>
@push('styles')
<style>
    .delete-confirmation { width: min(480px, calc(100vw - 32px)); max-height: calc(100dvh - 32px); overflow: auto; box-sizing: border-box; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; color: #334155; box-shadow: 0 24px 80px #0f172a33; }
    .delete-confirmation::backdrop { background: #0f172a99; backdrop-filter: blur(3px); }
    .delete-confirmation-close { position: absolute; top: 12px; right: 16px; border: 0; background: transparent; color: #64748b; font-size: 28px; cursor: pointer; }
    .delete-confirmation-icon { display: grid; place-items: center; width: 56px; height: 56px; border-radius: 16px; background: #fef2f2; color: #b91c1c; font-size: 24px; }
    .delete-confirmation h2 { margin: 20px 0 12px; color: var(--primary); font-size: 1.5rem; line-height: 1.3; }
    .delete-confirmation p { line-height: 1.6; overflow-wrap: anywhere; }
    .delete-confirmation-note { color: #64748b; font-size: .875rem; }
    .delete-confirmation-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px; margin-top: 28px; }
    .delete-confirmation [data-delete-accept] { background: #b91c1c; border-color: #b91c1c; }
    .delete-confirmation [data-delete-accept]:hover { background: #991b1b; }
    .delete-confirmation button:focus-visible { outline: 3px solid #86efac; outline-offset: 3px; }
</style>
@endpush
@push('scripts')
<script src="{{ asset('assets/js/delete-confirmation.js') }}?v={{ filemtime(public_path('assets/js/delete-confirmation.js')) }}" defer></script>
@endpush

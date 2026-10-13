@push('styles')
<style>
    .ia-filters { display: flex; flex-wrap: wrap; align-items: end; gap: 16px; padding: 22px; }
    .ia-filters label { display: grid; gap: 8px; min-width: 0; }
    .ia-filters input, .ia-filters select { max-width: 100%; padding: 10px 12px; border: 1px solid #dce3eb; border-radius: 8px; background: white; font: inherit; }
    .ia-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr)); gap: 24px; margin-top: 24px; }
    .ia-details dt { font-size: 14px; color: #64748b; }
    .ia-details dd { margin: 6px 0 0; overflow-wrap: anywhere; }
    @media (max-width: 640px) { .ia-filters label { width: 100%; } }
</style>
@endpush

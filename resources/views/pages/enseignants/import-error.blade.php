@error('csv')
  <div class="teacher-import-error" role="alert" style="color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #dc2626; border-radius: 8px; padding: 14px 18px; margin: 16px 0;">
    <strong>Import impossible</strong>
    <p style="color: inherit; margin: 6px 0 0;">{{ $message }}</p>
  </div>
@enderror

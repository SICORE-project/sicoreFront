@php
    $messages = [
        'success' => session('success'),
        'warning' => session('warning'),
        'error' => session('error'),
        'info' => session('info'),
    ];
@endphp

<div class="server-flashes" aria-live="polite" aria-atomic="true">
  @foreach ($messages as $type => $message)
    @if ($message)
      <div class="server-flash server-flash-{{ $type }}" role="status">
        <i class="fa-solid {{ $type === 'success' ? 'fa-circle-check' : ($type === 'warning' ? 'fa-triangle-exclamation' : ($type === 'error' ? 'fa-circle-xmark' : 'fa-circle-info')) }}" aria-hidden="true"></i>
        <span>{{ $message }}</span>
      </div>
    @endif
  @endforeach
</div>

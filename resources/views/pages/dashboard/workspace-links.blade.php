<ul>
    @foreach ($items as $entry)
        <li>
            @if (isset($entry['links']))
                <strong>{{ $entry['label'] }}</strong>
                @include('pages.dashboard.workspace-links', ['items' => $entry['links']])
            @else
                <a href="{{ route($entry['route']).(isset($entry['fragment']) ? '#'.$entry['fragment'] : '') }}">{{ $entry['label'] }}</a>
            @endif
        </li>
    @endforeach
</ul>

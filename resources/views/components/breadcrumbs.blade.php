@props(['items' => []])

@if(!empty($items))
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex flex-wrap items-center gap-2 text-xs text-neutral-600 dark:text-neutral-300">
            @foreach($items as $i => $crumb)
                @php
                    $isLast = $i === array_key_last($items);
                    $label = is_array($crumb) ? ($crumb['label'] ?? (string)$crumb) : (string)$crumb;
                    $url = is_array($crumb) ? ($crumb['url'] ?? null) : null;
                @endphp
                @if($i > 0)
                    <li class="text-neutral-400">/</li>
                @endif
                <li>
                    @if(!$isLast && $url)
                        <a href="{{ $url }}" class="hover:underline">{{ $label }}</a>
                    @else
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $label }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif


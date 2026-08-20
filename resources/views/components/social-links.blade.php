@props([
    'links' => null,
    'class' => '',
])

@php
    /** @var list<array{key: string, label: string, href: string}> $items */
    $items = $links ?? app(\App\Services\SocialLinksSettings::class)->filled();
@endphp

@if ($items !== [])
    <nav {{ $attributes->class(['social-links', $class]) }} aria-label="{{ __('site.social.nav_label') }}">
        <ul class="social-links__list">
            @foreach ($items as $item)
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        class="social-links__item social-links__item--{{ $item['key'] }}"
                        target="{{ str_starts_with($item['href'], 'mailto:') ? '_self' : '_blank' }}"
                        @if (! str_starts_with($item['href'], 'mailto:'))
                            rel="noopener noreferrer"
                        @endif
                        aria-label="{{ $item['label'] }}"
                    >
                        @include('components.social-icons.'.$item['key'])
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif

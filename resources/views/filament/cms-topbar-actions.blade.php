@php
    use App\Services\CmsUiLocale;
    use Filament\Support\Icons\Heroicon;

    $cmsLocale = app(CmsUiLocale::class);
    $currentLocale = app()->getLocale();
    $siteUrl = $cmsLocale->publicSiteUrl();
    $panel = filament()->getPanel('cms-safehouse');
@endphp

{{-- Inline layout: Filament panel CSS does not include app Tailwind utilities from this view. --}}
<div class="cms-topbar-actions">
    <x-filament::button
        tag="a"
        :href="$siteUrl"
        target="_blank"
        rel="noopener noreferrer"
        color="gray"
        size="sm"
        :icon="Heroicon::OutlinedArrowTopRightOnSquare"
    >
        {{ __('cms.actions.back_to_site') }}
    </x-filament::button>

    <div
        class="cms-topbar-actions__locales"
        role="group"
        aria-label="{{ __('cms.locale.label') }}"
    >
        @foreach ($cmsLocale->available() as $locale)
            @if ($currentLocale === $locale)
                <x-filament::button
                    color="primary"
                    size="sm"
                    disabled
                >
                    {{ strtoupper($locale) }}
                </x-filament::button>
            @else
                <form
                    method="post"
                    action="{{ $panel->route('locale.update', ['locale' => $locale]) }}"
                    class="cms-topbar-actions__locale-form"
                >
                    @csrf
                    <x-filament::button
                        type="submit"
                        color="gray"
                        size="sm"
                        :title="__('cms.locale.switch_to', ['locale' => __('cms.locale.names.'.$locale)])"
                        :aria-label="__('cms.locale.switch_to', ['locale' => __('cms.locale.names.'.$locale)])"
                    >
                        {{ strtoupper($locale) }}
                    </x-filament::button>
                </form>
            @endif
        @endforeach
    </div>
</div>

<style>
    .cms-topbar-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
        margin-inline-end: 0.25rem;
    }

    .cms-topbar-actions__locales {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .cms-topbar-actions__locale-form {
        display: inline-flex;
        margin: 0;
    }
</style>

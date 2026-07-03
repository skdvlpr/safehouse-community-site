@if (session('contact_success'))
    <p class="template-contact-form__success" role="status">{{ session('contact_success') }}</p>
@endif

@php
    $deskOptions = \App\Support\ContactDeskOptions::forForm();
    $selectedDesk = old('desk', array_key_first($deskOptions));
@endphp

<form
    class="template-contact-form"
    method="POST"
    action="{{ route('contact.store', ['locale' => app()->getLocale()]) }}"
    aria-label="{{ __('site.pages.contact_form_heading') }}"
>
    @csrf

    <div class="template-contact-form__field template-contact-form__field--honeypot" aria-hidden="true">
        <label for="contact-company">{{ __('site.pages.contact_company') }}</label>
        <input id="contact-company" type="text" name="company" tabindex="-1" autocomplete="off">
    </div>

    <div class="template-contact-form__field">
        <label for="contact-name">{{ __('site.pages.contact_name') }}</label>
        <input
            id="contact-name"
            type="text"
            name="name"
            value="{{ old('name') }}"
            required
            maxlength="255"
            placeholder="{{ __('site.pages.contact_name_placeholder') }}"
            @class(['template-contact-form__input--invalid' => $errors->has('name')])
        >
        @error('name')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="template-contact-form__field">
        <label for="contact-email">{{ __('site.pages.contact_email') }}</label>
        <input
            id="contact-email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            maxlength="255"
            placeholder="{{ __('site.pages.contact_email_placeholder') }}"
            @class(['template-contact-form__input--invalid' => $errors->has('email')])
        >
        @error('email')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    @if (count($deskOptions) > 0)
        <div class="template-contact-form__field">
            <label id="contact-desk-label">{{ __('site.pages.contact_desk') }}</label>
            <div
                class="sportello-select @error('desk') sportello-select--invalid @enderror"
                data-sportello-select
            >
                <input
                    type="hidden"
                    name="desk"
                    value="{{ $selectedDesk }}"
                    required
                    data-sportello-select-input
                >
                <button
                    type="button"
                    id="contact-desk"
                    class="sportello-select__trigger"
                    data-sportello-select-trigger
                    aria-haspopup="listbox"
                    aria-expanded="false"
                    aria-labelledby="contact-desk-label"
                >
                    <span data-sportello-select-value>
                        {{ $deskOptions[$selectedDesk] ?? reset($deskOptions) }}
                    </span>
                    <svg class="sportello-select__chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul
                    class="sportello-select__menu"
                    data-sportello-select-menu
                    role="listbox"
                    aria-labelledby="contact-desk-label"
                    hidden
                >
                    @foreach ($deskOptions as $value => $label)
                        <li
                            role="option"
                            data-sportello-select-option
                            data-value="{{ $value }}"
                            aria-selected="{{ (string) $selectedDesk === (string) $value ? 'true' : 'false' }}"
                            tabindex="-1"
                            @class([
                                'sportello-select__option',
                                'is-selected' => (string) $selectedDesk === (string) $value,
                            ])
                        >
                            {{ $label }}
                        </li>
                    @endforeach
                </ul>
            </div>
            @error('desk')
                <p class="template-contact-form__error">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="template-contact-form__field">
        <label for="contact-message">{{ __('site.pages.contact_message') }}</label>
        <textarea
            id="contact-message"
            name="message"
            rows="5"
            required
            maxlength="5000"
            placeholder="{{ __('site.pages.contact_message_placeholder') }}"
            @class(['template-contact-form__input--invalid' => $errors->has('message')])
        >{{ old('message') }}</textarea>
        @error('message')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="template-contact-form__field">
        <label class="template-contact-form__checkbox">
            <input
                type="checkbox"
                name="gdpr_consent"
                value="1"
                @checked(old('gdpr_consent'))
                required
            >
            <span>{{ __('site.pages.contact_gdpr_consent') }}</span>
        </label>
        @error('gdpr_consent')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="safehouse-btn-primary w-full">
        {{ __('site.pages.contact_submit') }}
    </button>

    <p class="template-contact-form__note">{{ __('site.pages.contact_form_notice') }}</p>
</form>

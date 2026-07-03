@if (session('contact_success'))
    <p class="template-contact-form__success" role="status">{{ session('contact_success') }}</p>
@endif

@php($deskOptions = \App\Support\ContactDeskOptions::forForm())

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
            <label for="contact-desk">{{ __('site.pages.contact_desk') }}</label>
            <select
                id="contact-desk"
                name="desk"
                required
                @class(['template-contact-form__input--invalid' => $errors->has('desk')])
            >
                @foreach ($deskOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('desk', array_key_first($deskOptions)) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
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

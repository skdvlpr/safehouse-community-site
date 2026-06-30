@if (session('volunteer_success'))
    <p class="template-contact-form__success" role="status">{{ session('volunteer_success') }}</p>
@endif

<form
    class="template-contact-form"
    method="POST"
    action="{{ route('volunteers.store', ['locale' => app()->getLocale()]) }}"
    aria-label="{{ __('site.volunteer.form_heading') }}"
>
    @csrf

    <div class="template-contact-form__field template-contact-form__field--honeypot" aria-hidden="true">
        <label for="volunteer-company">{{ __('site.volunteer.company') }}</label>
        <input id="volunteer-company" type="text" name="company" tabindex="-1" autocomplete="off">
    </div>

    <div class="template-contact-form__field">
        <label for="volunteer-name">{{ __('site.volunteer.name') }}</label>
        <input
            id="volunteer-name"
            type="text"
            name="name"
            value="{{ old('name') }}"
            required
            maxlength="255"
            placeholder="{{ __('site.volunteer.name_placeholder') }}"
            @class(['template-contact-form__input--invalid' => $errors->has('name')])
        >
        @error('name')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="template-contact-form__field">
        <label for="volunteer-email">{{ __('site.volunteer.email') }}</label>
        <input
            id="volunteer-email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            maxlength="255"
            placeholder="{{ __('site.volunteer.email_placeholder') }}"
            @class(['template-contact-form__input--invalid' => $errors->has('email')])
        >
        @error('email')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="template-contact-form__field">
        <label for="volunteer-phone">{{ __('site.volunteer.phone') }}</label>
        <input
            id="volunteer-phone"
            type="tel"
            name="phone"
            value="{{ old('phone') }}"
            maxlength="50"
            placeholder="{{ __('site.volunteer.phone_placeholder') }}"
            @class(['template-contact-form__input--invalid' => $errors->has('phone')])
        >
        @error('phone')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="template-contact-form__field">
        <label for="volunteer-message">{{ __('site.volunteer.message') }}</label>
        <textarea
            id="volunteer-message"
            name="message"
            rows="5"
            maxlength="5000"
            placeholder="{{ __('site.volunteer.message_placeholder') }}"
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
            <span>{{ __('site.volunteer.gdpr_consent') }}</span>
        </label>
        @error('gdpr_consent')
            <p class="template-contact-form__error">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="safehouse-btn-primary w-full">
        {{ __('site.volunteer.submit') }}
    </button>

    <p class="template-contact-form__note">{{ __('site.volunteer.form_notice') }}</p>
</form>

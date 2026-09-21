@if (session('membership_success'))
    <p class="template-contact-form__success mt-8" role="status">{{ session('membership_success') }}</p>
@endif

@error('membership_mail')
    <p class="template-contact-form__error mt-4" role="alert">{{ $message }}</p>
@enderror

<dialog id="socio-dialog" class="socio-dialog" @if ($errors->any() && old('membership_form')) data-socio-had-errors @endif>
    <div class="socio-dialog__inner">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <p class="template-eyebrow">{{ __('site.membership.eyebrow') }}</p>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('site.membership.title') }}</h2>
                <p class="mt-2 text-sm text-safehouse-muted">{{ __('site.membership.lead') }}</p>
            </div>
            <button type="button" class="socio-dialog__close" data-socio-close aria-label="{{ __('site.membership.close') }}">×</button>
        </div>

        <form
            class="template-contact-form"
            method="POST"
            action="{{ route('membership.store', ['locale' => app()->getLocale()]) }}"
            aria-label="{{ __('site.membership.title') }}"
        >
            @csrf
            <input type="hidden" name="membership_form" value="1">

            <div class="template-contact-form__field template-contact-form__field--honeypot" aria-hidden="true">
                <label for="socio-company">{{ __('site.volunteer.company') }}</label>
                <input id="socio-company" type="text" name="company" tabindex="-1" autocomplete="off">
            </div>

            <div class="socio-dialog__grid">
                <div class="template-contact-form__field">
                    <label for="socio-name">{{ __('site.membership.name') }}</label>
                    <input id="socio-name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" @class(['template-contact-form__input--invalid' => $errors->has('name')])>
                    @error('name')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-last-name">{{ __('site.membership.last_name') }}</label>
                    <input id="socio-last-name" type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="255" @class(['template-contact-form__input--invalid' => $errors->has('last_name')])>
                    @error('last_name')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-birth-place">{{ __('site.membership.birth_place') }}</label>
                    <input id="socio-birth-place" type="text" name="birth_place" value="{{ old('birth_place') }}" required maxlength="255">
                    @error('birth_place')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-birth-province">{{ __('site.membership.birth_province') }}</label>
                    <input id="socio-birth-province" type="text" name="birth_province" value="{{ old('birth_province') }}" required maxlength="8" placeholder="RM">
                    @error('birth_province')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-birth-date">{{ __('site.membership.birth_date') }}</label>
                    <input id="socio-birth-date" type="date" name="birth_date" value="{{ old('birth_date') }}" required>
                    @error('birth_date')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-tax-code">{{ __('site.membership.tax_code') }}</label>
                    <input id="socio-tax-code" type="text" name="tax_code" value="{{ old('tax_code') }}" required maxlength="16" autocapitalize="characters">
                    @error('tax_code')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-city">{{ __('site.membership.city') }}</label>
                    <input id="socio-city" type="text" name="city" value="{{ old('city') }}" required maxlength="255">
                    @error('city')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-province">{{ __('site.membership.province') }}</label>
                    <input id="socio-province" type="text" name="province" value="{{ old('province') }}" required maxlength="8" placeholder="TO">
                    @error('province')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field template-contact-form__field--full">
                    <label for="socio-address">{{ __('site.membership.address') }}</label>
                    <input id="socio-address" type="text" name="address" value="{{ old('address') }}" required maxlength="255">
                    @error('address')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-cap">{{ __('site.membership.cap') }}</label>
                    <input id="socio-cap" type="text" name="cap" value="{{ old('cap') }}" required maxlength="10">
                    @error('cap')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field">
                    <label for="socio-phone">{{ __('site.membership.phone') }}</label>
                    <input id="socio-phone" type="tel" name="phone" value="{{ old('phone') }}" required maxlength="50">
                    @error('phone')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
                <div class="template-contact-form__field template-contact-form__field--full">
                    <label for="socio-email">{{ __('site.membership.email') }}</label>
                    <input id="socio-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255">
                    @error('email')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="template-contact-form__field">
                <label class="template-contact-form__checkbox">
                    <input type="checkbox" name="accept_statute" value="1" @checked(old('accept_statute')) required>
                    <span>{{ __('site.membership.accept_statute') }}</span>
                </label>
                @error('accept_statute')<p class="template-contact-form__error">{{ $message }}</p>@enderror
            </div>
            <div class="template-contact-form__field">
                <label class="template-contact-form__checkbox">
                    <input type="checkbox" name="accept_mission" value="1" @checked(old('accept_mission')) required>
                    <span>{{ __('site.membership.accept_mission') }}</span>
                </label>
            </div>
            <div class="template-contact-form__field">
                <label class="template-contact-form__checkbox">
                    <input type="checkbox" name="accept_fee" value="1" @checked(old('accept_fee')) required>
                    <span>{{ __('site.membership.accept_fee') }}</span>
                </label>
            </div>

            <fieldset class="template-contact-form__field">
                <legend class="mb-1.5 block text-sm font-medium">{{ __('site.membership.newsletter_legend') }}</legend>
                <label class="template-contact-form__checkbox">
                    <input type="radio" name="newsletter_consent" value="1" @checked(old('newsletter_consent') === '1') required>
                    <span>{{ __('site.membership.newsletter_yes') }}</span>
                </label>
                <label class="template-contact-form__checkbox">
                    <input type="radio" name="newsletter_consent" value="0" @checked(old('newsletter_consent') === '0')>
                    <span>{{ __('site.membership.newsletter_no') }}</span>
                </label>
                @error('newsletter_consent')<p class="template-contact-form__error">{{ $message }}</p>@enderror
            </fieldset>

            @php($turnstile = app(\App\Services\TurnstileVerifier::class))
            @if ($turnstile->enabled())
                <div class="template-contact-form__field">
                    <div class="cf-turnstile" data-sitekey="{{ $turnstile->siteKey() }}" data-size="flexible"></div>
                    @error('cf-turnstile-response')<p class="template-contact-form__error">{{ $message }}</p>@enderror
                </div>
            @endif

            <button type="submit" class="safehouse-btn-primary w-full">{{ __('site.membership.submit') }}</button>
            <p class="template-contact-form__note">{{ __('site.membership.notice') }}</p>
        </form>
    </div>
</dialog>

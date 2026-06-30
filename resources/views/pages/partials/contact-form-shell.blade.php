<form class="template-contact-form" aria-label="{{ __('site.pages.contact_form_heading') }}">
    <div class="template-contact-form__field">
        <label for="contact-name">{{ __('site.pages.contact_name') }}</label>
        <input id="contact-name" type="text" disabled placeholder="{{ __('site.pages.contact_name_placeholder') }}">
    </div>
    <div class="template-contact-form__field">
        <label for="contact-email">{{ __('site.pages.contact_email') }}</label>
        <input id="contact-email" type="email" disabled placeholder="nome@esempio.it">
    </div>
    <div class="template-contact-form__field">
        <label for="contact-message">{{ __('site.pages.contact_message') }}</label>
        <textarea id="contact-message" rows="5" disabled placeholder="{{ __('site.pages.contact_message_placeholder') }}"></textarea>
    </div>
    <button type="button" class="safehouse-btn-primary w-full opacity-60" disabled>
        {{ __('site.pages.contact_submit') }}
    </button>
    <p class="template-contact-form__note">{{ __('site.pages.contact_form_placeholder') }}</p>
</form>

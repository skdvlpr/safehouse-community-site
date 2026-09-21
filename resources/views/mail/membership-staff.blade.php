{{ __('site.membership.mail.staff_intro', [], 'it') }}
{{ __('site.membership.mail.staff_name', [], 'it') }}: {{ $payload['name'] }}
{{ __('site.membership.mail.staff_last_name', [], 'it') }}: {{ $payload['last_name'] }}
{{ __('site.membership.mail.staff_email', [], 'it') }}: {{ $payload['email'] }}
{{ __('site.membership.mail.staff_phone', [], 'it') }}: {{ $payload['phone'] }}
{{ __('site.membership.mail.staff_tax_code', [], 'it') }}: {{ $payload['tax_code'] }}
{{ __('site.membership.mail.staff_birth', [], 'it') }}: {{ $payload['birth_place'] }} ({{ $payload['birth_province'] }}) {{ $payload['birth_date'] }}
{{ __('site.membership.mail.staff_address', [], 'it') }}: {{ $payload['address'] }}, {{ $payload['cap'] }} {{ $payload['city'] }} ({{ $payload['province'] }})
{{ __('site.membership.mail.staff_newsletter', [], 'it') }}: {{ ($payload['newsletter_consent'] ?? '0') === '1' ? 'acconsente' : 'non acconsente' }}

{{ __('site.membership.mail.staff_signature', [], 'it') }}

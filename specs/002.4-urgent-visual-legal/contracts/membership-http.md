# Membership HTTP

Cite: [Laravel validation](https://laravel.com/docs/13.x/validation), [Laravel mail](https://laravel.com/docs/13.x/mail), [rate limiting](https://laravel.com/docs/13.x/routing#rate-limiting), [Turnstile](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/).

`POST /{locale}/membership-application` named `membership.store`.

Middleware: `setlocale`, `throttle:membership` (3/hour/IP).

Success: 302 to the published `diventa-socio` slug + `membership_success` flash.

Validation error: 302 back with errors; dialog reopens via `data-socio-had-errors`.

SMTP missing: 302 back with `membership_mail`.

Honeypot `company` filled: 302 success, no mail, no Lead.

No `membership_success` measurement event in this feature (GTM not requested).

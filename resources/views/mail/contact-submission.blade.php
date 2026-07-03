Nuovo messaggio dal sito Safe House
====================================

Nome: {{ $submission->name }}
Email: {{ $submission->email }}
@if ($submission->correlation_token)
Riferimento: [SH-{{ $submission->correlation_token }}]
@endif

Messaggio:
----------
{{ $submission->message }}

--
Inviato il {{ $submission->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }} UTC
ID richiesta: #{{ $submission->id }}

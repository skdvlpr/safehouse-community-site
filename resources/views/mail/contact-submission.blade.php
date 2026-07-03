Nuovo messaggio dal sito Safe House
====================================

Nome: {{ $submission->name }}
Email: {{ $submission->email }}
@php($deskLabel = trim((string) (\App\Support\ContactDeskOptions::deskConfig($submission->desk)['label'] ?? '')))
@php($caseType = trim((string) (\App\Support\ContactDeskOptions::caseTypeForDesk($submission->desk) ?? '')))
@if ($deskLabel !== '')
Sportello: {{ $deskLabel }}
@endif
@if ($caseType !== '')
Tipo segnalazione: {{ $caseType }}
@endif
@if ($submission->correlation_token)
Riferimento: [SH-{{ $submission->correlation_token }}]
@endif

Messaggio:
----------
{{ $submission->message }}

--
Inviato il {{ $submission->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }} UTC
ID richiesta: #{{ $submission->id }}

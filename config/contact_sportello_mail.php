<?php

return [

    'storage_key' => 'contact.sportello_mail',

    'placeholders' => [
        '{{name}}',
        '{{email}}',
        '{{message}}',
        '{{desk_label}}',
        '{{case_type}}',
        '{{reference}}',
        '{{submitted_at}}',
        '{{request_id}}',
    ],

    'default_subject' => [
        'it' => '[SH-{{reference_token}}] Nuovo messaggio — {{name}}',
    ],

    'default_body' => [
        'it' => <<<'HTML'
<p>Gentile {{name}},</p>
<p>abbiamo registrato la tua richiesta tramite il sito <strong>Safe House</strong>.</p>
<p>Un operatore dello sportello <strong>{{desk_label}}</strong> ti risponderà al più presto.</p>
<p><strong>Il tuo messaggio</strong></p>
<blockquote>{{message}}</blockquote>
<p style="color:#71717a;font-size:14px;">Riferimento: {{reference}} · Inviato il {{submitted_at}}</p>
HTML,
    ],

];

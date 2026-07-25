<?php

return [

    'storage_key' => 'donations.settings',

    /**
     * Singleton DonationCampaign slug used by RecurringDonationCampaignService / CMS tab.
     * Never has a fundraising goal; excluded from "Campagne donazioni" CRUD.
     */
    'recurring_campaign_slug' => env('DONATIONS_RECURRING_SLUG', 'donazione-ricorrente'),

    'five_per_mille' => [
        'enabled' => true,
        'codice_fiscale' => env('DONATIONS_CODICE_FISCALE', ''),
        'menu_label' => [
            'it' => '5 x 1000',
            'en' => '5 x 1000',
            'ru' => '5 x 1000',
        ],
        'heading' => [
            'it' => 'Dona 5 x 1000',
            'en' => 'Donate 5 x 1000',
            'ru' => 'Пожертвуйте 5 x 1000',
        ],
        'lead' => [
            'it' => 'Destina una parte dell’IRPEF a Safe House ETS senza alcun costo per te: basta indicare il nostro codice fiscale nella dichiarazione dei redditi.',
            'en' => 'Allocate part of your income tax to Safe House ETS at no cost to you: enter our tax code on your tax return.',
            'ru' => 'Направьте часть подоходного налога в Safe House ETS без дополнительных расходов — укажите наш codice fiscale в налоговой декларации.',
        ],
        'body' => [
            'it' => '<p>Il <strong>5 x 1000</strong> è una quota dell’imposta sul reddito che lo Stato destina gratuitamente alle organizzazioni del terzo settore che scegli tu.</p><p>Non costa nulla in più: non è una donazione aggiuntiva, ma una scelta su come impiegare una piccola parte dell’IRPEF già dovuta.</p><p>Con il tuo 5 x 1000 sosteniamo accoglienza, pasti, orientamento legale e interventi sul territorio per persone in difficoltà.</p>',
            'en' => '<p>The <strong>5 x 1000</strong> is a share of income tax that the Italian state allocates free of charge to third-sector organisations you choose.</p><p>It costs you nothing extra: you are not making an additional donation, only deciding how a small part of your tax is used.</p><p>Your 5 x 1000 helps us provide shelter, meals, legal guidance and outreach for people in need.</p>',
            'ru' => '<p><strong>5 x 1000</strong> — это доля подоходного налога, которую государство бесплатно направляет выбранной вами некоммерческой организации.</p><p>Это не дополнительный платёж: вы лишь указываете, куда направить небольшую часть уже уплачиваемого налога.</p><p>Ваш выбор помогает нам обеспечивать приют, питание, юридическую поддержку и работу на территории.</p>',
        ],
        'instructions' => [
            'it' => '<ol><li>Apri la dichiarazione dei redditi (<strong>Modello 730</strong>, <strong>Redditi persone fisiche</strong> o <strong>CUD</strong>).</li><li>Cerca il riquadro <strong>«Destina il 5 x 1000»</strong> / finanziamento del terzo settore.</li><li><strong>Firma</strong> nel riquadro per autorizzare la scelta.</li><li>Scrivi il <strong>codice fiscale</strong> dell’associazione nel campo dedicato.</li><li>Presenta la dichiarazione entro la scadenza prevista.</li></ol>',
            'en' => '<ol><li>Open your Italian tax return (<strong>Modello 730</strong>, <strong>Redditi PF</strong> or <strong>CUD</strong>).</li><li>Find the <strong>“5 x 1000”</strong> / third-sector funding box.</li><li><strong>Sign</strong> the box to authorise your choice.</li><li>Enter the association’s <strong>codice fiscale</strong> in the dedicated field.</li><li>Submit your return before the deadline.</li></ol>',
            'ru' => '<ol><li>Откройте налоговую декларацию (<strong>Modello 730</strong>, <strong>Redditi PF</strong> или <strong>CUD</strong>).</li><li>Найдите блок <strong>«5 x 1000»</strong> / finanziamento del terzo settore.</li><li><strong>Подпишите</strong> поле, чтобы подтвердить выбор.</li><li>Укажите <strong>codice fiscale</strong> ассоциации в соответствующей графе.</li><li>Подайте декларацию до установленного срока.</li></ol>',
        ],
        'codice_label' => [
            'it' => 'Codice fiscale da indicare in dichiarazione',
            'en' => 'Tax code to enter on your return',
            'ru' => 'Codice fiscale для декларации',
        ],
    ],

    'bank_transfer' => [
        'enabled' => true,
        'iban' => env('DONATIONS_IBAN', ''),
        'beneficiary' => env('DONATIONS_BENEFICIARY', 'Safe House ETS'),
        'heading' => [
            'it' => 'Bonifico bancario',
            'en' => 'Bank transfer',
            'ru' => 'Банковский перевод',
        ],
        'body' => [
            'it' => '<p>Puoi sostenere Safe House con un bonifico sul conto dell’associazione. Indica nella causale il tuo nome e, se possibile, «Donazione».</p>',
            'en' => '<p>You can support Safe House by bank transfer to the association account. Use your name in the payment reference and, if possible, the word “Donation”.</p>',
            'ru' => '<p>Вы можете поддержать Safe House банковским переводом на счёт ассоциации. Укажите в назначении платежа своё имя и, по возможности, «Donazione».</p>',
        ],
        'iban_label' => [
            'it' => 'IBAN',
            'en' => 'IBAN',
            'ru' => 'IBAN',
        ],
        'beneficiary_label' => [
            'it' => 'Intestatario',
            'en' => 'Account holder',
            'ru' => 'Получатель',
        ],
    ],

];

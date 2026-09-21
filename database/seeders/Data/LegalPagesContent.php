<?php

declare(strict_types=1);

namespace Database\Seeders\Data;

/**
 * Operational Privacy + Cookie policy copy for Safe House ETS.
 * Upserted by LegalPagesSeeder (oneshot) or `php artisan site:sync-legal-pages`.
 */
final class LegalPagesContent
{
    /**
     * @return array{privacy: array<string, mixed>, cookie: array<string, mixed>}
     */
    public static function pages(): array
    {
        return [
            'privacy' => [
                'template' => 'legal',
                'is_published' => true,
                'title' => [
                    'it' => 'Privacy policy',
                    'en' => 'Privacy policy',
                    'ru' => 'Политика конфиденциальности',
                ],
                'slug' => [
                    'it' => 'privacy-policy',
                    'en' => 'privacy-policy',
                    'ru' => 'privacy-policy',
                ],
                'body' => [
                    'it' => self::privacyIt(),
                    'en' => self::privacyEn(),
                    'ru' => self::privacyRu(),
                ],
            ],
            'cookie' => [
                'template' => 'legal',
                'is_published' => true,
                'title' => [
                    'it' => 'Cookie policy',
                    'en' => 'Cookie policy',
                    'ru' => 'Политика cookie',
                ],
                'slug' => [
                    'it' => 'cookie-policy',
                    'en' => 'cookie-policy',
                    'ru' => 'cookie-policy',
                ],
                'body' => [
                    'it' => self::cookieIt(),
                    'en' => self::cookieEn(),
                    'ru' => self::cookieRu(),
                ],
            ],
        ];
    }

    private static function privacyIt(): string
    {
        return <<<'HTML'
<p>Ultimo aggiornamento: <strong>21 settembre 2026</strong>. Titolare: <strong>Safe House ETS</strong> (Codice Fiscale <strong>96629270586</strong>).</p>

<h2>1. Titolare del trattamento</h2>
<p>Il titolare del trattamento è <strong>Safe House ETS</strong>, ente del Terzo settore, Codice Fiscale <strong>96629270586</strong>.</p>
<ul>
<li><strong>Sito pubblico:</strong> <a href="https://safehouse.community">https://safehouse.community</a></li>
<li><strong>Contatti privacy:</strong> <a href="mailto:info@safehouse.community">info@safehouse.community</a></li>
<li><strong>Sede legale:</strong> Via Delleani 26, 00042 Anzio (RM)</li>
<li><strong>Iscrizione RUNTS:</strong> Rep. n. 156768</li>
<li><strong>Sede operativa:</strong> Torino (Piemonte)</li>
</ul>
<p>Non risulta nominato un DPO (Data Protection Officer); in caso di nomina, i riferimenti saranno aggiornati qui.</p>

<h2>2. Ambito di questa informativa</h2>
<p>Questa informativa riguarda:</p>
<ul>
<li>i visitatori del sito <strong>safehouse.community</strong> (navigazione, moduli, donazioni, cookie);</li>
<li>i trattamenti nei <strong>sistemi gestionali interni</strong> usati dallo staff autorizzato (contabilità associativa e fascicoli degli sportelli);</li>
<li>le integrazioni <strong>Google Calendar</strong> e <strong>Google Drive</strong> dell’area riservata dello staff.</li>
</ul>
<p>Il sito pubblico e i sistemi interni sono ospitati sullo <strong>stesso server virtuale (VPS) Aruba Cloud, in Italia</strong>.</p>
<p>Rispettiamo la riservatezza. I dati che ci affidi servono a rispondere, a gestire l’associazione e — solo se accetti i cookie analitici — a capire in forma aggregata come viene usato il sito. <strong>Non comunichiamo a terzi, per finalità pubblicitarie o di profilazione commerciale, il nome, il telefono o l’indirizzo email</strong> di chi ci scrive o dona.</p>

<h2>3. Dati trattati e finalità (sito pubblico)</h2>
<h3>3.1 Navigazione e sicurezza</h3>
<p>Dati tecnici di connessione (indirizzo IP, user-agent, log di sicurezza) per erogare il sito, prevenire abusi e garantire la sicurezza. Base giuridica: legittimo interesse (art. 6.1.f GDPR) e, ove applicabile, obbligo legale. Per la misurazione del pubblico (punto 3.7) Safe House <strong>non conserva l’indirizzo IP grezzo del visitatore</strong>.</p>
<h3>3.2 Moduli di contatto (generico, sportello legale, sportello digitale)</h3>
<p>Nome, email, messaggio, sportello scelto e consenso. Finalità: leggere e gestire la richiesta. Conserviamo il messaggio nei sistemi del sito, lo inviamo alla casella dello sportello e una copia al mittente. Il fascicolo può essere collegato ai sistemi interni dello sportello. Base: misure precontrattuali / legittimo interesse e, sul modulo, consenso. Dell’invio teniamo solo un hash di IP e user-agent, non l’indirizzo in chiaro.</p>
<h3>3.3 Candidatura volontario</h3>
<p>Nome, cognome, email, telefono, messaggio e consenso. Finalità: valutare la candidatura e ricontattarti. Il contenuto è inviato alla casella volontariato dello staff e una conferma arriva al candidato. Base: consenso e misure precontrattuali.</p>
<h3>3.4 Donazioni online (Stripe)</h3>
<p>Nome, tipologia (persona o organizzazione), email e/o telefono, eventuale commento, importo, campagna e se la donazione è una tantum o ricorrente. I dati della carta <strong>non transitano sui nostri server</strong>: il pagamento è gestito da <strong>Stripe</strong>. Per la contabilità associativa registriamo nei sistemi interni i metadati della donazione (inclusi, quando disponibili, commissioni e importo netto) e i recapiti necessari a ricevute e libri sociali. Base: esecuzione del rapporto di donazione e obblighi contabili e di legge.</p>
<h3>3.5 5 per mille e contenuti informativi</h3>
<p>La pubblicazione del Codice Fiscale non comporta raccolta di dati aggiuntivi oltre alla normale navigazione.</p>
<h3>3.6 Preferenza cookie</h3>
<p>Registriamo la scelta (solo necessari oppure analitici) e un log di audit (hash di IP e user-agent, data). Base: obbligo di dimostrare il consenso.</p>
<h3>3.7 Misurazione del pubblico (Google Analytics 4 tramite Google Tag Manager)</h3>
<p>Se scegli <em>Accetta tutti</em> oppure salvi le preferenze con i cookie analitici attivi, e se lo strumento è acceso dallo staff, carichiamo <strong>Google Analytics 4</strong> tramite <strong>Google Tag Manager</strong>. Serve a statistiche aggregate (visite, pagine, provenienza, dispositivo, area geografica approssimativa, coinvolgimento) e a sapere che un percorso è stato completato (donazione, candidatura volontario, messaggio di contatto o sportello). Non inviamo a questo strumento nomi, email, telefoni, testi dei moduli o importi. Non lo usiamo per pubblicità o remarketing. Base: consenso. Destinatari: Google Ireland / Google LLC. Google indica di usare l’IP per una geolocalizzazione approssimativa e poi di scartarlo; Safe House non conserva l’IP grezzo per questa misurazione. Questa sezione è distinta dalle API Calendar/Drive dello staff (punto 5).</p>
<h3>3.8 Protezione dei moduli (Cloudflare Turnstile)</h3>
<p>Sui moduli di contatto e volontariato usiamo <strong>Cloudflare Turnstile</strong> per distinguere le persone dai sistemi automatici. È uno strumento di sicurezza, non di pubblicità. In verifica può essere trasmesso a Cloudflare il token del widget e l’indirizzo IP della richiesta.</p>

<h2>4. Cookie</h2>
<p>Sul sito pubblico usiamo cookie necessari (sessione, sicurezza, preferenza di consenso). Google Analytics 4, tramite Google Tag Manager, si carica solo dopo <em>Accetta tutti</em> o <em>Salva preferenze</em> con gli analitici selezionati. I cookie di marketing e pubblicità <strong>non sono in uso</strong>. Dettagli: <a href="/it/cookie-policy">Cookie policy</a>.</p>

<h2 id="google-api-services">5. Google API Services — area riservata staff (Calendar &amp; Drive)</h2>
<p>Questa sezione è destinata anche alla verifica OAuth di Google Cloud / Google API Services User Data Policy (Limited Use).</p>
<p><strong>Applicazione:</strong> area riservata Safe House — integrazione Google Calendar / Drive per lo staff.</p>
<p><strong>Utenti:</strong> solo personale autorizzato di Safe House ETS (non il pubblico del sito).</p>
<p><strong>Dati Google a cui l’app può accedere</strong> (scope OAuth in uso):</p>
<ul>
<li>identità di base dell’account Google collegato (<code>openid</code>, <code>email</code>, <code>profile</code>);</li>
<li><strong>Google Calendar</strong> — lettura/scrittura eventi e calendari per sincronizzare appuntamenti e date rilevanti;</li>
<li><strong>Google Drive</strong> con scope limitato <code>drive.file</code> — accesso solo ai file creati o aperti dall’app (non all’intero Drive dell’utente).</li>
</ul>
<p><strong>Come usiamo i dati Google:</strong> esclusivamente per le funzioni di integrazione richieste dallo staff. I token OAuth sono conservati lato server e non esposti al browser come segreti.</p>
<p><strong>Cosa non facciamo:</strong> non vendiamo dati Google; non li usiamo per pubblicità; non li trasferiamo a terzi non correlati alla fornitura del servizio; non usiamo i dati Google per addestrare modelli di AI generalizzati.</p>
<p>Safe House ETS si impegna a rispettare la <a href="https://developers.google.com/terms/api-services-user-data-policy" rel="noopener noreferrer" target="_blank">Google API Services User Data Policy</a>, inclusa la Limited Use.</p>
<p><strong>Disconnessione:</strong> lo staff può disconnettere Google dall’area account esterni. Alla disconnessione i token vengono invalidati o rimossi; i record operativi restano soggetti alle retention interne.</p>

<h2>6. Sistemi gestionali interni</h2>
<p>I sistemi interni gestiscono anagrafiche e attività associative (contatti, membri, volontari, donazioni e rendicontazione, fascicoli sportello) per le finalità istituzionali di Safe House ETS. Destinatari: solo utenti autenticati con ruoli e controlli di accesso. Base: legittimo interesse, obblighi di legge o esecuzione di rapporti con gli interessati, a seconda del caso.</p>

<h2>7. Destinatari e fornitori</h2>
<ul>
<li><strong>Hosting:</strong> VPS Aruba Cloud (Italia) — sito pubblico e sistemi interni sullo stesso server.</li>
<li><strong>Posta elettronica istituzionale:</strong> Google Workspace for Nonprofits (Google Ireland / Google LLC), usata per inviare e ricevere i messaggi dell’associazione (contatti, volontariato, comunicazioni interne).</li>
<li><strong>Pagamenti:</strong> Stripe, secondo i termini Stripe. I dati della carta non transitano sui server di Safe House.</li>
<li><strong>Protezione moduli:</strong> Cloudflare Turnstile (Cloudflare, Inc. / Cloudflare Ireland), sui moduli di contatto e volontariato.</li>
<li><strong>Misurazione pubblico:</strong> Google Ireland / Google LLC per Google Analytics 4 e Google Tag Manager, solo dopo il consenso analitico.</li>
<li><strong>Google Calendar e Drive:</strong> quando uno staff collega il proprio account (punto 5).</li>
</ul>

<h2>8. Trasferimenti extra-UE</h2>
<p>Il server del sito è in Italia. Google (posta, misurazione, API staff), Stripe e Cloudflare possono comportare trattamenti anche fuori dall’Italia o dall’UE. Per i servizi Google, Google pubblica l’adesione al Data Privacy Framework UE–USA / decisione di adeguatezza; questa pagina non è un contratto di trattamento. Maggiori dettagli nelle informative di tali fornitori.</p>

<h2>9. Conservazione</h2>
<ul>
<li>Log tecnici e di sicurezza: per il tempo necessario alla sicurezza e agli obblighi di legge.</li>
<li>Messaggi dai moduli: per gestire la richiesta e un follow-up ragionevole, e nei fascicoli interni se la pratica resta aperta.</li>
<li>Candidature volontario: per il tempo di valutazione e ricontatto.</li>
<li>Dati di donazione e contabili: secondo obblighi civilistici e fiscali.</li>
<li>Log del consenso cookie (hash): per dimostrare il consenso, di regola fino a 12–24 mesi o fino al rinnovo della scelta.</li>
<li>Token Google dello staff: finché l’account esterno resta collegato.</li>
</ul>

<h2>10. Diritti degli interessati</h2>
<p>Puoi esercitare i diritti di accesso, rettifica, cancellazione, limitazione, portabilità, opposizione e revoca del consenso (ove applicabile) scrivendo a <a href="mailto:info@safehouse.community">info@safehouse.community</a>. Hai diritto di reclamare al <a href="https://www.garanteprivacy.it/" rel="noopener noreferrer" target="_blank">Garante per la protezione dei dati personali</a>.</p>

<h2>11. Aggiornamenti</h2>
<p>Possiamo aggiornare questa informativa per riflettere cambiamenti tecnici o organizzativi. La data in intestazione e il campo «Aggiornato» della pagina indicano l’ultima revisione pubblicata.</p>
HTML;
    }

    private static function privacyEn(): string
    {
        return <<<'HTML'
<p>Last updated: <strong>21 September 2026</strong>. Controller: <strong>Safe House ETS</strong> (Italian fiscal code / Codice Fiscale <strong>96629270586</strong>).</p>

<h2>1. Controller</h2>
<p>The controller is <strong>Safe House ETS</strong>, a Third Sector entity, fiscal code <strong>96629270586</strong>.</p>
<ul>
<li><strong>Public website:</strong> <a href="https://safehouse.community">https://safehouse.community</a></li>
<li><strong>Privacy contact:</strong> <a href="mailto:info@safehouse.community">info@safehouse.community</a></li>
<li><strong>Registered office:</strong> Via Delleani 26, 00042 Anzio (RM)</li>
<li><strong>RUNTS registration:</strong> Rep. n. 156768</li>
<li><strong>Operational office:</strong> Turin (Piedmont)</li>
</ul>
<p>No DPO (Data Protection Officer) is appointed at the time of this notice; if one is appointed, the details will be updated here.</p>

<h2>2. Scope</h2>
<p>This notice covers:</p>
<ul>
<li>visitors to <strong>safehouse.community</strong> (browsing, forms, donations, cookies);</li>
<li>processing in <strong>internal management systems</strong> used by authorised staff (association accounts and desk case files);</li>
<li><strong>Google Calendar</strong> and <strong>Google Drive</strong> integrations in the staff area.</li>
</ul>
<p>The public site and the internal systems are hosted on the <strong>same Aruba Cloud virtual server (VPS) in Italy</strong>.</p>
<p>We value confidentiality. Data you give us is used to reply, to run the association and — only if you accept analytics cookies — to understand in aggregate how the site is used. <strong>We do not send names, phone numbers or email addresses to third parties for advertising or commercial profiling.</strong></p>

<h2>3. Data and purposes (public website)</h2>
<h3>3.1 Browsing and security</h3>
<p>Technical connection data (IP address, user-agent, security logs) to serve the site, prevent abuse and keep it secure. Legal basis: legitimate interest (Art. 6.1.f GDPR) and, where applicable, legal obligation. For audience measurement (section 3.7) Safe House <strong>does not store the visitor’s raw IP address</strong>.</p>
<h3>3.2 Contact forms (general, legal desk, digital desk)</h3>
<p>Name, email, message, chosen desk and consent. Purpose: to read and handle the request. We store the message, email it to the desk inbox and send a copy to the sender. The file may be linked to internal desk case records. Basis: pre-contractual steps / legitimate interest and, on the form, consent. We keep only a hash of IP and user-agent on the submission, not the raw address.</p>
<h3>3.3 Volunteer application</h3>
<p>First name, last name, email, phone, message and consent. Purpose: to assess the application and get back to you. The content is emailed to the staff volunteer inbox and a confirmation is sent to the applicant. Basis: consent and pre-contractual steps.</p>
<h3>3.4 Online donations (Stripe)</h3>
<p>Name, type (individual or organisation), email and/or phone, optional comment, amount, campaign, and whether the gift is one-off or recurring. <strong>Card data does not pass through our servers</strong>: payment is handled by <strong>Stripe</strong>. For association accounts we record donation metadata in internal systems (including fees and net amount when available) and the contact details needed for receipts and books. Basis: the donation relationship and accounting / legal duties.</p>
<h3>3.5 5 per mille and information pages</h3>
<p>Publishing the fiscal code does not collect extra data beyond ordinary browsing.</p>
<h3>3.6 Cookie choice</h3>
<p>We store the choice (essential only or analytics) and an audit log (hashed IP and user-agent, date). Basis: the duty to demonstrate consent.</p>
<h3>3.7 Audience measurement (Google Analytics 4 via Google Tag Manager)</h3>
<p>If you choose <em>Accept all</em> or save preferences with analytics cookies on, and if staff have the tool enabled, we load <strong>Google Analytics 4</strong> through <strong>Google Tag Manager</strong>. It provides aggregated statistics (visits, pages, source, device, approximate geography, engagement) and records that a journey was completed (donation, volunteer application, contact or desk message). We do not send this tool names, emails, phone numbers, form text or amounts. We do not use it for advertising or remarketing. Basis: consent. Recipients: Google Ireland / Google LLC. Google states that it uses the IP for coarse geolocation and then discards it; Safe House does not keep the raw IP for this measurement. This section is distinct from staff Calendar/Drive APIs (section 5).</p>
<h3>3.8 Form protection (Cloudflare Turnstile)</h3>
<p>On contact and volunteer forms we use <strong>Cloudflare Turnstile</strong> to tell people from automated systems. It is a security tool, not advertising. The widget token and the request IP may be sent to Cloudflare for that check.</p>

<h2>4. Cookies</h2>
<p>The public site uses necessary cookies (session, security, consent preference). Google Analytics 4, via Google Tag Manager, loads only after <em>Accept all</em> or <em>Save preferences</em> with analytics selected. Marketing and advertising cookies <strong>are not in use</strong>. Details: <a href="/en/cookie-policy">Cookie policy</a>.</p>

<h2 id="google-api-services">5. Google API Services — staff area (Calendar &amp; Drive)</h2>
<p>This section also supports Google Cloud OAuth verification / Google API Services User Data Policy (Limited Use).</p>
<p><strong>Application:</strong> Safe House staff area — Google Calendar / Drive for staff.</p>
<p><strong>Users:</strong> authorised Safe House ETS staff only (not the public site audience).</p>
<p><strong>Google data the app may access</strong> (OAuth scopes in use):</p>
<ul>
<li>basic identity of the linked Google account (<code>openid</code>, <code>email</code>, <code>profile</code>);</li>
<li><strong>Google Calendar</strong> — read/write events and calendars to sync appointments and relevant dates;</li>
<li><strong>Google Drive</strong> with limited scope <code>drive.file</code> — only files created or opened by the app (not the user’s entire Drive).</li>
</ul>
<p><strong>How we use Google data:</strong> only to provide the staff integration. OAuth tokens are stored on the server and are not exposed to the browser as secrets.</p>
<p><strong>What we do not do:</strong> we do not sell Google data; we do not use it for advertising; we do not transfer it to unrelated third parties; we do not use Google data to train generalised AI models.</p>
<p>Safe House ETS undertakes to comply with the <a href="https://developers.google.com/terms/api-services-user-data-policy" rel="noopener noreferrer" target="_blank">Google API Services User Data Policy</a>, including Limited Use.</p>
<p><strong>Disconnect:</strong> staff can disconnect Google from the external-accounts area. Tokens are then invalidated or removed; operational records remain subject to internal retention.</p>

<h2>6. Internal management systems</h2>
<p>Internal systems hold association records (contacts, members, volunteers, donations and reporting, desk cases) for Safe House ETS institutional purposes. Recipients: authenticated users with roles and access controls. Basis: legitimate interest, legal duty or performance of a relationship, as the case requires.</p>

<h2>7. Recipients and providers</h2>
<ul>
<li><strong>Hosting:</strong> Aruba Cloud VPS (Italy) — public site and internal systems on the same server.</li>
<li><strong>Institutional email:</strong> Google Workspace for Nonprofits (Google Ireland / Google LLC), used to send and receive association mail (contact, volunteering, internal mail).</li>
<li><strong>Payments:</strong> Stripe, under Stripe’s terms. Card data does not pass through Safe House servers.</li>
<li><strong>Form protection:</strong> Cloudflare Turnstile (Cloudflare, Inc. / Cloudflare Ireland) on contact and volunteer forms.</li>
<li><strong>Audience measurement:</strong> Google Ireland / Google LLC for Google Analytics 4 and Google Tag Manager, only after analytics consent.</li>
<li><strong>Google Calendar and Drive:</strong> when a staff member links their account (section 5).</li>
</ul>

<h2>8. Transfers outside the EU</h2>
<p>The site server is in Italy. Google (mail, measurement, staff APIs), Stripe and Cloudflare may also process data outside Italy or the EU. For Google services, Google publishes participation in the EU–US Data Privacy Framework / adequacy decision; this page is not a processing contract. See those providers’ own notices for more detail.</p>

<h2>9. Retention</h2>
<ul>
<li>Technical and security logs: as needed for security and legal duties.</li>
<li>Form messages: for as long as needed to handle the request and a reasonable follow-up, and in internal files if the case stays open.</li>
<li>Volunteer applications: for assessment and follow-up.</li>
<li>Donation and accounting data: according to civil and tax duties.</li>
<li>Cookie-consent audit (hashes): to demonstrate consent, typically up to 12–24 months or until the choice is renewed.</li>
<li>Staff Google tokens: while the external account stays linked.</li>
</ul>

<h2>10. Your rights</h2>
<p>You may request access, rectification, erasure, restriction, portability, objection and withdrawal of consent (where applicable) by writing to <a href="mailto:info@safehouse.community">info@safehouse.community</a>. You may lodge a complaint with the <a href="https://www.garanteprivacy.it/" rel="noopener noreferrer" target="_blank">Italian Data Protection Authority (Garante)</a>.</p>

<h2>11. Updates</h2>
<p>We may update this notice to reflect technical or organisational changes. The date at the top and the page «Updated» field show the latest published revision.</p>
HTML;
    }

    private static function privacyRu(): string
    {
        return <<<'HTML'
<p>Последнее обновление: <strong>21 сентября 2026</strong>. Контролёр: <strong>Safe House ETS</strong> (Codice Fiscale <strong>96629270586</strong>). Публичные версии — итальянская и английская; отдельного публичного сайта на русском нет.</p>
<p>Хостинг: VPS Aruba Cloud (Италия). Почта: Google Workspace for Nonprofits. Платежи: Stripe. Защита форм: Cloudflare Turnstile. Измерение аудитории: Google Analytics 4 через Google Tag Manager только после согласия. Имя, телефон и email не передаются рекламным третьим лицам.</p>
HTML;
    }

    private static function cookieIt(): string
    {
        return <<<'HTML'
<p>Ultimo aggiornamento: <strong>21 settembre 2026</strong>. Complemento della <a href="/it/privacy-policy">Privacy policy</a> di Safe House ETS. Titolare: <strong>Safe House ETS</strong> (Codice Fiscale <strong>96629270586</strong>). Sede legale: Via Delleani 26, 00042 Anzio (RM). Iscrizione RUNTS: Rep. n. 156768.</p>

<h2>1. Cosa sono i cookie</h2>
<p>I cookie sono piccoli file memorizzati sul dispositivo. Alcuni sono necessari al funzionamento del sito. Altri, i cookie analitici, partono solo se li accetti.</p>

<h2>2. Come gestiamo il consenso</h2>
<p>Al primo accesso mostriamo un riquadro con:</p>
<ul>
<li><em>Accetta tutti</em> — cookie necessari e analitici;</li>
<li><em>Solo necessari</em> — il sito resta usabile, senza misurazione;</li>
<li><em>Preferenze</em> — puoi vedere le categorie. I cookie analitici sono proposti già selezionati: puoi togliere la spunta e poi salvare;</li>
<li>la X — equivale a <em>solo necessari</em>.</li>
</ul>
<p><strong>Fino a <em>Accetta tutti</em> o <em>Salva preferenze</em> gli strumenti di misurazione non vengono caricati.</strong> Lo scorrimento della pagina non registra una scelta.</p>
<p>La scelta è salvata (cookie e memoria locale <code>sh_cookie_consent</code>) e annotata in un registro di audit (hash di IP e user-agent). Resta valida finché non la cambi o non cancelli i cookie del sito.</p>
<p>Puoi riaprire lo stesso pannello dal pulsante in questa pagina e dal link «Preferenze cookie» nel footer.</p>
<p>I cookie necessari partono automaticamente. Google Analytics 4, tramite Google Tag Manager, si carica solo dopo il consenso analitico. L’associazione non conserva l’indirizzo IP grezzo del visitatore per questa misurazione.</p>

<h2>3. Cookie usati sul sito pubblico</h2>
<table>
<thead>
<tr><th>Categoria</th><th>Nome</th><th>Finalità</th><th>Durata</th><th>Provider</th></tr>
</thead>
<tbody>
<tr><td>Necessari</td><td><code>XSRF-TOKEN</code></td><td>Protezione CSRF</td><td>Sessione (~2 ore)</td><td>Safe House (sito)</td></tr>
<tr><td>Necessari</td><td><code>safe-house-community-session</code></td><td>Sessione applicativa</td><td>Sessione (~2 ore)</td><td>Safe House (sito)</td></tr>
<tr><td>Necessari</td><td><code>sh_cookie_consent</code></td><td>Memorizzare la preferenza cookie</td><td>Fino a 1 anno</td><td>Safe House (sito)</td></tr>
<tr><td>Necessari (pagamenti)</td><td>cookie Stripe (es. <code>__stripe_*</code>) quando usi il form donazione</td><td>Elaborazione del pagamento in sicurezza</td><td>Secondo Stripe</td><td>Stripe</td></tr>
<tr><td>Necessari (sicurezza)</td><td>Cloudflare Turnstile sui moduli contatto, volontariato e domanda socio</td><td>Verificare che chi invia il modulo sia una persona</td><td>Durante la verifica</td><td>Cloudflare</td></tr>
<tr><td>Analitici</td><td><code>_ga</code>, <code>_ga_*</code> (Google Analytics 4 tramite Google Tag Manager)</td><td>Statistiche aggregate e segnali di percorso completato; caricati solo dopo il consenso analitico; non usati per pubblicità o remarketing</td><td>Fino a 2 anni (secondo Google)</td><td>Google</td></tr>
<tr><td>Marketing</td><td>—</td><td>Non in uso</td><td>—</td><td>—</td></tr>
</tbody>
</table>

<h2>4. Come modificare le preferenze</h2>
<p>Usa il pulsante in questa pagina, il link nel footer, oppure cancella i cookie del sito dal browser. Per domande: <a href="mailto:info@safehouse.community">info@safehouse.community</a>.</p>
HTML;
    }

    private static function cookieEn(): string
    {
        return <<<'HTML'
<p>Last updated: <strong>21 September 2026</strong>. Companion to the Safe House ETS <a href="/en/privacy-policy">Privacy policy</a>. Controller: <strong>Safe House ETS</strong> (fiscal code <strong>96629270586</strong>). Registered office: Via Delleani 26, 00042 Anzio (RM). RUNTS: Rep. n. 156768.</p>

<h2>1. What cookies are</h2>
<p>Cookies are small files stored on your device. Some are necessary for the site to work. Analytics cookies start only if you accept them.</p>

<h2>2. How we manage consent</h2>
<p>On first visit we show a panel with:</p>
<ul>
<li><em>Accept all</em> — necessary and analytics cookies;</li>
<li><em>Essential only</em> — the site stays usable, without measurement;</li>
<li><em>Preferences</em> — you can see the categories. Analytics cookies are proposed already selected: you can uncheck them and then save;</li>
<li>the X — same as <em>essential only</em>.</li>
</ul>
<p><strong>Until you choose <em>Accept all</em> or <em>Save preferences</em>, measurement tools are not loaded.</strong> Scrolling does not store a choice.</p>
<p>Your choice is stored (<code>sh_cookie_consent</code> cookie and local storage) and logged for audit (hashed IP and user-agent). It stays until you change it or clear this site’s cookies.</p>
<p>You can reopen the same panel from the button on this page and from the «Cookie preferences» link in the footer.</p>
<p>Necessary cookies run automatically. Google Analytics 4, via Google Tag Manager, loads only after analytics consent. The association does not store the visitor’s raw IP for this measurement.</p>

<h2>3. Cookies on the public website</h2>
<table>
<thead>
<tr><th>Category</th><th>Name</th><th>Purpose</th><th>Duration</th><th>Provider</th></tr>
</thead>
<tbody>
<tr><td>Essential</td><td><code>XSRF-TOKEN</code></td><td>CSRF protection</td><td>Session (~2 hours)</td><td>Safe House (site)</td></tr>
<tr><td>Essential</td><td><code>safe-house-community-session</code></td><td>Application session</td><td>Session (~2 hours)</td><td>Safe House (site)</td></tr>
<tr><td>Essential</td><td><code>sh_cookie_consent</code></td><td>Store cookie preference</td><td>Up to 1 year</td><td>Safe House (site)</td></tr>
<tr><td>Essential (payments)</td><td>Stripe cookies (e.g. <code>__stripe_*</code>) when using donation checkout</td><td>Secure payment processing</td><td>Per Stripe</td><td>Stripe</td></tr>
<tr><td>Essential (security)</td><td>Cloudflare Turnstile on contact, volunteer and membership forms</td><td>Check that a person is submitting the form</td><td>During verification</td><td>Cloudflare</td></tr>
<tr><td>Analytics</td><td><code>_ga</code>, <code>_ga_*</code> (Google Analytics 4 via Google Tag Manager)</td><td>Aggregated statistics and completed-journey signals; load only after analytics consent; not used for advertising or remarketing</td><td>Up to 2 years (per Google)</td><td>Google</td></tr>
<tr><td>Marketing</td><td>—</td><td>Not in use</td><td>—</td><td>—</td></tr>
</tbody>
</table>

<h2>4. Changing preferences</h2>
<p>Use the button on this page, the footer link, or clear this site’s cookies in your browser. Questions: <a href="mailto:info@safehouse.community">info@safehouse.community</a>.</p>
HTML;
    }

    private static function cookieRu(): string
    {
        return <<<'HTML'
<p>Последнее обновление: <strong>21 сентября 2026</strong>. Публичные версии — итальянская и английская.</p>
<p>Необходимые cookie работают сразу. Аналитика (Google Analytics 4 через Google Tag Manager) загружается только после «Принять все» или сохранения предпочтений. Рекламные cookie не используются. Защита форм: Cloudflare Turnstile.</p>
HTML;
    }
}

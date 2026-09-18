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
<p><strong>Versione operativa:</strong> 17 settembre 2026. Titolare: <strong>Safe House ETS</strong> (Codice Fiscale <strong>96629270586</strong>). Questo testo è la policy in vigore per il sito pubblico e i trattamenti collegati all’attività associativa.</p>

<h2>1. Titolare del trattamento</h2>
<p>Il titolare del trattamento è <strong>Safe House ETS</strong>, ente del Terzo settore, Codice Fiscale <strong>96629270586</strong>.</p>
<ul>
<li><strong>Sito pubblico:</strong> <a href="https://safehouse.community">https://safehouse.community</a></li>
<li><strong>Contatti privacy / generali:</strong> <a href="mailto:info@safehouse.community">info@safehouse.community</a></li>
<li><strong>Sede legale:</strong> Italia</li>
</ul>
<p>Non risulta nominato un DPO (Data Protection Officer) al momento della pubblicazione; in caso di nomina, i riferimenti saranno aggiornati qui.</p>

<h2>2. Ambito di questa informativa</h2>
<p>Questa informativa riguarda:</p>
<ul>
<li>i visitatori e gli utenti del sito <strong>safehouse.community</strong> (form, donazioni, cookie);</li>
<li>i trattamenti nei <strong>sistemi gestionali interni</strong> usati dallo staff autorizzato;</li>
<li>le integrazioni <strong>Google Calendar</strong> e <strong>Google Drive</strong> collegate all’area riservata dello staff (OAuth per account Google dello staff).</li>
</ul>
<p>Sito e sistemi interni sono ospitati sullo <strong>stesso server VPS</strong> (Aruba Cloud, Italia).</p>

<h2>3. Dati trattati e finalità (sito pubblico)</h2>
<h3>3.1 Navigazione</h3>
<p>Dati tecnici di connessione (indirizzo IP, user-agent, log di sicurezza) per erogare il sito, prevenire abusi e garantire la sicurezza. Base giuridica: legittimo interesse (art. 6.1.f GDPR) e, ove applicabile, obbligo legale.</p>
<h3>3.2 Form di contatto e volontariato</h3>
<p>Nome, email, telefono (se indicato), messaggio e preferenze relative alla richiesta. Finalità: rispondere e gestire la richiesta. Base: esecuzione di misure precontrattuali / legittimo interesse; dove richiesto, consenso esplicito sul form.</p>
<h3>3.3 Donazioni online (Stripe)</h3>
<p>Dati del donatore (es. nome, email, importo, metadati della donazione). I dati della carta non transitano sui nostri server: il pagamento è gestito da <strong>Stripe</strong>. Possiamo registrare nei sistemi di contabilità interni metadati della donazione per trasparenza associativa. Base: esecuzione del rapporto di donazione / obblighi contabili e di legge.</p>
<h3>3.4 5 per mille e contenuti informativi</h3>
<p>La pubblicazione del Codice Fiscale non comporta raccolta di dati aggiuntivi oltre alla normale navigazione.</p>
<h3>3.5 Consenso cookie</h3>
<p>Registriamo preferenze cookie e un log di audit (hash di IP e user-agent, categorie accettate, data). Base: obbligo di dimostrare il consenso (ePrivacy / GDPR).</p>
<h3>3.6 Misurazione del pubblico (Google Analytics 4 tramite Google Tag Manager)</h3>
<p>Se accetti i cookie analitici, carichiamo <strong>Google Analytics 4</strong> tramite <strong>Google Tag Manager</strong> per statistiche aggregate sul pubblico e per eventi di conversione (<code>donate_success</code>, <code>donate_recurring_success</code>, <code>volunteer_success</code>, <code>contact_success</code>). Non usiamo questo strumento per pubblicità o remarketing in questa versione. Base giuridica operativa: consenso per questo strumento non tecnico (non interesse legittimo per i tracker). Destinatari: Google Ireland / Google LLC. Trasferimento: Google pubblica l’adesione al Data Privacy Framework UE–USA / decisione di adeguatezza; questa pagina non è un contratto di trattamento. Google indica di usare l’IP per una geolocalizzazione approssimativa e poi di scartarlo; Safe House <strong>non conserva l’indirizzo IP grezzo del visitatore</strong> per questa misurazione. Questa sezione è distinta dalle API Calendar/Drive dello staff (punto 5).</p>

<h2>4. Cookie</h2>
<p>Sul sito pubblico usiamo cookie necessari (sessione, sicurezza CSRF, preferenza di consenso). Google Analytics 4, tramite Google Tag Manager, si carica solo dopo il consenso analitico. I cookie di marketing/ads <strong>non sono in uso</strong>. Dettagli: <a href="/it/cookie-policy">Cookie policy</a>.</p>

<h2 id="google-api-services">5. Google API Services — area riservata staff (Calendar &amp; Drive)</h2>
<p>Questa sezione è destinata anche alla verifica OAuth di Google Cloud / Google API Services User Data Policy (Limited Use).</p>
<p><strong>Applicazione:</strong> area riservata Safe House — integrazione Google Calendar / Drive per lo staff.</p>
<p><strong>Utenti:</strong> solo personale autorizzato di Safe House ETS (non il pubblico del sito).</p>
<p><strong>Dati Google a cui l’app può accedere</strong> (scope OAuth tipici in uso):</p>
<ul>
<li>identità di base dell’account Google collegato (<code>openid</code>, <code>email</code>, <code>profile</code>);</li>
<li><strong>Google Calendar</strong> — lettura/scrittura eventi e calendari per sincronizzare appuntamenti, attività e date rilevanti;</li>
<li><strong>Google Drive</strong> con scope limitato <code>drive.file</code> — accesso solo ai file creati o aperti dall’app (non all’intero Drive dell’utente).</li>
</ul>
<p><strong>Come usiamo i dati Google:</strong> esclusivamente per fornire le funzioni di integrazione richieste dallo staff. I token OAuth sono conservati lato server e non esposti al browser come segreti.</p>
<p><strong>Cosa non facciamo:</strong> non vendiamo dati Google; non li usiamo per pubblicità; non li trasferiamo a terzi non correlati alla fornitura del servizio; non usiamo i dati Google per addestrare modelli di AI generalizzati.</p>
<p>Safe House ETS si impegna a rispettare la <a href="https://developers.google.com/terms/api-services-user-data-policy" rel="noopener noreferrer" target="_blank">Google API Services User Data Policy</a>, inclusa la Limited Use.</p>
<p><strong>Disconnessione e cancellazione:</strong> lo staff può disconnettere Google dall’area account esterni. Alla disconnessione i token vengono invalidati/rimossi; i record operativi restano soggetti alle retention interne.</p>

<h2>6. Sistemi gestionali interni — trattamenti dello staff</h2>
<p>I sistemi interni gestiscono anagrafiche e attività associative (contatti, membri, volontari, donazioni/rendicontazione, casi sportello, ecc.) per finalità istituzionali di Safe House ETS. Destinatari: solo utenti autenticati con ruoli e controlli di accesso. Base: legittimo interesse / obblighi di legge / esecuzione di rapporti con interessati, a seconda del caso.</p>

<h2>7. Responsabili del trattamento e fornitori</h2>
<ul>
<li><strong>Hosting:</strong> VPS Aruba Cloud (Italia) — sito e sistemi interni sullo stesso server.</li>
<li><strong>Posta elettronica:</strong> servizi email Aruba collegati a VPS/dominio.</li>
<li><strong>Pagamenti:</strong> Stripe (trattamento come autonomo titolare/responsabile secondo i termini Stripe).</li>
<li><strong>Google:</strong> Google Ireland / Google LLC per API Calendar e Drive quando lo staff collega l’account, e per Google Analytics 4 / Google Tag Manager sul sito pubblico solo dopo consenso analitico.</li>
</ul>

<h2>8. Trasferimenti extra-UE</h2>
<p>Hosting e posta sono in Italia/UE ove possibile. Stripe e Google possono comportare trasferimenti internazionali. Per Analytics, Google indica l’adesione al Data Privacy Framework UE–USA / decisione di adeguatezza come nota operativa già pubblicata da Google; questa informativa non riproduce un accordo contrattuale. Maggiori dettagli nelle privacy policy di tali fornitori.</p>

<h2>9. Conservazione</h2>
<ul>
<li>Log tecnici / sicurezza: secondo necessità di sicurezza e obblighi di legge (di regola mesi, salvo incidenti).</li>
<li>Messaggi da form: per il tempo necessario a gestire la richiesta e follow-up ragionevole.</li>
<li>Dati donazioni / contabili: secondo obblighi civilistici e fiscali.</li>
<li>Log consenso cookie (hash): per dimostrare il consenso (tipicamente fino a 12–24 mesi o fino a rinnovo policy).</li>
<li>Token Google: finché l’account esterno resta collegato.</li>
</ul>

<h2>10. Diritti degli interessati</h2>
<p>Puoi esercitare i diritti di accesso, rettifica, cancellazione, limitazione, portabilità, opposizione e revoca del consenso (ove applicabile) scrivendo a <a href="mailto:info@safehouse.community">info@safehouse.community</a>. Hai diritto di reclamare al <a href="https://www.garanteprivacy.it/" rel="noopener noreferrer" target="_blank">Garante per la protezione dei dati personali</a>.</p>

<h2>11. Aggiornamenti</h2>
<p>Possiamo aggiornare questa informativa per riflettere cambiamenti tecnici o legali. La data in intestazione e il campo “Aggiornato” della pagina indicano l’ultima revisione pubblicata.</p>
HTML;
    }

    private static function privacyEn(): string
    {
        return <<<'HTML'
<p><strong>Operational version:</strong> 17 September 2026. Controller: <strong>Safe House ETS</strong> (Italian fiscal code / Codice Fiscale <strong>96629270586</strong>). This is the live policy for the public website and related association processing.</p>

<h2>1. Data controller</h2>
<p>The controller is <strong>Safe House ETS</strong>, a Third Sector entity (ETS), Codice Fiscale <strong>96629270586</strong>.</p>
<ul>
<li><strong>Public website:</strong> <a href="https://safehouse.community">https://safehouse.community</a></li>
<li><strong>Privacy / general contact:</strong> <a href="mailto:info@safehouse.community">info@safehouse.community</a></li>
<li><strong>Registered address:</strong> Italy</li>
</ul>
<p>No Data Protection Officer (DPO) is appointed at the time of publication; if appointed, contact details will be added here.</p>

<h2>2. Scope</h2>
<p>This notice covers:</p>
<ul>
<li>visitors and users of <strong>safehouse.community</strong> (forms, donations, cookies);</li>
<li>processing in <strong>internal management systems</strong> used by authorised staff;</li>
<li><strong>Google Calendar</strong> and <strong>Google Drive</strong> integrations connected to the staff area (OAuth for staff Google accounts).</li>
</ul>
<p>The website and internal systems run on the <strong>same VPS</strong> (Aruba Cloud, Italy).</p>

<h2>3. Data we process and purposes (public website)</h2>
<h3>3.1 Browsing</h3>
<p>Technical connection data (IP address, user-agent, security logs) to deliver the site, prevent abuse and ensure security. Legal basis: legitimate interest (GDPR Art. 6(1)(f)) and legal obligations where applicable.</p>
<h3>3.2 Contact and volunteer forms</h3>
<p>Name, email, phone (if provided), message and related preferences. Purpose: respond to and handle the request. Basis: pre-contractual steps / legitimate interest; explicit consent on the form where required.</p>
<h3>3.3 Online donations (Stripe)</h3>
<p>Donor data (e.g. name, email, amount, donation metadata). Card data does not pass through our servers — payments are handled by <strong>Stripe</strong>. We may store donation metadata in our internal accounting systems for association transparency. Basis: donation relationship / legal accounting duties.</p>
<h3>3.4 “5 per mille” information</h3>
<p>Publishing our fiscal code does not collect additional personal data beyond normal browsing.</p>
<h3>3.5 Cookie consent</h3>
<p>We store cookie preferences and an audit log (hashed IP and user-agent, accepted categories, timestamp). Basis: demonstrating consent (ePrivacy / GDPR).</p>
<h3>3.6 Audience measurement (Google Analytics 4 via Google Tag Manager)</h3>
<p>If you accept analytics cookies, we load <strong>Google Analytics 4</strong> via <strong>Google Tag Manager</strong> for aggregated audience statistics and conversion events (<code>donate_success</code>, <code>donate_recurring_success</code>, <code>volunteer_success</code>, <code>contact_success</code>). We do not use this tool for advertising or remarketing in this version. Operational legal basis: consent for this non-technical tool (not legitimate interest for trackers). Recipients: Google Ireland / Google LLC. Transfer: Google publishes participation in the EU–US Data Privacy Framework / adequacy decision; this page is not a processing contract. Google states that IP is used for coarse geolocation and then discarded; Safe House still <strong>does not keep the visitor’s raw IP</strong> for this measurement. This section is distinct from staff Calendar/Drive APIs (section 5).</p>

<h2>4. Cookies</h2>
<p>On the public site we use essential cookies (session, CSRF security, consent preference). Google Analytics 4, via Google Tag Manager, loads only after analytics consent. Marketing/ads cookies are <strong>not in use</strong>. Details: <a href="/en/cookie-policy">Cookie policy</a>.</p>

<h2 id="google-api-services">5. Google API Services — staff area (Calendar &amp; Drive)</h2>
<p>This section is also intended for Google Cloud OAuth verification and the Google API Services User Data Policy (Limited Use).</p>
<p><strong>Application:</strong> Safe House staff area — Google Calendar / Drive integration for staff.</p>
<p><strong>Users:</strong> authorised Safe House ETS staff only (not the general public).</p>
<p><strong>Google user data the app may access</strong> (OAuth scopes in use):</p>
<ul>
<li>basic Google account identity (<code>openid</code>, <code>email</code>, <code>profile</code>);</li>
<li><strong>Google Calendar</strong> — read/write calendars and events to sync meetings, tasks and relevant dates;</li>
<li><strong>Google Drive</strong> with limited scope <code>drive.file</code> — only files created or opened by the app (not the user’s entire Drive).</li>
</ul>
<p><strong>How we use Google data:</strong> solely to provide staff integration features. OAuth tokens are stored server-side and are not exposed to the browser as secrets.</p>
<p><strong>What we do not do:</strong> we do not sell Google data; we do not use it for advertising; we do not transfer it to unrelated third parties; we do not use Google user data to train generalised AI/ML models.</p>
<p>Safe House ETS complies with the <a href="https://developers.google.com/terms/api-services-user-data-policy" rel="noopener noreferrer" target="_blank">Google API Services User Data Policy</a>, including Limited Use.</p>
<p><strong>Disconnect and deletion:</strong> staff can disconnect Google under external accounts. On disconnect, tokens are invalidated/removed; operational records remain subject to internal retention rules.</p>

<h2>6. Internal management systems — staff processing</h2>
<p>Internal systems hold association records and activities (contacts, members, volunteers, donations/reporting, desk cases, etc.) for Safe House ETS institutional purposes. Recipients: authenticated users under role and access controls. Bases: legitimate interest / legal duties / performance of relationships with data subjects, as applicable.</p>

<h2>7. Processors and providers</h2>
<ul>
<li><strong>Hosting:</strong> Aruba Cloud VPS (Italy) — website and internal systems on the same server.</li>
<li><strong>Email:</strong> Aruba email services linked to the VPS/domain.</li>
<li><strong>Payments:</strong> Stripe (controller/processor roles per Stripe’s terms).</li>
<li><strong>Google:</strong> Google Ireland / Google LLC for Calendar and Drive APIs when staff connect their account, and for Google Analytics 4 / Google Tag Manager on the public site only after analytics consent.</li>
</ul>

<h2>8. International transfers</h2>
<p>Hosting and email are in Italy/EU where possible. Stripe may involve international transfers under Stripe’s terms. For Analytics, Google publishes participation in the EU–US Data Privacy Framework / adequacy decision as an operational note; this notice does not reproduce a contractual agreement. See those providers’ privacy notices for details.</p>

<h2>9. Retention</h2>
<ul>
<li>Technical / security logs: as needed for security and law (typically months, longer if investigating an incident).</li>
<li>Form messages: as needed to handle the request and reasonable follow-up.</li>
<li>Donation / accounting data: per civil and tax retention rules.</li>
<li>Cookie consent audit (hashes): to demonstrate consent (typically 12–24 months or until policy renewal).</li>
<li>Google tokens: while the external account remains connected.</li>
</ul>

<h2>10. Your rights</h2>
<p>You may exercise rights of access, rectification, erasure, restriction, portability, objection and withdrawal of consent (where applicable) by emailing <a href="mailto:info@safehouse.community">info@safehouse.community</a>. You may lodge a complaint with the Italian Data Protection Authority (<a href="https://www.garanteprivacy.it/" rel="noopener noreferrer" target="_blank">Garante</a>) or your local supervisory authority in the EEA.</p>

<h2>11. Updates</h2>
<p>We may update this notice for technical or legal changes. The date above and the page “Updated” field show the published revision.</p>
HTML;
    }

    private static function privacyRu(): string
    {
        return <<<'HTML'
<p><strong>Рабочая версия:</strong> 11 августа 2026. Контролёр: <strong>Safe House ETS</strong> (код fiscale / Codice Fiscale <strong>96629270586</strong>). Это действующая политика для публичного сайта и связанных обработок ассоциации.</p>

<h2>1. Контролёр данных</h2>
<p>Контролёр — <strong>Safe House ETS</strong>, организация Terzo Settore (ETS), Codice Fiscale <strong>96629270586</strong>.</p>
<ul>
<li><strong>Публичный сайт:</strong> <a href="https://safehouse.community">https://safehouse.community</a></li>
<li><strong>Контакт по privacy:</strong> <a href="mailto:info@safehouse.community">info@safehouse.community</a></li>
<li><strong>Юридический адрес:</strong> Италия</li>
</ul>

<h2>2. Область действия</h2>
<ul>
<li>посетители сайта <strong>safehouse.community</strong> (формы, пожертвования, cookie);</li>
<li>обработка во <strong>внутренних системах учёта</strong> уполномоченным персоналом;</li>
<li>интеграции <strong>Google Calendar</strong> и <strong>Google Drive</strong> для зоны сотрудников (OAuth аккаунтов сотрудников).</li>
</ul>
<p>Сайт и внутренние системы размещены на <strong>одном VPS</strong> (Aruba Cloud, Италия).</p>

<h2>3. Данные и цели (публичный сайт)</h2>
<p>Технические данные навигации; данные форм контакта/волонтёрства; метаданные пожертвований через <strong>Stripe</strong> (данные карты на наших серверах не хранятся); журнал согласия на cookie (хеш IP/UA). Подробнее на итальянской/английской версии политики.</p>

<h2>4. Cookie</h2>
<p>На сайте — необходимые cookie (сессия, CSRF, предпочтение согласия). Аналитика/маркетинг <strong>сейчас не активны</strong>. См. <a href="/ru/cookie-policy">Политику cookie</a>.</p>

<h2 id="google-api-services">5. Google API Services — зона сотрудников (Calendar и Drive)</h2>
<p>Раздел для соответствия Google API Services User Data Policy (Limited Use) и OAuth verification.</p>
<p><strong>Приложение:</strong> зона сотрудников Safe House — интеграция Google Calendar / Drive, только для сотрудников Safe House ETS.</p>
<p><strong>Доступ:</strong> базовая идентичность Google (<code>openid</code>, <code>email</code>, <code>profile</code>); <strong>Google Calendar</strong> (чтение/запись событий); <strong>Google Drive</strong> со scope <code>drive.file</code> (только файлы, созданные/открытые приложением).</p>
<p>Данные Google используются только для функций интеграции для сотрудников; токены хранятся на сервере; не продаются и не используются для рекламы. Соблюдаем <a href="https://developers.google.com/terms/api-services-user-data-policy" rel="noopener noreferrer" target="_blank">Google API Services User Data Policy</a> (Limited Use).</p>

<h2>6–11. Поставщики, сроки, права</h2>
<p>Хостинг: Aruba Cloud (IT). Почта: Aruba. Платежи: Stripe. Права субъекта данных и жалобы в Garante: <a href="mailto:info@safehouse.community">info@safehouse.community</a>, <a href="https://www.garanteprivacy.it/" rel="noopener noreferrer" target="_blank">garanteprivacy.it</a>. Полная детализация — в EN/IT версиях.</p>
HTML;
    }

    private static function cookieIt(): string
    {
        return <<<'HTML'
<p><strong>Versione operativa:</strong> 17 settembre 2026. Complemento della <a href="/it/privacy-policy">Privacy policy</a> di Safe House ETS.</p>

<h2>1. Cosa sono i cookie</h2>
<p>I cookie sono piccoli file memorizzati sul dispositivo. Possono essere necessari al funzionamento del sito oppure, previo consenso, usati per finalità analitiche.</p>

<h2>2. Come gestiamo il consenso</h2>
<p>Al primo accesso mostriamo un banner con: <em>Accetta tutti</em>, <em>Solo necessari</em>, <em>Preferenze</em> e chiusura (X) che equivale a <em>solo necessari</em>. La scelta è salvata (cookie/localStorage <code>sh_cookie_consent</code>) e registrata in forma aggregata/audit (hash IP/UA) nella tabella consensi. Lo scorrimento della pagina non registra una scelta. Puoi riaprire lo stesso pannello preferenze dal pulsante nel footer.</p>
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
<tr><td>Necessari (pagamenti)</td><td>cookie Stripe (es. <code>__stripe_*</code>) quando usi il form donazione</td><td>Elaborazione pagamento sicura</td><td>Secondo Stripe</td><td>Stripe</td></tr>
<tr><td>Analitici</td><td><code>_ga</code>, <code>_ga_*</code> (Google Analytics 4 tramite Google Tag Manager)</td><td>Statistiche aggregate ed eventi di conversione; caricati solo dopo il consenso analitico; non usati per remarketing in questa versione</td><td>Secondo Google</td><td>Google</td></tr>
<tr><td>Marketing</td><td>—</td><td>Non in uso al momento</td><td>—</td><td>—</td></tr>
</tbody>
</table>

<h2>4. Come modificare le preferenze</h2>
<p>Riapri le preferenze cookie dal pulsante nel footer, oppure cancella i cookie del sito dal browser. Per domande: <a href="mailto:info@safehouse.community">info@safehouse.community</a>.</p>
HTML;
    }

    private static function cookieEn(): string
    {
        return <<<'HTML'
<p><strong>Operational version:</strong> 17 September 2026. Companion to the Safe House ETS <a href="/en/privacy-policy">Privacy policy</a>.</p>

<h2>1. What cookies are</h2>
<p>Cookies are small files stored on your device. They may be essential for the site to work or, with consent, used for analytics.</p>

<h2>2. How we manage consent</h2>
<p>On first visit we show a banner: <em>Accept all</em>, <em>Essential only</em>, <em>Preferences</em>, and dismiss (X), which equals essential only. Your choice is stored (<code>sh_cookie_consent</code> cookie/localStorage) and logged for audit (hashed IP/UA). Scrolling does not store a choice. You can reopen the same preferences panel from the footer button.</p>
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
<tr><td>Analytics</td><td><code>_ga</code>, <code>_ga_*</code> (Google Analytics 4 via Google Tag Manager)</td><td>Aggregated statistics and conversion events; load only after analytics consent; not used for remarketing in this version</td><td>Per Google</td><td>Google</td></tr>
<tr><td>Marketing</td><td>—</td><td>Not in use</td><td>—</td><td>—</td></tr>
</tbody>
</table>

<h2>4. Changing preferences</h2>
<p>Reopen cookie preferences from the footer button, or clear site cookies in your browser. Questions: <a href="mailto:info@safehouse.community">info@safehouse.community</a>.</p>
HTML;
    }

    private static function cookieRu(): string
    {
        return <<<'HTML'
<p><strong>Рабочая версия:</strong> 11 августа 2026. Дополнение к <a href="/ru/privacy-policy">Политике конфиденциальности</a> Safe House ETS.</p>

<h2>1–2. Cookie и согласие</h2>
<p>На первом визите показывается баннер (принять все / только необходимые / настройки). Выбор сохраняется в <code>sh_cookie_consent</code> и пишется в audit-лог (хеш IP/UA).</p>
<p><strong>Сейчас</strong> аналитические и маркетинговые cookie третьих сторон не используются.</p>

<h2>3. Cookie на публичном сайте</h2>
<table>
<thead>
<tr><th>Категория</th><th>Имя</th><th>Назначение</th><th>Срок</th><th>Провайдер</th></tr>
</thead>
<tbody>
<tr><td>Необходимые</td><td><code>XSRF-TOKEN</code></td><td>CSRF</td><td>Сессия</td><td>Safe House</td></tr>
<tr><td>Необходимые</td><td><code>safe-house-community-session</code></td><td>Сессия</td><td>Сессия</td><td>Safe House</td></tr>
<tr><td>Необходимые</td><td><code>sh_cookie_consent</code></td><td>Предпочтение cookie</td><td>до 1 года</td><td>Safe House</td></tr>
<tr><td>Необходимые (оплата)</td><td>Stripe (<code>__stripe_*</code>)</td><td>Платёжная форма</td><td>по Stripe</td><td>Stripe</td></tr>
<tr><td>Аналитика / маркетинг</td><td>—</td><td>Не используются</td><td>—</td><td>—</td></tr>
</tbody>
</table>

<h2>4. Контакты</h2>
<p><a href="mailto:info@safehouse.community">info@safehouse.community</a></p>
HTML;
    }
}

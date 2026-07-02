<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $existingAbout = Page::query()->where('key', 'about')->first();

        Page::query()->updateOrCreate(
            ['key' => 'about'],
            [
                'template' => 'about',
                'is_published' => true,
                'title' => [
                    'it' => 'Chi siamo',
                    'en' => 'About us',
                    'ru' => 'О нас',
                ],
                'slug' => [
                    'it' => 'chi-siamo',
                    'en' => 'about-us',
                    'ru' => 'o-nas',
                ],
                'body' => [
                    'it' => '<p>Safe House ETS è un\'associazione no profit impegnata nella tutela dei diritti umani e nel sostegno concreto alle persone che vivono situazioni di vulnerabilità sociale, economica e abitativa. Crediamo che la dignità, l\'accesso ai diritti fondamentali e il supporto alle persone non debbano essere privilegi, ma garanzie accessibili a tutti.</p><p>Operiamo ogni giorno per costruire percorsi di inclusione, autonomia e protezione, intervenendo sia nell\'emergenza sia nell\'accompagnamento a lungo termine. Il nostro lavoro unisce assistenza umanitaria, tutela legale, supporto digitale e reinserimento sociale, con un approccio basato sull\'ascolto, sul rispetto e sulla presa in carico integrata della persona.</p>',
                    'en' => '<p>Safe House ETS is a non-profit association committed to protecting human rights and providing concrete support to people experiencing social, economic, and housing vulnerability.</p><p>We work every day to build paths of inclusion, autonomy, and protection — combining humanitarian assistance, legal advocacy, digital support, and social reintegration.</p>',
                    'ru' => '<p>Safe House ETS — некоммерческая ассоциация, которая защищает права человека и оказывает практическую поддержку людям в уязвимом положении.</p><p>Мы строим пути включения, автономии и защиты — от экстренной помощи до долгосрочного сопровождения.</p>',
                ],
                'meta' => $this->aboutMeta(is_array($existingAbout?->meta) ? $existingAbout->meta : null),
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'services'],
            [
                'template' => 'services',
                'is_published' => true,
                'title' => [
                    'it' => 'Servizi',
                    'en' => 'Services',
                    'ru' => 'Услуги',
                ],
                'slug' => [
                    'it' => 'servizi',
                    'en' => 'services',
                    'ru' => 'uslugi',
                ],
                'body' => [
                    'it' => '<p>Interveniamo con servizi integrati per rispondere alle emergenze e accompagnare percorsi di autonomia e dignità.</p>',
                    'en' => '<p>We deliver integrated services for emergencies and long-term paths toward autonomy and dignity.</p>',
                    'ru' => '<p>Мы оказываем комплексные услуги — от экстренной помощи до долгосрочного сопровождения.</p>',
                ],
                'meta' => [
                    'services' => [
                        [
                            'title' => [
                                'it' => 'Aiuti umanitari e unità di strada',
                                'en' => 'Humanitarian aid & street unit',
                                'ru' => 'Гуманитарная помощь и уличная команда',
                            ],
                            'body' => [
                                'it' => "Attraverso la nostra unità di strada raggiungiamo persone senza dimora e cittadini che attraversano momenti di forte vulnerabilità. In poco tempo siamo arrivati a preparare e distribuire oltre 1.000 pasti caldi, accompagnati da beni essenziali, coperte e supporto orientativo verso i servizi del territorio.\n\nQuesto lavoro è reso possibile grazie a una cucina concessa gratuitamente alla nostra associazione, al recupero alimentare di prodotti ancora perfettamente utilizzabili e all'impegno quotidiano dei volontari.",
                                'en' => 'Our street unit reaches people without shelter and those in acute vulnerability — distributing hot meals, essentials, blankets, and guidance to local services.',
                                'ru' => 'Уличная команда помогает людям без жилья — горячие обеды, essentials, одеяла и ориентация по службам.',
                            ],
                            'stats' => [
                                'it' => '1.000+ pasti caldi · cucina gratuita · recupero alimentare · volontari',
                                'en' => '1,000+ hot meals · free kitchen · food recovery · volunteers',
                                'ru' => '1 000+ горячих обедов · бесплатная кухня · перераспределение еды',
                            ],
                        ],
                        [
                            'title' => [
                                'it' => 'Tutela dei diritti umani',
                                'en' => 'Human rights advocacy',
                                'ru' => 'Защита прав человека',
                            ],
                            'body' => [
                                'it' => "La difesa dei diritti umani è il cuore della nostra missione. Il team lavora a fianco di persone e famiglie richiedenti asilo, titolari di protezione internazionale o protezione speciale, offrendo orientamento, accompagnamento e supporto nell'accesso ai propri diritti.",
                                'en' => 'Human rights defense is at the heart of our mission — supporting asylum seekers and protected persons with guidance and access to their rights.',
                                'ru' => 'Защита прав — сердце миссии: сопровождение просителей убежища и людей под защитой.',
                            ],
                        ],
                        [
                            'title' => [
                                'it' => 'Sportello legale e supporto amministrativo',
                                'en' => 'Legal desk & admin support',
                                'ru' => 'Юридическая консультация',
                            ],
                            'body' => [
                                'it' => 'Sportello legale gratuito: consulenze di primo livello, orientamento e presa in carico senza costi. Obiettivo: rendere giustizia e accesso ai diritti più vicini e comprensibili.',
                                'en' => 'Free legal desk: first-level consultations and guidance at no cost for people in fragile situations.',
                                'ru' => 'Бесплатный юридический пункт первичных консультаций.',
                            ],
                        ],
                        [
                            'title' => [
                                'it' => 'Sportello digitale e inclusione tecnologica',
                                'en' => 'Digital desk & tech inclusion',
                                'ru' => 'Цифровая включённость',
                            ],
                            'body' => [
                                'it' => 'Supporto a cittadini, famiglie e anziani nella gestione delle pratiche burocratiche e nell\'utilizzo degli strumenti digitali.',
                                'en' => 'Support for citizens and families navigating bureaucracy and digital public services.',
                                'ru' => 'Помощь с бюрократией и цифровыми госуслугами.',
                            ],
                        ],
                        [
                            'title' => [
                                'it' => 'Reinserimento sociale e rete territoriale',
                                'en' => 'Social reintegration & local network',
                                'ru' => 'Социальная реинтеграция',
                            ],
                            'body' => [
                                'it' => "Percorsi personalizzati di reinserimento sociale e autonomia. Sviluppo di una rete tra associazioni, enti, professionisti e realtà del territorio per interventi tempestivi e coordinati.\n\nSolo attraverso una comunità unita è possibile offrire risposte rapide, efficaci e durature.",
                                'en' => 'Personalised reintegration paths and a coordinated network of local organisations for timely, lasting responses.',
                                'ru' => 'Индивидуальные программы реинтеграции и сеть местных организаций.',
                            ],
                        ],
                    ],
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'contact'],
            [
                'template' => 'contact',
                'is_published' => true,
                'title' => [
                    'it' => 'Contatti',
                    'en' => 'Contact',
                    'ru' => 'Контакты',
                ],
                'slug' => [
                    'it' => 'contatti',
                    'en' => 'contact',
                    'ru' => 'kontakty',
                ],
                'body' => [
                    'it' => '<p>Per informazioni sui nostri servizi, volontariato o collaborazioni, scrivici usando il modulo in questa pagina.</p><p><strong>Email:</strong> info@safehouse.community</p>',
                    'en' => '<p>For questions about our services, volunteering, or partnerships, get in touch using the form on this page.</p><p><strong>Email:</strong> info@safehouse.community</p>',
                    'ru' => '<p>По вопросам услуг, волонтёрства или сотрудничества — напишите через форму на этой странице.</p><p><strong>Email:</strong> info@safehouse.community</p>',
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'privacy'],
            [
                'template' => 'legal',
                'is_published' => true,
                'title' => [
                    'it' => 'Privacy policy',
                    'en' => 'Privacy policy',
                    'ru' => 'Политика конфиденциальности',
                ],
                'slug' => [
                    'it' => 'privacy',
                    'en' => 'privacy',
                    'ru' => 'privacy',
                ],
                'body' => [
                    'it' => '<p>Questa pagina descrive come Safe House ETS tratta i dati personali raccolti tramite il sito web. Testo completo da approvare con il consulente privacy — bozza CMS.</p>',
                    'en' => '<p>This page describes how Safe House ETS processes personal data collected through this website. Full legal text pending counsel review — CMS draft.</p>',
                    'ru' => '<p>Как Safe House ETS обрабатывает персональные данные на сайте. Полный юридический текст — после согласования с консультантом.</p>',
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'cookie'],
            [
                'template' => 'legal',
                'is_published' => true,
                'title' => [
                    'it' => 'Cookie policy',
                    'en' => 'Cookie policy',
                    'ru' => 'Политика cookie',
                ],
                'slug' => [
                    'it' => 'cookie',
                    'en' => 'cookie',
                    'ru' => 'cookie',
                ],
                'body' => [
                    'it' => '<p>Questo sito utilizza cookie necessari per il funzionamento (sessione, sicurezza) e, previo consenso, cookie analitici. Puoi gestire le preferenze dal banner cookie o contattarci per maggiori informazioni.</p>',
                    'en' => '<p>This site uses essential cookies (session, security) and, with your consent, analytics cookies. Manage preferences from the cookie banner or contact us for more information.</p>',
                    'ru' => '<p>На сайте используются необходимые cookie (сессия, безопасность) и, с вашего согласия, аналитические cookie. Настройки — в баннере cookie или по запросу.</p>',
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'demo-landing'],
            [
                'template' => 'landing',
                'is_published' => true,
                'title' => [
                    'it' => 'Esempio landing',
                    'en' => 'Landing example',
                    'ru' => 'Пример лендинга',
                ],
                'slug' => [
                    'it' => 'esempio-landing',
                    'en' => 'landing-example',
                    'ru' => 'primer-landing',
                ],
                'body' => [
                    'it' => '<p>Template landing per campagne e iniziative speciali: hero a tutta larghezza, messaggio chiaro e invito all\'azione. Ideale per raccolte temporanee o eventi sul territorio.</p>',
                    'en' => '<p>Landing template for campaigns and special initiatives: full-width hero, clear message, and a call to action. Ideal for time-bound drives or community events.</p>',
                    'ru' => '<p>Шаблон лендинга для кампаний и специнициатив: широкий hero, понятное сообщение и призыв к действию.</p>',
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'demo-article'],
            [
                'template' => 'article',
                'is_published' => true,
                'title' => [
                    'it' => 'Esempio articolo',
                    'en' => 'Article example',
                    'ru' => 'Пример статьи',
                ],
                'slug' => [
                    'it' => 'esempio-articolo',
                    'en' => 'article-example',
                    'ru' => 'primer-statya',
                ],
                'body' => [
                    'it' => '<p>Template articolo per approfondimenti, testimonianze e rassegna stampa. Tipografia da lettura lunga e data di aggiornamento in evidenza.</p><p>Usa questa pagina come esempio di contenuto editoriale curato dal CMS.</p>',
                    'en' => '<p>Article template for in-depth stories, testimonials, and press reviews. Long-read typography with a visible updated date.</p><p>Use this page as an example of editorial content managed in the CMS.</p>',
                    'ru' => '<p>Шаблон статьи для материалов, историй и обзора прессы. Типографика для длинного чтения с датой обновления.</p><p>Пример редакционного контента из CMS.</p>',
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'demo-news'],
            [
                'template' => 'news_index',
                'is_published' => true,
                'title' => [
                    'it' => 'Hub notizie',
                    'en' => 'News hub',
                    'ru' => 'Хаб новостей',
                ],
                'slug' => [
                    'it' => 'hub-notizie',
                    'en' => 'news-hub',
                    'ru' => 'hab-novostey',
                ],
                'body' => [
                    'it' => '<p>Template hub notizie: introduzione e collegamento all\'elenco dinamico delle notizie pubblicate sul sito.</p>',
                    'en' => '<p>News hub template: intro copy and a link to the dynamic news list on the site.</p>',
                    'ru' => '<p>Шаблон хаба новостей: вступление и ссылка на динамический список новостей.</p>',
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['key' => 'trasparenza'],
            [
                'template' => 'legal',
                'is_published' => true,
                'title' => [
                    'it' => 'Trasparenza',
                    'en' => 'Transparency',
                    'ru' => 'Прозрачность',
                ],
                'slug' => [
                    'it' => 'trasparenza',
                    'en' => 'transparency',
                    'ru' => 'prozrachnost',
                ],
                'body' => [
                    'it' => '<p>Pubblichiamo annualmente bilancio sociale, rendicontazione delle raccolte e informazioni sul governo associativo. Questa pagina è un esempio di contenuto aggiuntivo gestito dal CMS e visibile nel menu <strong>Altre Pagine</strong>.</p><p>Per richieste specifiche scrivi a <a href="mailto:info@safehouse.community">info@safehouse.community</a>.</p>',
                    'en' => '<p>We publish an annual social report, fundraising summaries, and governance information. This page is an example of extra CMS content shown under <strong>Other pages</strong> in the menu.</p><p>For specific requests email <a href="mailto:info@safehouse.community">info@safehouse.community</a>.</p>',
                    'ru' => '<p>Мы публикуем годовой социальный отчёт, сводки по сборам и информацию об управлении ассоциацией. Эта страница — пример дополнительного CMS-контента в меню <strong>Другие страницы</strong>.</p><p>По запросам: <a href="mailto:info@safehouse.community">info@safehouse.community</a>.</p>',
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>|null  $existingMeta
     * @return array<string, mixed>
     */
    private function aboutMeta(?array $existingMeta): array
    {
        $meta = [
            'tagline' => [
                'it' => 'Comunità di accoglienza e solidarietà sul territorio',
                'en' => 'A community of welcome and solidarity on the ground',
                'ru' => 'Сообщество гостеприимства и солидарности',
            ],
            'values' => [
                'it' => "Safe House ETS crede che i diritti umani siano universali e debbano essere garantiti a ogni persona, senza distinzioni. Lottiamo ogni giorno contro le discriminazioni, l'emarginazione e tutte le forme di esclusione sociale. Scegliamo di stare accanto a chi vive situazioni di vulnerabilità, povertà o assenza di tutele, trasformando la solidarietà in azioni concrete.\n\nIl nostro valore più forte è la disobbedienza civile e sociale: non accettiamo l'indifferenza davanti alle ingiustizie e ci impegniamo a dare voce a chi non viene ascoltato. Crediamo nella presenza sul territorio, nell'ascolto e nell'intervento immediato. Per noi, essere in prima linea significa assumersi la responsabilità di costruire comunità più giuste, inclusive e umane.\n\nIl nostro obiettivo è diventare un punto di riferimento per chi ha bisogno di aiuto, tutela e dignità, mettendo sempre al centro le persone e i loro diritti.",
                'en' => "We believe human rights are universal and must be guaranteed to every person without distinction. We stand beside those facing vulnerability and poverty, turning solidarity into concrete action.\n\nCivil and social disobedience against indifference is our strongest value. We listen, act on the ground, and build fairer, more inclusive communities.",
                'ru' => "Мы верим, что права человека универсальны. Мы рядом с теми, кто в уязвимости, превращая солидарность в конкретные действия.\n\nГражданское неповиновение безразличию — наш главный принцип. Мы на месте, слушаем и действуем.",
            ],
            'closing' => [
                'it' => 'Safe House ETS è una casa sicura che si muove tra le persone, nelle strade e nelle comunità, trasformando solidarietà, diritti e inclusione in azioni concrete ogni giorno.',
                'en' => 'Safe House ETS is a safe home that moves among people, in the streets and communities — turning solidarity, rights, and inclusion into daily action.',
                'ru' => 'Safe House ETS — безопасный дом среди людей, на улицах и в сообществах, где солидарность становится делом каждый день.',
            ],
        ];

        if ($this->shouldSeedDemoCarousel()) {
            $meta['carousel'] = self::demoCarouselSlides();
        } elseif (is_array($existingMeta['carousel'] ?? null) && $existingMeta['carousel'] !== []) {
            $meta['carousel'] = $existingMeta['carousel'];
        }

        return $meta;
    }

    private function shouldSeedDemoCarousel(): bool
    {
        if (env('SEED_DEMO_CAROUSEL') !== null) {
            return filter_var(env('SEED_DEMO_CAROUSEL'), FILTER_VALIDATE_BOOLEAN);
        }

        return app()->environment(['local', 'testing']);
    }

    /**
     * @return list<array{path: string, alt: array<string, string>}>
     */
    private static function demoCarouselSlides(): array
    {
        return [
            [
                'path' => 'images/carousel-demo/slide-1.jpg',
                'alt' => [
                    'it' => 'Volontari Safe House — momento di convivialità',
                    'en' => 'Safe House volunteers — community moment',
                    'ru' => 'Волонтёры Safe House — общий момент',
                ],
            ],
            [
                'path' => 'images/carousel-demo/slide-2.jpg',
                'alt' => [
                    'it' => 'Gruppo Safe House — comunità sul territorio',
                    'en' => 'Safe House group — community on the ground',
                    'ru' => 'Группа Safe House — сообщество',
                ],
            ],
        ];
    }
}

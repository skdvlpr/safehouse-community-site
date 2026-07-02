<?php

namespace Database\Seeders;

use App\Models\DonationCampaign;
use Illuminate\Database\Seeder;

class DonationCampaignSeeder extends Seeder
{
    public function run(): void
    {
        DonationCampaign::query()
            ->where('slug', 'debug-campaign')
            ->update(['is_active' => false]);

        DonationCampaign::query()->updateOrCreate(
            ['slug' => 'safe-house'],
            [
                'title' => [
                    'it' => 'Dona a Safe House',
                    'en' => 'Donate to Safe House',
                    'ru' => 'Поддержать Safe House',
                ],
                'description' => [
                    'it' => '<p>Con il tuo contributo sosteniamo ogni giorno accoglienza, pasti caldi, orientamento legale e supporto alle persone in situazione di fragilità. Ogni donazione, piccola o grande, ci aiuta a restare presenti sul territorio.</p>',
                    'en' => '<p>Your gift supports daily welcome, hot meals, legal guidance, and practical help for people in vulnerable situations. Every contribution helps us stay present in the community.</p>',
                    'ru' => '<p>Ваш вклад помогает нам каждый день обеспечивать приём, горячие обеды, юридическую поддержку и практическую помощь людям в уязвимом положении.</p>',
                ],
                'form_notice' => [
                    'it' => 'I dati della carta non transitano sui nostri server: vengono inviati direttamente a Stripe, il nostro partner per i pagamenti sicuri.',
                    'en' => 'Card details never pass through our servers — they are sent directly to Stripe, our secure payment partner.',
                    'ru' => 'Данные карты не проходят через наши серверы — они отправляются напрямую в Stripe, нашему платёжному партнёру.',
                ],
                'privacy_notice' => [
                    'it' => '<p>Trattiamo nome, email e dati di contatto solo per registrare la donazione, inviare la ricevuta e adempiere agli obblighi di legge. I dati di pagamento sono gestiti da Stripe.</p>',
                    'en' => '<p>We process name, email, and contact details only to record the donation, send a receipt, and meet legal obligations. Payment data is handled by Stripe.</p>',
                    'ru' => '<p>Мы обрабатываем имя, email и контактные данные только для учёта пожертвования, отправки квитанции и выполнения юридических обязательств. Платёжные данные обрабатывает Stripe.</p>',
                ],
                'thank_you_message' => [
                    'it' => 'Il tuo pagamento è andato a buon fine. Il tuo contributo ci aiuta a continuare il lavoro di accoglienza, tutela dei diritti e solidarietà concreta ogni giorno.',
                    'en' => 'Your payment was successful. Your contribution helps us continue our daily work of welcome, rights protection, and concrete solidarity.',
                    'ru' => 'Платёж прошёл успешно. Ваш вклад помогает нам продолжать ежедневную работу по приёму, защите прав и практической солидарности.',
                ],
                'preset_amounts' => [1000, 2500, 5000, 10000, 25000, 50000],
                'allow_custom_amount' => true,
                'min_amount_cents' => 500,
                'currency' => 'EUR',
                'espocrm_finanziamento_name' => 'Donate to Safe House',
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        DonationCampaign::query()->updateOrCreate(
            ['slug' => 'operazione-inverno'],
            [
                'title' => [
                    'it' => 'Operazione Inverno 2026',
                    'en' => 'Winter Operation 2026',
                    'ru' => 'Зимняя операция 2026',
                ],
                'description' => [
                    'it' => '<p>Coperte, indumenti termici, pasti caldi e kit di emergenza per chi vive la strada durante i mesi più rigidi. Obiettivo: 120 kit completi entro marzo.</p>',
                    'en' => '<p>Blankets, warm clothing, hot meals, and emergency kits for people living on the street during the coldest months. Goal: 120 complete kits by March.</p>',
                    'ru' => '<p>Одеяла, тёплая одежда, горячие обеды и аварийные наборы для людей на улице в холодные месяцы. Цель: 120 комплектов к марту.</p>',
                ],
                'form_notice' => [
                    'it' => 'Puoi donare una tantum o scegliere un importo simbolico: ogni euro copre materiali essenziali per una notte al sicuro.',
                    'en' => 'Give once or choose a symbolic amount — every euro covers essential supplies for one safer night.',
                    'ru' => 'Можно пожертвовать один раз или выбрать символическую сумму — каждый евро покрывает essentials на одну более безопасную ночь.',
                ],
                'privacy_notice' => [
                    'it' => '<p>I dati raccolti servono esclusivamente alla gestione della donazione e alla rendicontazione della raccolta, nel rispetto del GDPR.</p>',
                    'en' => '<p>Collected data is used only to manage the donation and report on this campaign, in compliance with GDPR.</p>',
                    'ru' => '<p>Собранные данные используются только для учёта пожертвования и отчётности по кампании в соответствии с GDPR.</p>',
                ],
                'thank_you_message' => [
                    'it' => 'Grazie per il tuo sostegno all\'Operazione Inverno. Con il tuo aiuto possiamo portare calore, cibo e dignità a chi ne ha più bisogno in questa stagione.',
                    'en' => 'Thank you for supporting the Winter Operation. With your help we can bring warmth, food, and dignity to those who need it most this season.',
                    'ru' => 'Спасибо за поддержку зимней операции. С вашей помощью мы можем принести тепло, еду и достоинство тем, кто больше всего нуждается в этом сезоне.',
                ],
                'preset_amounts' => [1500, 3000, 6000, 12000],
                'allow_custom_amount' => true,
                'min_amount_cents' => 500,
                'currency' => 'EUR',
                'espocrm_finanziamento_name' => 'Operazione Inverno 2026',
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        DonationCampaign::query()->updateOrCreate(
            ['slug' => 'mensa-solidale'],
            [
                'title' => [
                    'it' => 'Mensa solidale',
                    'en' => 'Community kitchen',
                    'ru' => 'Социальная столовая',
                ],
                'description' => [
                    'it' => '<p>Sostieni la mensa che ogni settimana serve pasti caldi e dignitosi. Con 5 € copriamo un pasto completo; con 50 € una settimana di presenza in cucina per una persona.</p>',
                    'en' => '<p>Support the kitchen that serves warm, dignified meals every week. €5 covers one full meal; €50 covers a week of kitchen presence for one person.</p>',
                    'ru' => '<p>Поддержите столовую, которая каждую неделю подаёт горячие и достойные обеды. 5 € — один полный обед; 50 € — неделя присутствия на кухне для одного человека.</p>',
                ],
                'form_notice' => [
                    'it' => 'La donazione è deducibile secondo la normativa vigente per le ONLUS riconosciute. Riceverai conferma via email.',
                    'en' => 'Donations may be tax-deductible under applicable rules for recognized non-profits. You will receive email confirmation.',
                    'ru' => 'Пожертвование может быть налогово вычитаемым согласно действующим правилам для некоммерческих организаций. Вы получите подтверждение по email.',
                ],
                'privacy_notice' => [
                    'it' => '<p>Utilizziamo i dati del donatore solo per adempiere agli obblighi contabili e inviare attestazioni, senza cessione a terzi per marketing.</p>',
                    'en' => '<p>We use donor data only for accounting obligations and receipts, without sharing it with third parties for marketing.</p>',
                    'ru' => '<p>Мы используем данные донора только для бухгалтерского учёта и квитанций, без передачи третьим лицам в маркетинговых целях.',
                ],
                'thank_you_message' => [
                    'it' => 'Grazie! Il tuo contributo si trasforma subito in pasti concreti, ingredienti freschi e un luogo accogliente per chi ha fame di dignità oltre che di cibo.',
                    'en' => 'Thank you! Your gift immediately becomes real meals, fresh ingredients, and a welcoming place for those hungry for dignity as well as food.',
                    'ru' => 'Спасибо! Ваш вклад сразу превращается в реальные обеды, свежие продукты и гостеприимное место для тех, кому нужна не только еда, но и достоинство.',
                ],
                'preset_amounts' => [500, 1000, 2500, 5000, 50000],
                'allow_custom_amount' => true,
                'min_amount_cents' => 500,
                'currency' => 'EUR',
                'espocrm_finanziamento_name' => 'Mensa solidale',
                'is_active' => true,
                'sort_order' => 2,
            ],
        );
    }
}

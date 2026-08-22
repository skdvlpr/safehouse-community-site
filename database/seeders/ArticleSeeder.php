<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $category = ArticleCategory::query()->updateOrCreate(
            ['slug->it' => 'comunita'],
            [
                'name' => [
                    'it' => 'Comunità',
                    'en' => 'Community',
                    'ru' => 'Сообщество',
                ],
                'slug' => [
                    'it' => 'comunita',
                    'en' => 'community',
                    'ru' => 'soobshchestvo',
                ],
                'description' => [
                    'it' => 'Notizie dall\'associazione e dal territorio.',
                    'en' => 'News from the association and the community.',
                    'ru' => 'Новости ассоциации и сообщества.',
                ],
            ],
        );

        Article::query()->updateOrCreate(
            ['slug->it' => 'benvenuti-safe-house'],
            [
                'article_category_id' => $category->id,
                'title' => [
                    'it' => 'Benvenuti in Safe House Community',
                    'en' => 'Welcome to Safe House Community',
                    'ru' => 'Добро пожаловать в Safe House Community',
                ],
                'slug' => [
                    'it' => 'benvenuti-safe-house',
                    'en' => 'welcome-safe-house',
                    'ru' => 'dobro-pozhalovat',
                ],
                'excerpt' => [
                    'it' => 'La nostra associazione è al lavoro ogni giorno per accoglienza, diritti e inclusione.',
                    'en' => 'Our association works every day for welcome, rights, and inclusion.',
                    'ru' => 'Наша ассоциация каждый день работает ради гостеприимства, прав и включения.',
                ],
                'body' => [
                    'it' => '<p>Safe House ETS è una casa sicura che si muove tra le persone, nelle strade e nelle comunità.</p><p>Segui questa sezione per aggiornamenti su progetti, volontariato e iniziative sul territorio.</p>',
                    'en' => '<p>Safe House ETS is a safe home that moves among people, in the streets and communities.</p><p>Follow this section for updates on projects, volunteering, and local initiatives.</p>',
                    'ru' => '<p>Safe House ETS — безопасный дом среди людей, на улицах и в сообществах.</p><p>Следите за новостями о проектах, волонтёрстве и инициативах.</p>',
                ],
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
        );

        $eventsCategory = ArticleCategory::query()->updateOrCreate(
            ['slug->it' => 'eventi'],
            [
                'name' => [
                    'it' => 'Eventi',
                    'en' => 'Events',
                    'ru' => 'События',
                ],
                'slug' => [
                    'it' => 'eventi',
                    'en' => 'events',
                    'ru' => 'sobytiya',
                ],
                'description' => [
                    'it' => 'Iniziative, incontri e attività sul territorio.',
                    'en' => 'Initiatives, meetings, and local activities.',
                    'ru' => 'Инициативы, встречи и мероприятия.',
                ],
            ],
        );

        Article::query()->updateOrCreate(
            ['slug->it' => 'open-day-volontari'],
            [
                'article_category_id' => $eventsCategory->id,
                'title' => [
                    'it' => 'Open day volontari',
                    'en' => 'Volunteer open day',
                    'ru' => 'День открытых дверей для волонтёров',
                ],
                'slug' => [
                    'it' => 'open-day-volontari',
                    'en' => 'volunteer-open-day',
                    'ru' => 'den-otkrytyh-dverey',
                ],
                'excerpt' => [
                    'it' => 'Un pomeriggio per conoscere il volontariato in Safe House.',
                    'en' => 'An afternoon to learn about volunteering at Safe House.',
                    'ru' => 'Послеобеденное время, чтобы узнать о волонтёрстве в Safe House.',
                ],
                'body' => [
                    'it' => '<p>Vi aspettiamo in sede per presentare le attività e rispondere alle domande.</p>',
                    'en' => '<p>Join us on site to learn about our activities and ask questions.</p>',
                    'ru' => '<p>Ждём вас, чтобы рассказать о деятельности и ответить на вопросы.</p>',
                ],
                'is_published' => true,
                'published_at' => now(),
            ],
        );
    }
}

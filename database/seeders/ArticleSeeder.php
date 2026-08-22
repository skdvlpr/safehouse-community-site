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
            ['slug->it' => 'community'],
            [
                'name' => [
                    'it' => 'Comunità',
                    'en' => 'Community',
                ],
                'slug' => [
                    'it' => 'community',
                    'en' => 'community',
                ],
                'description' => [
                    'it' => 'Notizie dall\'associazione e dal territorio.',
                    'en' => 'News from the association and the community.',
                ],
            ],
        );

        Article::query()->updateOrCreate(
            ['slug->it' => 'welcome-safe-house'],
            [
                'article_category_id' => $category->id,
                'title' => [
                    'it' => 'Benvenuti in Safe House Community',
                    'en' => 'Welcome to Safe House Community',
                ],
                'slug' => [
                    'it' => 'welcome-safe-house',
                    'en' => 'welcome-safe-house',
                ],
                'excerpt' => [
                    'it' => 'La nostra associazione è al lavoro ogni giorno per accoglienza, diritti e inclusione.',
                    'en' => 'Our association works every day for welcome, rights, and inclusion.',
                ],
                'body' => [
                    'it' => '<p>Safe House ETS è una casa sicura che si muove tra le persone, nelle strade e nelle comunità.</p><p>Segui questa sezione per aggiornamenti su progetti, volontariato e iniziative sul territorio.</p>',
                    'en' => '<p>Safe House ETS is a safe home that moves among people, in the streets and communities.</p><p>Follow this section for updates on projects, volunteering, and local initiatives.</p>',
                ],
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
        );

        $eventsCategory = ArticleCategory::query()->updateOrCreate(
            ['slug->it' => 'events'],
            [
                'name' => [
                    'it' => 'Eventi',
                    'en' => 'Events',
                ],
                'slug' => [
                    'it' => 'events',
                    'en' => 'events',
                ],
                'description' => [
                    'it' => 'Iniziative, incontri e attività sul territorio.',
                    'en' => 'Initiatives, meetings, and local activities.',
                ],
            ],
        );

        Article::query()->updateOrCreate(
            ['slug->it' => 'volunteer-open-day'],
            [
                'article_category_id' => $eventsCategory->id,
                'title' => [
                    'it' => 'Open day volontari',
                    'en' => 'Volunteer open day',
                ],
                'slug' => [
                    'it' => 'volunteer-open-day',
                    'en' => 'volunteer-open-day',
                ],
                'excerpt' => [
                    'it' => 'Un pomeriggio per conoscere il volontariato in Safe House.',
                    'en' => 'An afternoon to learn about volunteering at Safe House.',
                ],
                'body' => [
                    'it' => '<p>Vi aspettiamo in sede per presentare le attività e rispondere alle domande.</p>',
                    'en' => '<p>Join us on site to learn about our activities and ask questions.</p>',
                ],
                'is_published' => true,
                'published_at' => now(),
            ],
        );
    }
}

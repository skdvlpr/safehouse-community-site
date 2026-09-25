<?php

use App\Models\Page;
use App\Support\MembershipEnglishLanding;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = Page::query()->where('key', 'diventa-socio')->first();

        if ($page === null) {
            return;
        }

        $english = (string) $page->getTranslation('body', 'en');
        $split = MembershipEnglishLanding::splitParagraphHeadings($english);

        if ($split === $english) {
            return;
        }

        $page->setTranslation('body', 'en', $split);
        $page->save();
    }

    public function down(): void
    {
        // The previous English body was one block. Leaving the split text in place keeps the cards.
    }
};

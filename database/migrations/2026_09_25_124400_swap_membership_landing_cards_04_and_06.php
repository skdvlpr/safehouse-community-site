<?php

use App\Models\Page;
use App\Support\MembershipLandingCardOrder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = Page::query()->where('key', 'diventa-socio')->first();

        if ($page === null) {
            return;
        }

        foreach (['it', 'en'] as $locale) {
            $body = (string) $page->getTranslation('body', $locale);
            $swapped = MembershipLandingCardOrder::swapHtml($body);

            if ($swapped !== $body) {
                $page->setTranslation('body', $locale, $swapped);
            }
        }

        $meta = is_array($page->meta) ? $page->meta : [];
        $cards = $meta['landing_cards'] ?? null;

        if (is_array($cards) && $cards !== []) {
            $meta['landing_cards'] = MembershipLandingCardOrder::swapMetaCards($cards);
            $page->meta = $meta;
        }

        $page->save();
    }

    public function down(): void
    {
        // Order-only CMS edit. Re-run is a no-op once Cosa-significa is already before Chi-può.
    }
};

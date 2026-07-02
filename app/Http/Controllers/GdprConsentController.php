<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGdprConsentRequest;
use App\Services\GdprConsentService;
use Illuminate\Http\JsonResponse;

class GdprConsentController extends Controller
{
    public function __construct(
        private readonly GdprConsentService $consents,
    ) {}

    public function store(StoreGdprConsentRequest $request, string $locale): JsonResponse
    {
        $level = $request->validated('level');

        $this->consents->recordCookieBanner($request, $level);

        return response()->json([
            'status' => 'ok',
            'level' => $level,
        ]);
    }
}

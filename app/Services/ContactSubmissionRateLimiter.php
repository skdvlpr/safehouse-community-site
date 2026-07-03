<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ContactSubmissionRateLimiter
{
    private const MAX_ATTEMPTS = 10;

    private const DECAY_SECONDS = 3600;

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn|false
     */
    public function attempt(Request $request, callable $callback): mixed
    {
        return RateLimiter::attempt(
            $this->key($request),
            self::MAX_ATTEMPTS,
            $callback,
            self::DECAY_SECONDS,
        );
    }

    public function key(Request $request): string
    {
        return 'contact|'.$request->ip();
    }
}

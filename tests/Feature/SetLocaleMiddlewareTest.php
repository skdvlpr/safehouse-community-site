<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class SetLocaleMiddlewareTest extends TestCase
{
    public function test_middleware_sets_application_locale_for_valid_code(): void
    {
        $middleware = new SetLocale;

        $middleware->handle(
            Request::create('/ru/example', 'GET'),
            fn (): Response => new Response('ok', 200),
        );

        $this->assertSame('ru', App::getLocale());
    }

    public function test_middleware_sets_carbon_locale_for_valid_code(): void
    {
        $middleware = new SetLocale;

        $middleware->handle(
            Request::create('/it/example', 'GET'),
            fn (): Response => new Response('ok', 200),
        );

        $this->assertSame('it', Carbon::getLocale());
    }

    public function test_middleware_aborts_for_unknown_locale(): void
    {
        $middleware = new SetLocale;

        $this->expectException(NotFoundHttpException::class);

        $middleware->handle(
            Request::create('/xx/example', 'GET'),
            fn (): Response => new Response('ok', 200),
        );
    }
}

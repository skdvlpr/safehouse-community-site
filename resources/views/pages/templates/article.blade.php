
@extends('layouts.app')

@section('title', $title)

@section('main_class', 'mx-auto w-full max-w-3xl flex-1 px-4 py-10')

@section('content')
    <x-page-template-shell :page="$page">
        <div class="template-article-rail">
            @include('pages.partials.template-eyebrow', ['label' => __('site.pages.templates.article')])

            <div class="template-article-meta">
                <span>{{ __('site.pages.article_label') }}</span>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $page->updated_at?->toDateString() }}">
                    {{ $page->updated_at?->locale($locale)->isoFormat('LL') }}
                </time>
            </div>

            <h1 class="mb-8 text-3xl font-semibold leading-tight tracking-tight md:text-4xl">{{ $title }}</h1>

            <article class="template-article-body safehouse-glass safehouse-prose">
                {!! $body !!}
            </article>
        </div>
    </x-page-template-shell>
@endsection

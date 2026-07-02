@php
    /** @var \App\DataTransferObjects\FundraisingProgress $progress */
@endphp

<div class="fundraising-progress" aria-label="{{ __('site.donations.progress_label') }}">
    @if ($progress->hasTarget())
        <div class="fundraising-progress__amounts">
            <span class="fundraising-progress__collected">{{ $progress->formatMoney($progress->collected) }}</span>
            <span class="fundraising-progress__separator">/</span>
            <span class="fundraising-progress__target">{{ $progress->formatMoney($progress->target) }}</span>
        </div>
        <div class="fundraising-progress__track" role="progressbar"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="{{ $progress->percent }}">
            <div class="fundraising-progress__fill" style="width: {{ $progress->percent }}%;">
                <span class="fundraising-progress__percent">{{ $progress->percent }}%</span>
            </div>
        </div>
    @else
        <p class="fundraising-progress__collected-only">
            {{ __('site.donations.collected_so_far', ['amount' => $progress->formatMoney($progress->collected)]) }}
        </p>
    @endif
</div>

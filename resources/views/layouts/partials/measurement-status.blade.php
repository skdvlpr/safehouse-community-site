@php
    $measurementBoot = app(\App\Services\MeasurementBootService::class);
@endphp

<p class="template-legal-measurement-status" role="status">
    {{ __($measurementBoot->isBootable() ? 'site.measurement.status_on' : 'site.measurement.status_off') }}
</p>

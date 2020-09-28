@component('mail::message')
# Dear Language Expert for {{ $language->name }}

There are {{ $count }} new Pending Questions that have been added yesterday and not yet handled. Please visit the {{ config('app.name') }} and check those out when you have time.

@component('mail::button', ['url' => url('/')])
Visit portal
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

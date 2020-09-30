@component('mail::message')
# Dear Language Expert for {{ $language->name }}

There are **{{ $count }}** Pending Questions that have not been handled yet. Please visit the {{ config('app.name') }} and deal with those when you have time.

@component('mail::button', ['url' => url('/')])
Visit portal
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

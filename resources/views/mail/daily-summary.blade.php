<x-mail::message>
# Daily Wash Summary — {{ $summary['date'] }}

**Total cars:** {{ number_format($summary['total_cars']) }}  
**Total revenue:** ৳{{ number_format($summary['total_revenue'], 0) }}

## By Site

@foreach ($summary['by_site'] as $site)
- **{{ $site['site_name'] }}** — {{ $site['cars'] }} cars · ৳{{ number_format($site['revenue'], 0) }}
@endforeach

@if (count($summary['missing_sites'] ?? []) > 0)
## Missing Logs
@foreach ($summary['missing_sites'] as $name)
- {{ $name }}
@endforeach
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

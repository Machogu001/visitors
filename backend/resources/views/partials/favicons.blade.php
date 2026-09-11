@php
	$customFavicon = config('branding.favicon');
	$hasCustomFavicon = is_string($customFavicon) && $customFavicon !== '' && file_exists(public_path($customFavicon));
	$faviconVersion = $hasCustomFavicon ? filemtime(public_path($customFavicon)) : null;
@endphp
@if ($hasCustomFavicon)
	<link rel="icon" href="{{ asset($customFavicon) }}?v={{ $faviconVersion }}">
@else
	<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
@endif
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#f3f5f9">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('branding.name', 'VisitorPortal') }}">

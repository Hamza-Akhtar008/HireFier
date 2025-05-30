@php
	$bottomAdvertising ??= [];
	$isFromHome ??= false;
@endphp
@if (!empty($bottomAdvertising))
	@php
		$margin = '';
		if (!$isFromHome) {
			$margin = ' mb-3';
		}
	@endphp
	@if ($isFromHome)
		@include('front.sections.spacer')
	@endif
	<div class="container{{ $margin }}">
		<div class="row">
			@php
				$responsiveClass = (data_get($bottomAdvertising, 'is_responsive') != 1) ? ' d-none d-xl-block d-lg-block d-md-none d-sm-none' : '';
			@endphp
			{{-- Desktop --}}
			<div class="container mb20 ads-parent-responsive{{ $responsiveClass }}">
				<div class="text-center">
					{!! data_get($bottomAdvertising, 'tracking_code_large') !!}
				</div>
			</div>
			@if (data_get($bottomAdvertising, 'is_responsive') != 1)
				{{-- Tablet --}}
				<div class="container mb20 ads-parent-responsive d-none d-xl-none d-lg-none d-md-block d-sm-none">
					<div class="text-center">
						{!! data_get($bottomAdvertising, 'tracking_code_medium') !!}
					</div>
				</div>
				{{-- Mobile --}}
				<div class="container ads-parent-responsive d-block d-xl-none d-lg-none d-md-none d-sm-block">
					<div class="text-center">
						{!! data_get($bottomAdvertising, 'tracking_code_small') !!}
					</div>
				</div>
			@endif
		</div>
	</div>
@endif

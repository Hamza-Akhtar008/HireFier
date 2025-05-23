@php
	$post ??= [];
@endphp
<div class="reg-sidebar-inner text-center">
	
	@if (request()->segment(1) == 'create' || request()->segment(2) == 'create')
		{{-- Create Form --}}
		<div class="promo-text-box">
			<i class="fa-regular fa-image fa-4x icon-color-1"></i>
			<h3><strong>{{ t('create_new_job') }}</strong></h3>
			<p>
				{{ t('Do you have a post to be filled within your company', ['appName' => config('app.name')]) }}
			</p>
		</div>
	@else
		{{-- Edit Form --}}
		@if (isSingleStepFormEnabled())
			{{-- Single Step Form --}}
			@if (auth()->check())
				@if (auth()->user()->getAuthIdentifier() == data_get($post, 'user_id'))
					<div class="card sidebar-card panel-contact-seller">
						<div class="card-header">{{ t('author_actions') }}</div>
						<div class="card-content user-info">
							<div class="card-body text-center">
								<a href="{{ urlGen()->post($post) }}" class="btn btn-default btn-block">
									<i class="fa-regular fa-hand-point-right"></i> {{ t('Return to the Ad') }}
								</a>
							</div>
						</div>
					</div>
				@endif
			@endif
			
		@else
			{{-- Multi Steps Form --}}
			@if (auth()->check())
				@if (auth()->user()->getAuthIdentifier() == data_get($post, 'user_id'))
					<div class="card sidebar-card panel-contact-seller">
						<div class="card-header">{{ t('author_actions') }}</div>
						<div class="card-content user-info">
							<div class="card-body text-center">
								<a href="{{ urlGen()->post($post) }}" class="btn btn-default btn-block">
									<i class="fa-regular fa-hand-point-right"></i> {{ t('Return to the Ad') }}
								</a>
								@if (isset($countPackages) && isset($countPaymentMethods) && $countPackages > 0 && $countPaymentMethods > 0)
									<a href="{{ url('posts/' . data_get($post, 'id') . '/payment') }}" class="btn btn-success btn-block">
										<i class="fa-regular fa-circle-check"></i> {{ t('Make It Premium') }}
									</a>
								@endif
							</div>
						</div>
					</div>
				@endif
			@endif
		
		@endif
	@endif
	
	<div class="card sidebar-card border-color-primary">
		<div class="card-header bg-primary border-color-primary text-white uppercase">
			<strong>{{ t('How to find quickly a candidate') }}</strong>
		</div>
		<div class="card-content">
			<div class="card-body text-start">
				<ul class="list-check">
					<li> {{ t('find_quickly_candidate_advice_1') }} </li>
					<li> {{ t('find_quickly_candidate_advice_2') }}</li>
					<li> {{ t('find_quickly_candidate_advice_4') }}</li>
					<li> {{ t('find_quickly_candidate_advice_5') }}</li>
					<li> {{ t('find_quickly_candidate_advice_6') }}</li>
				</ul>
			</div>
		</div>
	</div>
	
</div>

{{--
 * JobClass - Job Board Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com/jobclass
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
--}}
@extends('auth.layouts.master')

@php
	$passwordTips = getPasswordTips();
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
@endphp

@section('content')
	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-11 col-xxl-10 mx-auto">
		@php
			// $mbAuth = socialLogin()->isEnabled() ? ' mb-4' : ' mb-4';
			$mbAuth = ' mb-4';
		@endphp
		<div class="row d-flex justify-content-center">
			<div class="col-12 col-sm-12 col-md-12 col-lg-11 col-xl-10 col-xxl-8">
				<h3 class="fw-600{{ $mbAuth }}">{{ trans('auth.sign_up') }}</h3>
			</div>
		</div>
		
		@include('auth.login.inc.social', ['page' => 'register', 'position' => 'top'])
		
		<div class="row d-flex justify-content-center">
			<div class="col-12 col-sm-12 col-md-12 col-lg-11 col-xl-10 col-xxl-8">
				<p class="text-muted mb-4">{{ trans('auth.register_description') }}</p>
				
				<form id="signupForm"
				      class="form-horizontal"
				      method="post"
				      action="{{ url()->current() }}"
				      enctype="multipart/form-data"
				>
					{!! csrf_field() !!}
					@honeypot
					
					{{-- user_type_id --}}
					@php
						$userTypeIdError = (isset($errors) && $errors->has('user_type_id')) ? ' is-invalid' : '';
						$userTypeId = old('user_type_id', request()->query('type'));
					@endphp
					<div class="mb3 required">
						<label class="form-label">{{ t('you_are_a') }} <sup>*</sup></label>
						<div class="col-10 mb-3">
							@foreach ($userTypes as $type)
								@php
									$iTypeId = data_get($type, 'id');
								@endphp
								<div class="form-check form-check-inline pt-2">
									<input type="radio"
									       name="user_type_id"
									       id="userTypeId-{{ $iTypeId }}"
									       class="form-check-input{{ $userTypeIdError }}"
									       value="{{ $iTypeId }}" @checked($userTypeId == $iTypeId)
									>
									<label class="form-check-label" for="userTypeId-{{ $iTypeId }}">
										{{ data_get($type, 'label') }}
									</label>
								</div>
							@endforeach
						</div>
					</div>
					
					{{-- name --}}
					@php
						$nameError = (isset($errors) && $errors->has('name')) ? ' is-invalid' : '';
					@endphp
					<div class="mb-3 required">
						<label class="form-label">{{ t('Name') }} <sup>*</sup></label>
						<input name="name"
						       placeholder="{{ t('Name') }}"
						       class="form-control input-md{{ $nameError }}"
						       type="text"
						       value="{{ old('name') }}"
						>
					</div>
					
					{{-- country_code --}}
					@if (empty(config('country.code')))
						@php
							$countryCodeError = (isset($errors) && $errors->has('country_code')) ? ' is-invalid' : '';
							$countryCodeValue = (!empty(config('ipCountry.code'))) ? config('ipCountry.code') : 0;
						@endphp
						<div class="mb-3 required">
							<label class="form-label{{ $countryCodeError }}" for="country_code">
								{{ t('your_country') }} <sup>*</sup>
							</label>
							<select id="countryCode"
							        name="country_code"
							        class="form-control large-data-selecter{{ $countryCodeError }}"
							>
								<option value="0" @selected(empty(old('country_code')))>
									{{ t('Select') }}
								</option>
								@foreach ($countries as $code => $item)
									<option value="{{ $code }}" @selected(old('country_code', $countryCodeValue) == $code)>
										{{ $item->get('name') }}
									</option>
								@endforeach
							</select>
						</div>
					@else
						<input id="countryCode" name="country_code" type="hidden" value="{{ config('country.code') }}">
					@endif
					
					{{-- auth_field (as notification channel) --}}
					@php
						$authFields = getAuthFields(true);
						$authFieldError = (isset($errors) && $errors->has('auth_field')) ? ' is-invalid' : '';
						$usersCanChooseNotifyChannel = isUsersCanChooseNotifyChannel();
						$authFieldValue = ($usersCanChooseNotifyChannel) ? (old('auth_field', getAuthField())) : getAuthField();
					@endphp
					@if ($usersCanChooseNotifyChannel)
						<div class="mb-3 required">
							<label class="form-label" for="auth_field">
								{{ trans('auth.notifications_channel') }} <sup>*</sup>
							</label>
							<div class="row">
								<div class="col-12">
									@foreach ($authFields as $iAuthField => $notificationType)
										<div class="form-check form-check-inline pt-2">
											<input name="auth_field"
											       id="{{ $iAuthField }}AuthField"
											       value="{{ $iAuthField }}"
											       class="form-check-input auth-field-input{{ $authFieldError }}"
											       type="radio" @checked($authFieldValue == $iAuthField)
											>
											<label class="form-check-label mb-0" for="{{ $iAuthField }}AuthField">
												{{ $notificationType }}
											</label>
										</div>
									@endforeach
									<div class="form-text text-muted">
										{{ trans('auth.notifications_channel_hint') }}
									</div>
								</div>
							</div>
						</div>
					@else
						<input id="{{ $authFieldValue }}AuthField" name="auth_field" type="hidden" value="{{ $authFieldValue }}">
					@endif
					
					@php
						$forceToDisplay = isBothAuthFieldsCanBeDisplayed() ? ' force-to-display' : '';
					@endphp
					
					{{-- email --}}
					@php
						$emailError = (isset($errors) && $errors->has('email')) ? ' is-invalid' : '';
						$emailRequiredClass = (getAuthField() == 'email') ? ' required' : '';
					@endphp
					<div class="mb-3 auth-field-item{{ $emailRequiredClass . $forceToDisplay }}">
						<label class="form-label pt-0" for="email">{{ trans('auth.email') }}
							@if (getAuthField() == 'email')
								<sup>*</sup>
							@endif
						</label>
						<input id="email" name="email"
						       type="email"
						       data-valid-type="email"
						       class="form-control{{ $emailError }}"
						       placeholder="{{ trans('auth.email_address') }}"
						       value="{{ old('email') }}"
						>
					</div>
					
					{{-- phone --}}
					@php
						$phoneError = (isset($errors) && $errors->has('phone')) ? ' is-invalid' : '';
						$phoneCountryValue = config('country.code');
						$phoneRequiredClass = (getAuthField() == 'phone') ? ' required' : '';
					@endphp
					<div class="row mb-3 auth-field-item{{ $phoneRequiredClass . $forceToDisplay }}">
						<div class="col-8">
							<label class="form-label pt-0" for="phone">{{ trans('auth.phone_number') }}
								@if (getAuthField() == 'phone')
									<sup>*</sup>
								@endif
							</label>
							<input id="phone" name="phone"
							       class="form-control input-md{{ $phoneError }}"
							       type="tel"
							       placeholder="{{ trans('auth.phone_number') }}"
							       value="{{ phoneE164(old('phone'), old('phone_country', $phoneCountryValue)) }}"
							       autocomplete="off"
							>
							<input name="phone_country" type="hidden" value="{{ old('phone_country', $phoneCountryValue) }}">
						</div>
					</div>
					
					{{-- username --}}
					@php
						$usernameIsEnabled = !config('larapen.core.disable.username');
					@endphp
					@if ($usernameIsEnabled)
						@php
							$usernameError = (isset($errors) && $errors->has('username')) ? ' is-invalid' : '';
						@endphp
						<div class="row mb-3">
							<div class="col-8">
								<label class="form-label" for="username">{{ trans('auth.username') }}</label>
								<input id="username"
								       name="username"
								       type="text"
								       class="form-control{{ $usernameError }}"
								       placeholder="{{ trans('auth.username') }}"
								       value="{{ old('username') }}"
								>
							</div>
						</div>
					@endif
					
					{{-- password --}}
					@php
						$passwordError = (isset($errors) && $errors->has('password')) ? ' is-invalid' : '';
					@endphp
					<div class="row mb-3 required">
						<div class="col-10">
							<div class="row">
								<label class="form-label col-6" for="password">
									{{ trans('auth.password') }} <sup>*</sup>
								</label>
								<div class="col-6 text-end">
									<i class="fa-regular fa-eye-slash"></i>
									<a href=""
									   class="toggle-password-link"
									   data-bs-toggle="tooltip"
									   data-bs-title="{{ trans('auth.show_password') }}"
									   data-toggle-text="true"
									>
										{{ trans('auth.show') }}
									</a>
								</div>
							</div>
							<input id="password" name="password"
							       type="password"
							       class="form-control{{ $passwordError }}"
							       placeholder="{{ trans('auth.password') }}"
							       autocomplete="new-password"
							>
							@if (!empty($passwordTips))
								<div class="form-text text-muted mt-2">
									@foreach($passwordTips as $tip)
										<span class="d-block"><i class="bi bi-check2"></i> {{ $tip }}</span>
									@endforeach
								</div>
							@endif
						</div>
					</div>
					
					{{-- password_confirmation --}}
					@php
						$passwordError = (isset($errors) && $errors->has('password')) ? ' is-invalid' : '';
					@endphp
					<div class="row mb-3 required">
						<div class="col-10">
							<div class="row">
								<label class="form-label col-6" for="passwordConfirmation">
									{{ trans('auth.confirm_password') }} <sup>*</sup>
								</label>
								<div class="col-6 text-end">
									<i class="fa-regular fa-eye-slash"></i>
									<a href="#"
									   class="toggle-password-link"
									   data-bs-toggle="tooltip"
									   data-bs-title="{{ trans('auth.show_password') }}"
									   data-toggle-text="true"
									>
										{{ trans('auth.show') }}
									</a>
								</div>
							</div>
							<input id="passwordConfirmation" name="password_confirmation"
							       type="password"
							       class="form-control{{ $passwordError }}"
							       placeholder="{{ trans('auth.confirm_password') }}"
							       autocomplete="off"
							>
						</div>
					</div>
					
					@if (config('larapen.core.register.showCompanyFields'))
						<div id="companyBloc" class="row mt-4">
							<h4 class="col-12">
								<i class="bi bi-buildings"></i>
								<strong>{{ t('company_information') }}</strong>
							</h4>
							
							@include('auth.register.partials.company')
						</div>
					@endif
					
					@if (config('larapen.core.register.showResumeFields'))
						<div id="resumeBloc" class="row mt-4">
							<h4 class="col-12">
								<i class="fa-solid fa-paperclip fa"></i>
								<strong>{{ t('Resume') }}</strong>
							</h4>
							
							@include('auth.register.partials.resume')
						</div>
					@endif
					
					@include('front.layouts.inc.tools.captcha', ['colRight' => 'col-10', 'label' => true])
					
					{{-- accept_terms --}}
					@php
						$acceptTermsError = (isset($errors) && $errors->has('accept_terms')) ? ' is-invalid' : '';
					@endphp
					<div class="mb-1 required">
						<div class="form-check">
							<input name="accept_terms" id="acceptTerms"
							       class="form-check-input{{ $acceptTermsError }}"
							       value="1"
							       type="checkbox" @checked(old('accept_terms') == '1')
							>
							<label class="form-check-label" for="acceptTerms" style="font-weight: normal;">
								{!! t('accept_terms_label', ['attributes' => getUrlPageByType('terms')]) !!}
							</label>
						</div>
					</div>
					
					{{-- accept_marketing_offers --}}
					@php
						$acceptMarketingOffersError = (isset($errors) && $errors->has('accept_marketing_offers')) ? ' is-invalid' : '';
					@endphp
					<div class="mb-3 required">
						<div class="form-check">
							<input name="accept_marketing_offers" id="acceptMarketingOffers"
							       class="form-check-input{{ $acceptMarketingOffersError }}"
							       value="1"
							       type="checkbox" @checked(old('accept_marketing_offers') == '1')
							>
							<label class="form-check-label" for="acceptMarketingOffers" style="font-weight: normal;">
								{!! t('accept_marketing_offers_label') !!}
							</label>
						</div>
					</div>
					
					{{-- Button --}}
					<div class="d-grid my-4">
						<button type="submit" id="signupBtn" class="btn btn-primary btn-lg">
							{{ trans('auth.sign_up') }}
						</button>
					</div>
				</form>
				
			</div>
		</div>
		
		<p class="text-center text-muted mb-0">
			{{ trans('auth.already_have_account') }} <a href="{{ urlGen()->signIn() }}">{{ trans('auth.sign_in') }}</a>
		</p>
	</div>
@endsection

@section('after_styles')
	<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
	@if (config('lang.direction') == 'rtl')
		<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput-rtl.min.css') }}" rel="stylesheet">
	@endif
	@if (str_starts_with($fiTheme, 'explorer'))
		<link href="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.min.css') }}" rel="stylesheet">
	@endif
	<style>
		.krajee-default.file-preview-frame:hover:not(.file-preview-error) {
			box-shadow: 0 0 5px 0 #666666;
		}
	</style>
@endsection

@section('after_scripts')
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.js') }}" type="text/javascript"></script>
	<script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>
	
	<script>
		let userTypeId = '{{ $userTypeId ?? 0 }}';
		
		onDocumentReady((event) => {
			{{-- Set user type --}}
			setUserType(userTypeId);
			const userTypeEls = document.querySelectorAll('input[type=radio][name=user_type_id]');
			if (userTypeEls.length > 0) {
				userTypeEls.forEach((radio) => {
					radio.addEventListener('change', (e) => setUserType(e.target.value));
				});
			}
		});
	</script>
@endsection

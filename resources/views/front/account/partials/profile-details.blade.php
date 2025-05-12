<div class="col-12">
	<div class="card card-default">
		<div class="card-header">
			<h4 class="card-title">
				{{ t('Account Details') }}
			</h4>
		</div>
		<div class="card-body">
			<form name="details"
			      class="form-horizontal"
			      role="form"
			      method="POST"
			      action="{{ urlGen()->accountProfile() }}"
			>
				{!! csrf_field() !!}
				<input name="_method" type="hidden" value="PUT">
				
				@if (empty($authUser->user_type_id) || $authUser->user_type_id == 0)
					
					{{-- user_type_id --}}
					@php
						$userTypeIdError = (isset($errors) && $errors->has('user_type_id')) ? ' is-invalid' : '';
					@endphp
					<div class="row mb-3 required">
						<label class="col-md-3 col-form-label{{ $userTypeIdError }}" for="user_type_id">
							{{ t('you_are_a') }} <sup>*</sup>
						</label>
						<div class="col-md-9">
							<select name="user_type_id" id="userTypeId" class="form-control selecter{{ $userTypeIdError }}">
								<option value="0" @selected(empty(old('user_type_id')))>
									{{ t('Select') }}
								</option>
								@foreach ($userTypes as $type)
									<option value="{{ data_get($type, 'id') }}"
											@selected(old('user_type_id', $authUser->user_type_id) == data_get($type, 'id'))
									>
										{{ data_get($type, 'label') }}
									</option>
								@endforeach
							</select>
						</div>
					</div>
				
				@else
					
					{{-- gender_id --}}
					@php
						$genderIdError = (isset($errors) && $errors->has('gender_id')) ? ' is-invalid' : '';
					@endphp
					<div class="row mb-3 required">
						<label class="col-md-3 col-form-label" for="gender_id">
							{{ t('gender') }} <sup>*</sup>
						</label>
						<div class="col-md-9 col-lg-8 col-xl-6">
							<select name="gender_id" id="genderId" class="form-control selecter{{ $genderIdError }}">
								<option value="0" @selected(empty(old('gender_id')))>
									{{ t('Select') }}
								</option>
								@if (!empty($genders))
									@foreach ($genders as $gender)
										<option value="{{ data_get($gender, 'id') }}"
												@selected(old('gender_id', $authUser->gender_id) == data_get($gender, 'id'))
										>
											{{ data_get($gender, 'title') }}
										</option>
									@endforeach
								@endif
							</select>
						</div>
					</div>
					
					{{-- name --}}
					@php
						$nameError = (isset($errors) && $errors->has('name')) ? ' is-invalid' : '';
					@endphp
					<div class="row mb-3 required">
						<label class="col-md-3 col-form-label{{ $nameError }}" for="name">
							{{ t('Name') }} <sup>*</sup>
						</label>
						<div class="col-md-9 col-lg-8 col-xl-6">
							<input name="name"
							       type="text"
							       class="form-control{{ $nameError }}"
							       placeholder=""
							       value="{{ old('name', $authUser->name) }}"
							>
						</div>
					</div>
					
					{{-- username --}}
					@php
						$usernameError = (isset($errors) && $errors->has('username')) ? ' is-invalid' : '';
					@endphp
					<div class="row mb-3 required">
						<label class="col-md-3 col-form-label{{ $usernameError }}" for="username">
							{{ trans('auth.username') }}
						</label>
						<div class="col-md-9 col-lg-8 col-xl-6">
							<div class="input-group{{ $usernameError }}">
								<span class="input-group-text"><i class="fa-regular fa-user"></i></span>
								<input id="username" name="username"
								       type="text"
								       class="form-control{{ $usernameError }}"
								       placeholder="{{ trans('auth.username') }}"
								       value="{{ old('username', $authUser->username) }}"
								>
							</div>
						</div>
					</div>
					
					{{-- auth_field (as notification channel) --}}
					@php
						$authFields = getAuthFields(true);
						$authFieldError = (isset($errors) && $errors->has('auth_field')) ? ' is-invalid' : '';
						$usersCanChooseNotifyChannel = isUsersCanChooseNotifyChannel(true);
						$authFieldValue = $authUser->auth_field ?? getAuthField();
						$authFieldValue = ($usersCanChooseNotifyChannel) ? old('auth_field', $authFieldValue) : $authFieldValue;
					@endphp
					@if ($usersCanChooseNotifyChannel)
						<div class="row mb-3 required">
							<label class="col-md-3 col-form-label" for="auth_field">{{ trans('auth.notifications_channel') }}
								<sup>*</sup></label>
							<div class="col-md-9">
								@foreach ($authFields as $iAuthField => $notificationType)
									<div class="form-check form-check-inline pt-2">
										<input name="auth_field"
										       id="{{ $iAuthField }}AuthField"
										       value="{{ $iAuthField }}"
										       class="form-check-input auth-field-input{{ $authFieldError }}"
										       type="radio" @checked($authFieldValue == $iAuthField)
										>
										<label class="form-check-label mb-0" for="auth_field_{{ $iAuthField }}">
											{{ $notificationType }}
										</label>
									</div>
								@endforeach
								<div class="form-text text-muted">
									{{ t('notifications_channel_hint') }}
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
					@endphp
					<div class="row mb-3 auth-field-item required{{ $forceToDisplay }}">
						<label class="col-md-3 col-form-label{{ $emailError }}" for="email">{{ t('email') }}
							@if (getAuthField() == 'email')
								<sup>*</sup>
							@endif
						</label>
						<div class="col-md-9 col-lg-8 col-xl-6">
							<div class="input-group{{ $emailError }}">
								<span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
								<input id="email" name="email"
								       type="email"
								       data-valid-type="email"
								       class="form-control{{ $emailError }}"
								       placeholder="{{ t('email_address') }}"
								       value="{{ old('email', $authUser->email) }}"
								>
							</div>
						</div>
					</div>
					
					{{-- phone --}}
					@php
						$phoneError = (isset($errors) && $errors->has('phone')) ? ' is-invalid' : '';
						$phoneValue = $authUser->phone ?? null;
						$phoneCountryValue = $authUser->phone_country ?? config('country.code');
						$phoneValue = phoneE164($phoneValue, $phoneCountryValue);
						$phoneValueOld = phoneE164(old('phone', $phoneValue), old('phone_country', $phoneCountryValue));
					@endphp
					<div class="row mb-3 auth-field-item required{{ $forceToDisplay }}">
						<label class="col-md-3 col-form-label{{ $phoneError }}" for="phone">{{ t('phone') }}
							@if (getAuthField() == 'phone')
								<sup>*</sup>
							@endif
						</label>
						<div class="col-md-9 col-lg-8 col-xl-6">
							<div class="input-group{{ $phoneError }}">
								<input id="phone" name="phone"
								       type="tel"
								       class="form-control{{ $phoneError }}"
								       value="{{ $phoneValueOld }}"
								>
								<span class="input-group-text iti-group-text">
										<input name="phone_hidden"
										       id="phoneHidden"
										       type="checkbox"
										       value="1" @checked(old('phone_hidden', $authUser->phone_hidden) == '1')
										>&nbsp;<small>{{ t('Hide') }}</small>
									</span>
							</div>
							<input name="phone_country" type="hidden" value="{{ old('phone_country', $phoneCountryValue) }}">
						</div>
					</div>
					
					{{-- country_code --}}
					<input name="country_code" type="hidden" value="{{ $authUser->country_code }}">
				
				@endif
				
				<div class="row mb-3">
					<div class="offset-md-3 col-md-9"></div>
				</div>
				
				{{-- button --}}
				<div class="row">
					<div class="offset-md-3 col-md-9">
						<button type="submit" class="btn btn-primary">{{ t('Update') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

@section('after_scripts')
	@parent
	<script>
		phoneCountry = '{{ old('phone_country', ($phoneCountryValue ?? '')) }}';
	</script>
@endsection

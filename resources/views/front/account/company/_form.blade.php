@section('modal_location')
	@include('front.layouts.inc.modal.location')
@endsection

@php
	/* Get form origin */
	$originForm ??= null;
	
	/* From Company's Form */
	$classLeftCol = 'col-md-3';
	$classRightCol = 'col-md-9';
	
	$classRightCol = ($originForm == 'user') ? 'col-md-7' : $classRightCol; /* From User's Form */
	$classRightCol = ($originForm == 'post') ? 'col-md-8' : $classRightCol; /* From Post's Form */
	
	$companyInput ??= [];
	$company ??= [];
	
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$serverAllowedImageFormatsJson = collect(getServerAllowedImageFormats())->toJson();
@endphp

<div id="companyFields">
	{{-- name --}}
	@php
		$companyNameError = (isset($errors) && $errors->has('company.name')) ? ' is-invalid' : '';
		$companyName = data_get($company, 'name');
		$companyName = data_get($companyInput, 'company.name', $companyName);
	@endphp
	<div class="row mb-3">
		<label class="{{ $classLeftCol }} col-form-label" for="companyName">{{ t('company_name') }} <sup>*</sup></label>
		<div class="{{ $classRightCol }}">
			<input name="company[name]"
			       id="companyName"
				   placeholder="{{ t('company_name') }}"
				   class="form-control input-md{{ $companyNameError }}"
				   type="text"
				   value="{{ old('company.name', $companyName) }}"
			>
		</div>
	</div>
	
	{{-- logo_path --}}
	@php
		$companyLogoPathError = (isset($errors) && $errors->has('company.logo_path')) ? ' is-invalid' : '';
		$companyLogoPath = data_get($company, 'logo_path');
		$companyLogoPath = data_get($companyInput, 'company.logo_path', $companyLogoPath);
	@endphp
	<div class="row mb-3">
		<label class="{{ $classLeftCol }} col-form-label{{ $companyLogoPathError }}" for="logoPath">
			{{ t('Logo') }}
		</label>
		<div class="{{ $classRightCol }}">
			<div {!! (config('lang.direction')=='rtl') ? 'dir="rtl"' : '' !!} class="file-loading mb10">
				<input id="logoPath" name="company[logo_path]" type="file" class="file{{ $companyLogoPathError }}">
			</div>
			<div class="form-text text-muted">
				{{ t('file_types', ['file_types' => getAllowedFileFormatsHint('image')]) }}
			</div>
		</div>
	</div>
	
	{{-- description --}}
	@php
		$companyDescriptionError = (isset($errors) && $errors->has('company.description')) ? ' is-invalid' : '';
		$companyDescription = data_get($company, 'description');
		$companyDescription = data_get($companyInput, 'company.description', $companyDescription);
	@endphp
	<div class="row mb-3">
		<label class="{{ $classLeftCol }} col-form-label" for="companyDescription">{{ t('Company Description') }} <sup>*</sup></label>
		<div class="{{ $classRightCol }}">
			<textarea class="form-control{{ $companyDescriptionError }}"
					  name="company[description]"
					  id="companyDescription"
					  rows="10"
					  style="height: 200px"
			>{{ old('company.description', $companyDescription) }}</textarea>
			<div class="form-text text-muted">
				{{ t('Describe the company') }} - ({{ t('N characters maximum', ['number' => 1000]) }})
			</div>
		</div>
	</div>
	
	@if (!empty($company))
		{{-- country_code --}}
		@php
			$companyCountryCodeError = (isset($errors) && $errors->has('company.country_code')) ? ' is-invalid' : '';
			$companyCountryCode = data_get($company, 'country_code', config('country.code', 0));
			$companyCountryCode = old('company.country_code', $companyCountryCode);
		@endphp
		<div class="row mb-3 required">
			<label class="{{ $classLeftCol }} col-form-label{{ $companyCountryCodeError }}" for="countryCode">{{ t('country') }}</label>
			<div class="{{ $classRightCol }}">
				<select id="countryCode" name="company[country_code]" class="form-control large-data-selecter{{ $companyCountryCodeError }}">
					<option value="0" data-admin-type="0" @selected(empty(old('company.country_code')))>
						{{ t('select_a_country') }}
					</option>
					@foreach ($countries as $item)
						<option value="{{ data_get($item, 'code') }}"
								data-admin-type="{{ data_get($item, 'admin_type', 0) }}"
								@selected($companyCountryCode == data_get($item, 'code'))
						>
							{{ data_get($item, 'name') }}
						</option>
					@endforeach
				</select>
			</div>
		</div>
		
		@php
			$adminType = config('country.admin_type', 0);
		@endphp
		@if (config('settings.listing_form.city_selection') == 'select')
			@if (in_array($adminType, ['1', '2']))
				{{-- admin_code --}}
				@php
					$adminCodeError = (isset($errors) && $errors->has('admin_code')) ? ' is-invalid' : '';
				@endphp
				<div id="locationBox" class="row mb-3 required">
					<label class="{{ $classLeftCol }} col-form-label{{ $adminCodeError }}" for="adminCode">
						{{ t('location') }} <sup>*</sup>
					</label>
					<div class="{{ $classRightCol }}">
						<select id="adminCode" name="company[admin_code]" class="form-control large-data-selecter{{ $adminCodeError }}">
							<option value="0" @selected(empty(old('admin_code')))>
								{{ t('select_your_location') }}
							</option>
						</select>
					</div>
				</div>
			@endif
		@else
			@php
				$adminType = (in_array($adminType, ['0', '1', '2'])) ? $adminType : 0;
				$relAdminType = (in_array($adminType, ['1', '2'])) ? $adminType : 1;
				$adminCode = data_get($company, 'city.subadmin' . $relAdminType . '_code', 0);
				$adminCode = data_get($company, 'city.subAdmin' . $relAdminType . '.code', $adminCode);
				$adminName = data_get($company, 'city.subAdmin' . $relAdminType . '.name');
				$cityId = data_get($company, 'city.id', 0);
				$cityName = data_get($company, 'city.name');
				$fullCityName = !empty($adminName) ? $cityName . ', ' . $adminName : $cityName;
			@endphp
			<input type="hidden" id="selectedAdminType" name="selected_admin_type" value="{{ old('selected_admin_type', $adminType) }}">
			<input type="hidden" id="selectedAdminCode" name="selected_admin_code" value="{{ old('selected_admin_code', $adminCode) }}">
			<input type="hidden" id="selectedCityId" name="selected_city_id" value="{{ old('selected_city_id', $cityId) }}">
			<input type="hidden" id="selectedCityName" name="selected_city_name" value="{{ old('selected_city_name', $fullCityName) }}">
		@endif
		
		{{-- city_id --}}
		@php
			$companyCityIdError = (isset($errors) && $errors->has('company.city_id')) ? ' is-invalid' : '';
		@endphp
		<div id="cityBox" class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label{{ $companyCityIdError }}" for="cityId">{{ t('city') }}</label>
			<div class="{{ $classRightCol }}">
				<select id="cityId" name="company[city_id]" class="form-control large-data-selecter{{ $companyCityIdError }}">
					<option value="0" @selected(empty(old('company.city_id')))>
						{{ t('select_a_city') }}
					</option>
				</select>
			</div>
		</div>
		
		{{-- address --}}
		@php
			$companyAddressError = (isset($errors) && $errors->has('company.address')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyAddress">{{ t('Address') }}</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyAddressError }}">
					<span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
					<input name="company[address]"
					       id="companyAddress"
						   type="text"
						   class="form-control{{ $companyAddressError }}"
						   placeholder=""
						   value="{{ old('company.address', data_get($company, 'address')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- phone --}}
		@php
			$companyPhoneError = (isset($errors) && $errors->has('company.phone')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyPhone">{{ t('phone') }}</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyPhoneError }}">
					<span class="input-group-text"><i class="fa-solid fa-phone-flip"></i></span>
					<input name="company[phone]"
					       id="companyPhone"
					       type="text"
						   class="form-control{{ $companyPhoneError }}"
						   placeholder=""
						   value="{{ old('company.phone', data_get($company, 'phone')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- fax --}}
		@php
			$companyFaxError = (isset($errors) && $errors->has('company.fax')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyFax">{{ t('Fax') }}</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyFaxError }}">
					<span class="input-group-text"><i class="fa-solid fa-print"></i></span>
					<input name="company[fax]" id="companyFax"
					       type="text"
						   class="form-control{{ $companyFaxError }}"
						   placeholder=""
						   value="{{ old('company.fax', data_get($company, 'fax')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- email --}}
		@php
			$companyEmailError = (isset($errors) && $errors->has('company.email')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyEmail">{{ t('email') }}</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyEmailError }}">
					<span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
					<input name="company[email]"
					       id="companyEmail"
					       type="text"
					       data-valid-type="email"
						   class="form-control{{ $companyEmailError }}"
						   placeholder=""
						   value="{{ old('company.email', data_get($company, 'email')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- website --}}
		@php
			$companyWebsiteError = (isset($errors) && $errors->has('company.website')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyWebsite">{{ t('Website') }}</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyWebsiteError }}">
					<span class="input-group-text"><i class="fa-solid fa-globe"></i></span>
					<input name="company[website]"
					       id="companyWebsite"
					       type="text"
					       data-valid-type="url"
						   class="form-control{{ $companyWebsiteError }}"
						   placeholder=""
						   value="{{ old('company.website', data_get($company, 'website')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- facebook --}}
		@php
			$companyFacebookError = (isset($errors) && $errors->has('company.facebook')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyFacebook">Facebook</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyFacebookError }}">
					<span class="input-group-text"><i class="fa-brands fa-facebook"></i></span>
					<input name="company[facebook]"
					       id="companyFacebook"
					       type="text"
						   class="form-control{{ $companyFacebookError }}"
						   placeholder=""
						   value="{{ old('company.facebook', data_get($company, 'facebook')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- twitter --}}
		@php
			$companyTwitterError = (isset($errors) && $errors->has('company.twitter')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyTwitter">Twitter</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyTwitterError }}">
					<span class="input-group-text"><i class="fa-brands fa-x-twitter"></i></span>
					<input name="company[twitter]"
					       id="companyTwitter"
					       type="text"
						   class="form-control{{ $companyTwitterError }}"
						   placeholder=""
						   value="{{ old('company.twitter', data_get($company, 'twitter')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- linkedin --}}
		@php
			$companyLinkedinError = (isset($errors) && $errors->has('company.linkedin')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="company.linkedin">Linkedin</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyLinkedinError }}">
					<span class="input-group-text"><i class="fa-brands fa-linkedin"></i></span>
					<input name="company[linkedin]"
					       id="companyLinkedin"
					       type="text"
						   class="form-control{{ $companyLinkedinError }}"
						   placeholder=""
						   value="{{ old('company.linkedin', data_get($company, 'linkedin')) }}"
					>
				</div>
			</div>
		</div>
		
		{{-- pinterest --}}
		@php
			$companyPinterestError = (isset($errors) && $errors->has('company.pinterest')) ? ' is-invalid' : '';
		@endphp
		<div class="row mb-3">
			<label class="{{ $classLeftCol }} col-form-label" for="companyPinterest">Pinterest</label>
			<div class="{{ $classRightCol }}">
				<div class="input-group{{ $companyPinterestError }}">
					<span class="input-group-text"><i class="fa-solid fa-bullhorn"></i></span>
					<input name="company[pinterest]"
					       id="companyPinterest"
					       type="text"
						   class="form-control{{ $companyPinterestError }}"
						   placeholder=""
						   value="{{ old('company.pinterest', data_get($company, 'pinterest')) }}"
					>
				</div>
			</div>
		</div>
	@endif
</div>

@section('after_styles')
	@parent
	<style>
		#companyFields .select2-container {
			width: 100% !important;
		}
		.krajee-default.file-preview-frame .kv-file-content {
			height: auto;
		}
		.krajee-default.file-preview-frame .file-thumbnail-footer {
			height: 30px;
		}
	</style>
@endsection

@section('after_scripts')
	@parent
	<script>
		let coOptions = {};
		coOptions.theme = '{{ $fiTheme }}';
		coOptions.language = '{{ config('app.locale') }}';
		coOptions.rtl = {{ (config('lang.direction') == 'rtl') ? 'true' : 'false' }};
		coOptions.dropZoneEnabled = false;
		coOptions.showPreview = true;
		coOptions.previewFileType = 'image';
		coOptions.allowedFileExtensions = {!! $serverAllowedImageFormatsJson !!};
		coOptions.showUpload = false;
		coOptions.showRemove = false;
		coOptions.minFileSize = {{ (int)config('settings.upload.min_image_size', 0) }};
		coOptions.maxFileSize = {{ (int)config('settings.upload.max_image_size', 1000) }};
		coOptions.initialPreview = [];
		coOptions.fileActionSettings = {
			showDrag: false
		};
		coOptions.layoutTemplates = {
			footer: '<div class="file-thumbnail-footer pt-2">{actions}</div>',
			actionDelete: ''
		};
		
		@if (!empty($companyLogoPath) && isset($disk) && $disk->exists($companyLogoPath))
			@php
				// $logoUrl = thumbParam($companyLogoPath)->setOption('picture-md')->url();
				// $logoUrl = hasTemporaryPath($companyLogoPath) ? $disk->url($companyLogoPath) : $logoUrl;
				$logoUrl = thumbService($companyLogoPath)->resize('picture-md')->url();
			@endphp
			coOptions.initialPreview[0] =  '<img src="{{ $logoUrl }}" class="file-preview-image">';
		@endif
		
		onDocumentReady((event) => {
			{{-- fileinput --}}
			$('#logoPath').fileinput(coOptions);
		});
	</script>
	@if (!empty($company))
		@php
			$countryCode = data_get($company, 'country_code', 0);
			$adminType = config('country.admin_type', 0);
			$selectedAdminCode = data_get($company, 'city.subAdmin' . $adminType . '.code', 0);
			$selectedAdminCode = data_get($companyInput, 'admin_code', $selectedAdminCode);
			$cityId = (int)(data_get($company, 'city_id', 0));
		@endphp
		<script>
			/* Translation */
			var lang = {
				'select': {
					'country': "{{ t('select_a_country') }}",
					'admin': "{{ t('select_a_location') }}",
					'city': "{{ t('select_a_city') }}"
				}
			};
	
			/* Locations */
			var countryCode = '{{ old('company.country_code', $countryCode) }}';
			var adminType = '{{ $adminType }}';
			var selectedAdminCode = '{{ old('company.admin_code', $selectedAdminCode) }}';
			var cityId = '{{ (int)old('company.city_id', $cityId) }}';
		</script>
		@if (config('settings.listing_form.city_selection') == 'select')
			<script src="{{ url('assets/js/app/d.select.location.js') . vTime() }}"></script>
		@else
			<script src="{{ url('assets/js/app/browse.locations.js') . vTime() }}"></script>
			<script src="{{ url('assets/js/app/d.modal.location.js') . vTime() }}"></script>
		@endif
	@endif
@endsection

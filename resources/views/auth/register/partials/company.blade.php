@section('modal_location')
	@include('front.layouts.inc.modal.location')
@endsection

@php
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$serverAllowedImageFormatsJson = collect(getServerAllowedImageFormats())->toJson();
@endphp

<div id="companyFields" class="col-12">
	{{-- name --}}
	@php
		$companyNameError = (isset($errors) && $errors->has('company.name')) ? ' is-invalid' : '';
	@endphp
	<div class="mb-3">
		<label class="form-label" for="companyName">
			{{ t('company_name') }} <sup>*</sup>
		</label>
		<input name="company[name]"
		       id="companyName"
		       placeholder="{{ t('company_name') }}"
		       class="form-control{{ $companyNameError }}"
		       type="text"
		       value="{{ old('company.name') }}"
		>
	</div>
	
	{{-- logo_path --}}
	@php
		$companyLogoPathError = (isset($errors) && $errors->has('company.logo_path')) ? ' is-invalid' : '';
		$direction = (config('lang.direction')=='rtl') ? 'dir="rtl"' : '';
	@endphp
	<div class="row mb-3">
		<label class="form-label" for="logoPath">
			{{ t('Logo') }}
		</label>
		<div class="col-12">
			<div {!! $direction !!} class="file-loading mb10">
				<input id="logoPath" name="company[logo_path]" type="file" class="file{{ $companyLogoPathError }}">
			</div>
			<div class="form-text text-muted{{ $companyLogoPathError }}">
				{{ t('file_types', ['file_types' => getAllowedFileFormatsHint('image')]) }}
			</div>
		</div>
	</div>
	
	{{-- description --}}
	@php
		$companyDescriptionError = (isset($errors) && $errors->has('company.description')) ? ' is-invalid' : '';
	@endphp
	<div class="mb-3">
		<label class="form-label" for="companyDescription">
			{{ t('Company Description') }} <sup>*</sup>
		</label>
		<textarea class="form-control{{ $companyDescriptionError }}"
		          name="company[description]"
		          id="companyDescription"
		          rows="10"
		          style="height: 200px"
		>{{ old('company.description') }}</textarea>
		<div class="form-text text-muted{{ $companyDescriptionError }}">
			{{ t('Describe the company') }} - ({{ t('N characters maximum', ['number' => 1000]) }})
		</div>
	</div>
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
		
		onDocumentReady((event) => {
			{{-- fileinput --}}
			$('#logoPath').fileinput(coOptions);
		});
	</script>
@endsection

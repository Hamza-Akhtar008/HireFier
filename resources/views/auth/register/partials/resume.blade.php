@php
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$allowedFileFormatsJson = collect(getAllowedFileFormats())->toJson();
@endphp
<div id="resumeFields" class="col-12">
	
	{{-- file_path --}}
	@php
		$resumeFilePathError = (isset($errors) && $errors->has('resume.file_path')) ? ' is-invalid' : '';
	@endphp
	<div class="row mb-3">
		<label class="form-label" for="resumeFilePath">
			{{ t('your_resume') }}
		</label>
		<div class="col-12">
			<div class="mb10">
				<input id="resumeFilePath" name="resume[file_path]" type="file" class="file{{ $resumeFilePathError }}">
			</div>
			<div class="form-text text-muted{{ $resumeFilePathError }}">
				{{ t('file_types', ['file_types' => getAllowedFileFormatsHint()]) }}
			</div>
		</div>
	</div>
	
</div>

@section('after_styles')
	@parent
	<style>
		#resumeFields .select2-container {
			width: 100% !important;
		}
	</style>
@endsection

@section('after_scripts')
	@parent
	<script>
		let cvOptions = {};
		cvOptions.theme = '{{ $fiTheme }}';
		cvOptions.language = '{{ config('app.locale') }}';
		cvOptions.rtl = {{ (config('lang.direction') == 'rtl') ? 'true' : 'false' }};
		cvOptions.allowedFileExtensions = {!! $allowedFileFormatsJson !!};
		cvOptions.minFileSize = {{ (int)config('settings.upload.min_file_size', 0) }};
		cvOptions.maxFileSize = {{ (int)config('settings.upload.max_file_size', 1000) }};
		cvOptions.showPreview = false;
		cvOptions.showUpload = false;
		cvOptions.showRemove = false;
		
		onDocumentReady((event) => {
			{{-- fileinput (resume) --}}
			$('#resumeFilePath').fileinput(cvOptions);
		});
	</script>
@endsection

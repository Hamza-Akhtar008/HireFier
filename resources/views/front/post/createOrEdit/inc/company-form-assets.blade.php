@php
	$companyInput ??= [];
	$postInput ??= [];
	$post ??= [];
	$admin ??= [];
	
	$isSingleStepCreateForm = (isSingleStepFormEnabled() && request()->segment(1) == 'create');
	$isSingleStepEditForm = (isSingleStepFormEnabled() && request()->segment(1) == 'edit');
	
	$postId = data_get($post, 'id') ?? '';
	$postTypeId = data_get($postInput, 'post_type_id', 0);
	$postTypeId = data_get($post, 'post_type_id', $postTypeId);
	
	$countryCode = data_get($postInput, 'country_code', config('country.code', 0));
	$countryCode = data_get($post, 'country_code', $countryCode);
	
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
@endphp
@section('modal_location')
	@include('front.layouts.inc.modal.location')
@endsection

@push('after_styles_stack')
	<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
	@if (config('lang.direction') == 'rtl')
		<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput-rtl.min.css') }}" rel="stylesheet">
	@endif
	@if (str_starts_with($fiTheme, 'explorer'))
		<link href="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.min.css') }}" rel="stylesheet">
	@endif
	
	{{-- Multi Steps Form --}}
	@if (isMultipleStepsFormEnabled())
		<style>
			.krajee-default.file-preview-frame:hover:not(.file-preview-error) {
				box-shadow: 0 0 5px 0 #666666;
			}
		</style>
	@endif
	
	{{-- Single Step Form --}}
	@if (isSingleStepFormEnabled())
		<style>
			.krajee-default.file-preview-frame:hover:not(.file-preview-error) {
				box-shadow: 0 0 5px 0 #666666;
			}
			
			.file-loading:before {
				content: " {{ t('loading_wd') }}";
			}
			
			/* Preview Frame Size */
			.krajee-default.file-preview-frame .kv-file-content {
				height: auto;
			}
			
			.krajee-default.file-preview-frame .file-thumbnail-footer {
				height: 30px;
			}
		</style>
	@endif
@endpush

@push('after_scripts_stack')
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.js') }}" type="text/javascript"></script>
	<script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>
	
	<script>
		/* Company */
		var selectedCompanyId = '{{ old('company_id', ($selectedCompanyId ?? 0)) }}';
		
		onDocumentReady((event) => {
			/* Company */
			getCompany(selectedCompanyId);
			const companyIdEl = document.getElementById('companyId');
			if (companyIdEl) {
				$(companyIdEl).on('click', (e) => getCompany(e.target.value));
				$(companyIdEl).on('change', (e) => getCompany(e.target.value));
			}
			
			/* Company logo's button */
			const companyFormLinkEl = document.getElementById('companyFormLink');
			if (companyFormLinkEl) {
				companyFormLinkEl.addEventListener('click', (e) => {
					let companyLink = e.target.getAttribute('href');
					if (companyLink.indexOf('/new/') !== -1) {
						e.preventDefault();
						getCompany(0);
						
						return false;
					}
				});
			}
		});
	</script>
@endpush

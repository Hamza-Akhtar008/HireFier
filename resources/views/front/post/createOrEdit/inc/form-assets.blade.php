@php
	use App\Helpers\Common\Date;
	
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
	
	$adminType = config('country.admin_type', 0);
	
	$selectedAdminCode = data_get($postInput, 'admin_code', 0);
	$selectedAdminCode = data_get($admin, 'code', $selectedAdminCode);
	
	$cityId = data_get($postInput, 'city_id');
	$cityId = data_get($post, 'city_id', $cityId);
	
	$postCreatedAt = data_get($post, 'created_at');
	$postCreatedAt = Date::isValid($postCreatedAt) ? $postCreatedAt : date('Y-m-d');
	
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
@endphp
@section('modal_location')
	@include('front.layouts.inc.modal.location')
@endsection

@push('after_styles_stack')
	@include('front.layouts.inc.tools.wysiwyg.css')
	
	<link href="{{ url('assets/plugins/bootstrap-daterangepicker/3.1/daterangepicker.css') }}" rel="stylesheet">
@endpush

@push('after_scripts_stack')
	@include('front.layouts.inc.tools.wysiwyg.js')
	@include('front.common.js.payment-scripts')
	
	<script src="{{ url('assets/plugins/momentjs/2.18.1/moment.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-daterangepicker/3.1/daterangepicker.js') }}" type="text/javascript"></script>
	
	<script>
		/* Translation */
		var lang = {
			'select': {
				'country': "{{ t('select_a_country') }}",
				'admin': "{{ t('select_a_location') }}",
				'city': "{{ t('select_a_city') }}"
			},
			'price': "{{ t('Price') }}",
			'salary': "{{ t('Salary') }}",
			'nextStepBtnLabel': {
				'next': "{{ t('Next') }}",
				'submit': "{{ t('Update') }}"
			}
		};
		
		var stepParam = 0;
		
		/* Category */
		var oldInputAvailable = false;
		@if ($errors->isNotEmpty())
			oldInputAvailable = true;
		@endif
		
		/* Locations */
		var countryCode = '{{ old('country_code', $countryCode) }}';
		var adminType = '{{ $adminType }}';
		var selectedAdminCode = '{{ old('admin_code', $selectedAdminCode) }}';
		var cityId = '{{ old('city_id', $cityId) }}';
		
		/* Packages */
		var packageIsEnabled = false;
		@if (isset($packages, $paymentMethods) && $packages->count() > 0 && $paymentMethods->count() > 0)
			packageIsEnabled = true;
		@endif
	</script>
	<script>
		onDocumentReady((event) => {
			{{-- select2: If error occured, apply Bootstrap's error class --}}
			@if (config('settings.listing_form.city_selection') == 'select')
				@if ($errors->has('admin_code'))
					$('select[name="admin_code"]').closest('div').addClass('is-invalid');
				@endif
			@endif
			@if ($errors->has('city_id'))
				$('select[name="city_id"]').closest('div').addClass('is-invalid');
			@endif
			
			{{-- Tagging with multi-value Select Boxes --}}
			@php
				$tagsLimit = (int)config('settings.listing_form.tags_limit', 15);
				$tagsMinLength = (int)config('settings.listing_form.tags_min_length', 2);
				$tagsMaxLength = (int)config('settings.listing_form.tags_max_length', 30);
			@endphp
			let selectTagging = $('.tags-selecter').select2({
				language: langLayout.select2,
				width: '100%',
				tags: true,
				maximumSelectionLength: {{ $tagsLimit }},
				tokenSeparators: [',', ';', ':', '/', '\\', '#'],
				createTag: function (params) {
					var term = $.trim(params.term);
					
					{{-- Don't offset to create a tag if there is some symbols/characters --}}
					let invalidCharsArray = [',', ';', '_', '/', '\\', '#'];
					let arrayLength = invalidCharsArray.length;
					for (let i = 0; i < arrayLength; i++) {
						let invalidChar = invalidCharsArray[i];
						if (term.indexOf(invalidChar) !== -1) {
							return null;
						}
					}
					
					{{-- Don't offset to create empty tag --}}
							{{-- Return null to disable tag creation --}}
					if (term === '') {
						return null;
					}
					
					{{-- Don't allow tags which are less than 2 characters or more than 50 characters --}}
					if (term.length < {{ $tagsMinLength }} || term.length > {{ $tagsMaxLength }}) {
						return null;
					}
					
					return {
						id: term,
						text: term
					}
				}
			});
			
			{{-- Apply tags limit --}}
			selectTagging.on('change', function (e) {
				if ($(this).val().length > {{ $tagsLimit }}) {
					$(this).val($(this).val().slice(0, {{ $tagsLimit }}));
				}
			});
			
			{{-- select2: If error occured, apply Bootstrap's error class --}}
			@if ($errors->has('tags.*'))
				$('select[name^="tags"]').next('.select2.select2-container').addClass('is-invalid');
			@endif
			
			/*
			 * start_date field
			 * https://www.daterangepicker.com/#options
			 */
			let postCreatedAt = '{{ $postCreatedAt }}';
			let referenceDate = moment(postCreatedAt);
			
			let dateEl = $('#payableForm .cf-date');
			dateEl.daterangepicker({
				autoUpdateInput: false,
				autoApply: true,
				singleDatePicker: true,
				showDropdowns: true,
				minYear: parseInt(moment().format('YYYY'), 10) - 1, {{-- Note: Substract 1 year to avoid months disabling for the current year --}}
				maxYear: parseInt(moment().format('YYYY'), 10) + 10,
				startDate: moment().format('{{ t('datepicker_format') }}'),
				locale: {
					format: '{{ t('datepicker_format') }}',
					separator: " - ",
					applyLabel: "{{ t('datepicker_applyLabel') }}",
					cancelLabel: "{{ t('datepicker_cancelLabel') }}",
					fromLabel: "{{ t('datepicker_fromLabel') }}",
					toLabel: "{{ t('datepicker_toLabel') }}",
					customRangeLabel: "{{ t('datepicker_customRangeLabel') }}",
					weekLabel: "{{ t('datepicker_weekLabel') }}",
					daysOfWeek: [
						"{{ t('datepicker_sunday') }}",
						"{{ t('datepicker_monday') }}",
						"{{ t('datepicker_tuesday') }}",
						"{{ t('datepicker_wednesday') }}",
						"{{ t('datepicker_thursday') }}",
						"{{ t('datepicker_friday') }}",
						"{{ t('datepicker_saturday') }}"
					],
					monthNames: [
						"{{ t('January') }}",
						"{{ t('February') }}",
						"{{ t('March') }}",
						"{{ t('April') }}",
						"{{ t('May') }}",
						"{{ t('June') }}",
						"{{ t('July') }}",
						"{{ t('August') }}",
						"{{ t('September') }}",
						"{{ t('October') }}",
						"{{ t('November') }}",
						"{{ t('December') }}"
					],
					firstDay: 1
				}
			});
			dateEl.on('apply.daterangepicker', function (ev, picker) {
				{{-- Avoid past dates selection --}}
				if (picker.startDate.format('YYYYMMDD') >= parseInt(referenceDate.format('YYYYMMDD'))) {
					$(this).val(picker.startDate.format('{{ t('datepicker_format') }}'));
				} else {
					let dateInPastText = '{{ t('date_cannot_be_in_the_past') }}';
					Swal.fire({
						position: 'center',
						icon: 'error',
						text: dateInPastText
					});
					
					$(this).val('');
				}
			});
		});
	</script>
	
	<script src="{{ url('assets/js/app/d.modal.category.js') . vTime() }}"></script>
	@if (config('settings.listing_form.city_selection') == 'select')
		<script src="{{ url('assets/js/app/d.select.location.js') . vTime() }}"></script>
	@else
		<script src="{{ url('assets/js/app/browse.locations.js') . vTime() }}"></script>
		<script src="{{ url('assets/js/app/d.modal.location.js') . vTime() }}"></script>
	@endif
@endpush

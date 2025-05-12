@php
	$posts ??= [];
	$totalPosts ??= 0;
	
	$city ??= null;
	$cat ??= null;
	
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserTypeId = (!empty($authUser) && !empty($authUser->user_type_id)) ? $authUser->user_type_id : 0;
	$isJobSeekerUser = ($authUserTypeId == 2);
	
	$isFromSearchCompany ??= false;
@endphp
@if (!empty($posts) && $totalPosts > 0)
	@foreach($posts as $key => $post)
		@php
			// Get Package Info
			$premiumClass = '';
			$premiumBadge = '';
			if (data_get($post, 'featured') == 1) {
				if (!empty(data_get($post, 'payment.package'))) {
					$premiumClass = ' premium-post';
					$premiumBadge = ' <span class="badge bg-dark float-end">' . data_get($post, 'payment.package.short_name') . '</span>';
				}
			}
		@endphp
		
		@php
			$postId = data_get($post, 'id');
			$postUrl = urlGen()->post($post);
			$parentCatUrl = null;
			if (!empty(data_get($post, 'category.parent'))) {
				$parentCatUrl = urlGen()->category(data_get($post, 'category.parent'), null, $city);
			}
			$catUrl = urlGen()->category(data_get($post, 'category'), null, $city);
			$locationUrl = urlGen()->city(data_get($post, 'city'), null, $cat);
		@endphp
		
		<div class="item-list job-item{{ $premiumClass }}">
			<div class="row">
				<div class="col-md-1 col-sm-2 no-padding photobox">
					<div class="add-image">
						<a href="{{ $postUrl }}">
							<img class="img-thumbnail no-margin"
							     src="{{ data_get($post, 'logo_url.medium') }}"
							     alt="{{ data_get($post, 'company_name') }}"
							>
						</a>
					</div>
				</div>
				
				<div class="col-md-11 col-sm-10 add-desc-box">
					<div class="add-details jobs-item">
						<h5 class="company-title">
							@if (!empty(data_get($post, 'company_id')))
								<a href="{{ urlGen()->company(data_get($post, 'company_id')) }}">
									{{ data_get($post, 'company_name') }}
								</a>
							@else
								{{ data_get($post, 'company_name') }}
							@endif
						</h5>
						<h4 class="job-title">
							<a href="{{ $postUrl }}">{{ str(data_get($post, 'title'))->limit(70) }}</a>{!! $premiumBadge !!}
						</h4>
						
						@php
							$showPostInfo = (
								!config('settings.listings_list.hide_post_type')
								|| !config('settings.listings_list.hide_date')
								|| !config('settings.listings_list.hide_category')
								|| !config('settings.listings_list.hide_location')
								|| !config('settings.listings_list.hide_salary')
							);
						@endphp
						@if ($showPostInfo)
							<span class="info-row">
								@if (!config('settings.listings_list.hide_date'))
									<span class="date">
										<i class="fa-regular fa-clock"></i> {!! data_get($post, 'created_at_formatted') !!}
									</span>
								@endif
								@if (!config('settings.listings_list.hide_category'))
									<span class="category">
										<i class="bi bi-folder"></i>&nbsp;
										@if (!empty(data_get($post, 'category.parent')))
											<a href="{!! urlGen()->category(data_get($post, 'category.parent'), null, $city ?? null) !!}">
												{{ data_get($post, 'category.parent.name') }}
											</a>&nbsp;&raquo;&nbsp;
										@endif
										<a href="{!! urlGen()->category(data_get($post, 'category'), null, $city ?? null) !!}">
											{{ data_get($post, 'category.name') }}
										</a>
									</span>
								@endif
								@if (!config('settings.listings_list.hide_location'))
									<span class="item-location">
										<i class="bi bi-geo-alt"></i>&nbsp;
										<a href="{!! urlGen()->city(data_get($post, 'city'), null, $cat ?? null) !!}">
											{{ data_get($post, 'city.name') }}
										</a> {{ data_get($post, 'distance_info') }}
									</span>
								@endif
								@if (!config('settings.listings_list.hide_post_type'))
									<span class="post_type">
										<i class="bi bi-tag"></i> {{ data_get($post, 'postType.name') }}
									</span>
								@endif
								@if (!config('settings.listings_list.hide_salary'))
									<span class="salary">
										<i class="bi bi-cash-coin"></i>&nbsp;
										{!! data_get($post, 'salary_formatted') !!}
										@if (!empty(data_get($post, 'salaryType')))
											{{ t('per') }} {{ data_get($post, 'salaryType.name') }}
										@endif
									</span>
								@endif
							</span>
						@endif
						
						@if (!config('settings.listings_list.hide_excerpt'))
							<div class="jobs-desc">
								{!! str(multiLinesStringCleaner(data_get($post, 'description')))->limit(180) !!}
							</div>
						@endif
						
						<div class="job-actions">
							<ul class="list-unstyled list-inline">
								@php
									$savedByLoggedUser = (bool)data_get($post, 'p_saved_by_logged_user');
								@endphp
								@if (!empty($authUser))
									@if ($isJobSeekerUser)
										@if ($savedByLoggedUser)
											<li class="saved-job" id="{{ $postId }}">
												<a class="saved-job" id="saved-{{ $postId }}" href="javascript:void(0)">
													<span class="bi bi-bookmark-fill"></span> {{ t('Saved Job') }}
												</a>
											</li>
										@else
											<li id="{{ $postId }}">
												<a class="save-job" id="save-{{ $postId }}" href="javascript:void(0)">
													<span class="bi bi-bookmark"></span> {{ t('Save Job') }}
												</a>
											</li>
										@endif
									@endif
								@else
									<li id="{{ $postId }}">
										<a class="save-job" id="save-{{ $postId }}" href="javascript:void(0)">
											<span class="bi bi-bookmark"></span> {{ t('Save Job') }}
										</a>
									</li>
								@endif
								<li>
									<a class="email-job"
									   data-bs-toggle="modal"
									   data-id="{{ $postId }}"
									   href="#sendByEmail"
									   id="email-{{ $postId }}"
									>
										<i class="fa-regular fa-envelope"></i> {{ t('Email Job') }}
									</a>
								</li>
							</ul>
						</div>
	
					</div>
				</div>
			</div>
		</div>
	@endforeach
@else
	<div class="p-4" style="width: 100%;">
		@if ($isFromSearchCompany)
			{{ t('No jobs were found for this company') }}
		@else
			{{ t('no_result_refine_your_search') }}
		@endif
	</div>
@endif

@section('modal_location')
	@parent
	@include('front.layouts.inc.modal.send-by-email')
@endsection

@section('after_scripts')
	@parent
	<script>
		/* Favorites Translation */
		var lang = {
			labelSavePostSave: "{!! t('Save Job') !!}",
			labelSavePostRemove: "{{ t('Saved Job') }}",
			loginToSavePost: "{!! t('Please log in to save the Ads') !!}",
			loginToSaveSearch: "{!! t('Please log in to save your search') !!}"
		};
		
		onDocumentReady((event) => {
			/* Get Post ID */
			const emailJobEls = document.querySelectorAll('.email-job');
			emailJobEls.forEach((element) => {
				element.addEventListener('click', (e) => {
					const hiddenPostIdEl = document.querySelector('input[type=hidden][name=post_id]');
					if (hiddenPostIdEl) {
						let clickedEl = (e.target.tagName.toLowerCase() === 'i')
							? e.target.parentElement
							: e.target;
						
						hiddenPostIdEl.value = clickedEl.dataset.id;
					}
				});
			});
			
			@if (isset($errors) && $errors->any())
				@if (old('sendByEmailForm')=='1')
					{{-- Re-open the modal if error occured --}}
					const sendByEmailEl = document.getElementById('sendByEmail');
					if (sendByEmailEl) {
						let sendByEmail = new bootstrap.Modal(sendByEmailEl, {});
						sendByEmail.show();
					}
				@endif
			@endif
		})
	</script>
@endsection

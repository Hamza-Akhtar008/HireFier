@php
	$sectionOptions = $companiesOptions ?? [];
	$sectionData ??= [];
	$featuredCompanies = (array)data_get($sectionData, 'featuredCompanies');
	$companies = (array)data_get($featuredCompanies, 'companies');
	
	$hideOnMobile = (data_get($sectionOptions, 'hide_on_mobile') == '1') ? ' hidden-sm' : '';
@endphp

@if (!empty($featuredCompanies))
	@if (!empty($companies))
		@include('front.sections.spacer', ['hideOnMobile' => $hideOnMobile])
		
		<div class="container{{ $hideOnMobile }}">
			<div class="col-12 content-box layout-section">
				<div class="row row-featured row-featured-category row-featured-company">
					<div class="col-12  box-title no-border">
						<div class="inner">
							<h2>
								<span class="title-3">{!! data_get($featuredCompanies, 'title') !!}</span>
								<a class="sell-your-item" href="{{ data_get($featuredCompanies, 'link') }}">
									{{ t('View more') }}
									<i class="fa-solid fa-bars"></i>
								</a>
							</h2>
						</div>
					</div>
					<div class="col-12">
						<div class="row row-cols-lg-6 row-cols-md-4 row-cols-sm-3 row-cols-2">
							@foreach($companies as $key => $iCompany)
								<div class="col f-category">
									<a href="{{ urlGen()->company(data_get($iCompany, 'id')) }}">
										<img src="{{ data_get($iCompany, 'logo_url.medium') }}" class="img-fluid" alt="{{ data_get($iCompany, 'name') }}">
										<h6> {{ t('Jobs at') }}
											<span class="company-name">{{ data_get($iCompany, 'name') }}</span>
											<span class="jobs-count text-muted">({{ data_get($iCompany, 'posts_count') ?? 0 }})</span>
										</h6>
									</a>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif
@endif

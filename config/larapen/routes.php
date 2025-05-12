<?php
/*
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
 */

$routes = [
	
	// Post
	'post' => '{slug}/{hashableId}',
	
	// Search
	'search' => 'latest-jobs',
	'searchPostsByUserId' => 'users/{id}/jobs',
	'searchPostsByUsername' => 'profile/{username}',
	'searchPostsByTag' => 'tag/{tag}',
	'searchPostsByCity' => 'jobs/{city}/{id}',
	'searchPostsBySubCat' => 'category/{catSlug}/{subCatSlug}',
	'searchPostsByCat' => 'category/{catSlug}',
	'searchPostsByCompanyId' => 'companies/{id}/jobs',
	
	// Auth
	'login' => 'login',
	'logout' => 'logout',
	'register' => 'register',
	
	// Other Pages
	'companies' => 'companies',
	'pageBySlug' => 'page/{slug}',
	'sitemap' => 'sitemap',
	'countries' => 'countries',
	'contact' => 'contact',
	'pricing' => 'pricing',
	
];

if (isMultiCountriesUrlsEnabled()) {
	
	$routes['search'] = '{countryCode}/latest-jobs';
	$routes['searchPostsByUserId'] = '{countryCode}/users/{id}/jobs';
	$routes['searchPostsByUsername'] = '{countryCode}/profile/{username}';
	$routes['searchPostsByTag'] = '{countryCode}/tag/{tag}';
	$routes['searchPostsByCity'] = '{countryCode}/jobs/{city}/{id}';
	$routes['searchPostsBySubCat'] = '{countryCode}/category/{catSlug}/{subCatSlug}';
	$routes['searchPostsByCat'] = '{countryCode}/category/{catSlug}';
	$routes['searchPostsByCompanyId'] = '{countryCode}/companies/{id}/jobs';
	$routes['companies'] = '{countryCode}/companies';
	$routes['sitemap'] = '{countryCode}/sitemap';
	
}

// Post
$postPermalinks = (array)config('larapen.options.permalink.post');
if (in_array(config('settings.seo.listing_permalink', '{slug}/{hashableId}'), $postPermalinks)) {
	$routes['post'] = config('settings.seo.listing_permalink', '{slug}/{hashableId}') . config('settings.seo.listing_permalink_ext', '');
}

return $routes;

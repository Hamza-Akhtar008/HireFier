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

namespace App\Helpers\Services\Search;

use App\Helpers\Services\Search\Traits\Filters;
use App\Helpers\Services\Search\Traits\GroupBy;
use App\Helpers\Services\Search\Traits\Having;
use App\Helpers\Services\Search\Traits\OrderBy;
use App\Helpers\Services\Search\Traits\Relations;
use App\Helpers\Services\Search\Traits\Select;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\EntityCollection;
use App\Http\Resources\PostResource;
use App\Jobs\GenerateLogoCollectionThumbnails;
use App\Models\Company;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;

class PostQueries
{
	use Select, Relations, Filters, GroupBy, Having, OrderBy;
	
	private static bool $dbModeStrict = false;
	protected static int $cacheExpiration = 300; // 5mn (60s * 5)
	
	public mixed $country;
	public mixed $lang;
	
	// Default Inputs (op, perPage, cacheExpiration & orderBy)
	// These inputs need to have a default value
	protected array $input = [];
	
	// Pre-Search Objects
	private array $preSearch;
	public mixed $cat = null;
	public mixed $city = null;
	public mixed $citiesIds = [];
	public mixed $admin = null;
	
	// Default Columns Selected
	protected array $select = [];
	protected array $groupBy = [];
	protected array $having = [];
	protected array $orderBy = [];
	
	protected Builder $posts;
	protected string $postsTable;
	
	// 'queryStringKey' => ['name' => 'column', 'order' => 'direction']
	public array $orderByParametersFields = [];
	
	private array $webGlobalQueries = ['countryCode', 'languageCode'];
	private array $webQueriesPerController = [
		'CategoryController' => ['op', 'c', 'sc'],
		'CityController'     => ['op', 'l', 'location', 'r'],
		'TagController'      => ['op', 'tag'],
		'UserController'     => ['op', 'userId', 'username'],
		'CompanyController'  => ['op', 'companyId'],
		'SearchController'   => ['op'],
		'PostsController'    => ['op'], // Account\
	];
	
	/**
	 * PostQueries constructor.
	 *
	 * @param array $input
	 * @param array $preSearch
	 */
	public function __construct(array $input = [], array $preSearch = [])
	{
		self::$dbModeStrict = config('database.connections.' . config('database.default') . '.strict');
		
		// Input
		$this->input = $this->bindValidValuesForInput($input);
		
		// Pre-Search (category, city or admin. division)
		$this->cat = !empty($preSearch['cat']) ? $preSearch['cat'] : null;
		$this->city = !empty($preSearch['city']) ? $preSearch['city'] : null;
		$this->citiesIds = !empty($preSearch['citiesIds']) ? $preSearch['citiesIds'] : [];
		$this->admin = !empty($preSearch['admin']) ? $preSearch['admin'] : null;
		
		// Save preSearch
		$this->preSearch = $preSearch;
		
		// Init. Builder
		$this->posts = Post::query();
		$this->postsTable = (new Post())->getTable();
		
		// Add Default Select Columns
		$this->setSelect();
		
		// Relations
		$this->setRelations();
	}
	
	/**
	 * Get the results
	 *
	 * @return array
	 */
	public function fetch(): array
	{
		// Apply Requested Filters
		$this->applyFilters();
		
		// Apply Aggregation & Reorder Statements
		$this->applyGroupBy();
		$this->applyHaving();
		$this->applyOrderBy();
		
		// Get Results
		$perPage = data_get($this->input, 'perPage');
		$posts = $this->posts->paginate((int)$perPage);
		
		// Generate listings logo thumbnails
		GenerateLogoCollectionThumbnails::dispatch($posts);
		
		// Remove Distance from Request
		$this->input = $this->removeDistanceFromRequest($this->input);
		
		// If the request is made from the app's Web environment,
		// use the Web URL as the pagination's base URL
		$posts = setPaginationBaseUrl($posts);
		
		// Append request queries in the pagination links
		$query = request()->query();
		$query = collect($query)->map(fn ($item) => (is_null($item) ? '' : $item))->toArray();
		$posts->appends($query);
		
		// Get Count Results
		$count = [$posts->total()];
		
		// Wrap the listings for API calls
		$postsCollection = new EntityCollection(PostResource::class, $posts, $this->input);
		$message = ($posts->count() <= 0) ? t('no_posts_found') : null;
		$postsResult = $postsCollection->toResponse(request())->getData(true);
		
		// Add 'user' object in preSearch (If available)
		$this->preSearch['user'] = null;
		$searchBasedOnUser = (
			!empty(data_get($this->input, 'userId'))
			|| !empty(data_get($this->input, 'username'))
		);
		if ($searchBasedOnUser) {
			$this->preSearch['user'] = data_get($postsResult, 'data.0.user');
		}
		
		// Add 'company' object in preSearch (If available)
		$this->preSearch['company'] = null;
		$companyId = data_get($this->input, 'companyId');
		$searchBasedOnCompany = !empty($companyId);
		if ($searchBasedOnCompany) {
			$this->preSearch['company'] = data_get($postsResult, 'data.0.company');
			if (empty($this->preSearch['company'])) {
				// Get the Company
				$cacheId = 'company.' . $companyId;
				$company = cache()->remember($cacheId, self::$cacheExpiration, function () use ($companyId) {
					return Company::find($companyId);
				});
				
				if (empty($company)) {
					$message = t('company_not_found');
				}
				
				$this->preSearch['company'] = new CompanyResource($company, $this->input);
			}
		}
		
		$this->preSearch['tag'] = data_get($this->input, 'tag');
		
		$this->preSearch['distance'] = [
			'default' => self::$defaultDistance,
			'current' => self::$distance,
			'max'     => self::$maxDistance,
		];
		
		// Results Data
		return [
			'message'   => $message,
			'count'     => $count,
			'posts'     => $postsResult,
			'distance'  => self::$distance,
			'preSearch' => $this->preSearch,
			'tags'      => $this->getPostsTags($posts),
		];
	}
	
	/**
	 * Get found posts' tags (per page)
	 *
	 * @param $posts
	 * @return array|string|null
	 */
	private function getPostsTags($posts): array|string|null
	{
		if (!config('settings.listings_list.show_listings_tags')) {
			return null;
		}
		
		if ($posts->count() > 0) {
			$tags = [];
			foreach ($posts as $post) {
				if (!empty($post->tags)) {
					$tags = array_merge($tags, $post->tags);
				}
			}
			
			return tagCleaner($tags);
		}
		
		return null;
	}
	
	/**
	 * Bind valid values for the input's elements
	 *
	 * @param array $array
	 * @return array
	 */
	private function bindValidValuesForInput(array $array = []): array
	{
		// cacheExpiration
		$cacheExpiration = data_get($array, 'cacheExpiration');
		$cacheExpirationIsValid = !empty($cacheExpiration) && is_numeric($cacheExpiration);
		if (!$cacheExpirationIsValid) {
			$array['cacheExpiration'] = self::$cacheExpiration;
		}
		
		// op
		$array['op'] = data_get($array, 'op', 'default');
		
		// perPage
		$array['perPage'] = getNumberOfItemsPerPage('posts', data_get($array, 'perPage'));
		
		// orderBy
		// Avoid to set an arbitrary orderBy value (set value to null instead)
		// $orderBy = data_get($array, 'orderBy');
		// $array['orderBy'] = !empty($orderBy) ? $orderBy : null;
		
		return $array;
	}
}

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

namespace App\Models;

use App\Helpers\Common\Num;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\SpatieTranslatable\HasTranslations;
use App\Models\Scopes\ActiveScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\PackageTrait;
use App\Observers\PackageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([PackageObserver::class])]
#[ScopedBy([ActiveScope::class])]
class Package extends BaseModel
{
	use Crud, AppendsTrait, HasTranslations;
	use PackageTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'packages';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = [
		'period_start',
		'period_end',
		'description_array',
		'description_string',
		'price_formatted',
	];
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $guarded = ['id'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'type',
		'name',
		'short_name',
		'ribbon',
		'has_badge',
		'price',
		'currency_code',
		'promotion_time',
		'interval',
		'listings_limit',
		'expiration_time',
		'description',
		'facebook_ads_duration',
		'google_ads_duration',
		'twitter_ads_duration',
		'linkedin_ads_duration',
		'recommended',
		'active',
		'parent_id',
		'lft',
		'rgt',
		'depth',
	];
	
	/**
	 * @var array<int, string>
	 */
	public array $translatable = ['name', 'short_name', 'description'];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function country(): BelongsTo
	{
		return $this->belongsTo(Country::class, 'country_code', 'code');
	}
	
	public function currency(): BelongsTo
	{
		return $this->belongsTo(Currency::class, 'currency_code', 'code');
	}
	
	public function payments(): HasMany
	{
		return $this->hasMany(Payment::class, 'package_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopePromotion(Builder $builder): Builder
	{
		return $builder->where('type', 'promotion');
	}
	
	public function scopeSubscription(Builder $builder): Builder
	{
		return $builder->where('type', 'subscription');
	}
	
	public function scopeApplyCurrency(Builder $builder): Builder
	{
		if (config('settings.localization.local_currency_packages_activation')) {
			$builder->where('currency_code', config('country.currency'));
		}
		
		return $builder;
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function interval(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$key = 'subscription_interval_options.' . $value;
				
				return trans()->has('global.' . $key) ? t($key) : null;
			},
		);
	}
	
	protected function periodStart(): Attribute
	{
		return Attribute::make(
			get: fn () => now()->startOfDay(),
		);
	}
	
	protected function periodEnd(): Attribute
	{
		return Attribute::make(
			get: function () {
				$today = now();
				
				$intervalInDays = (int)($this->promotion_time ?? 30);
				
				$isSubsPackage = (isset($this->type) && $this->type == 'subscription');
				if ($isSubsPackage) {
					$interval = !empty($this->interval) ? $this->interval : 'month';
					$intervalInDays = 7;
					if ($interval == 'month') {
						$intervalInDays = $today->daysInMonth ?? 30;
					}
					if ($interval == 'year') {
						$intervalInDays = $today->daysInYear ?? 365;
					}
				}
				
				return $today->addDays($intervalInDays)->endOfDay();
			},
		);
	}
	
	protected function descriptionArray(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => $this->getDescriptionArray($value),
		);
	}
	
	protected function descriptionString(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (!isset($this->description_array)) {
					return null;
				}
				
				$description = '';
				
				$options = $this->description_array;
				if (is_array($options)) {
					$options = array_filter($options, function ($value) {
						return !is_null($value) && $value !== '';
					});
					$options = array_unique($options);
					if (count($options) > 0) {
						$description .= implode(". \n", $options);
					}
				}
				
				return $description;
			},
		);
	}
	
	protected function priceFormatted(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$currency = [];
				if ($this->relationLoaded('currency')) {
					if (!empty($this->currency)) {
						$currency = $this->currency->toArray();
					}
				}
				
				return Num::money($this->price, $currency);
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function getDescriptionArray($value): array
	{
		$locale = app()->getLocale();
		$isPromoPackage = (isset($this->type) && $this->type == 'promotion');
		$isSubsPackage = (isset($this->type) && $this->type == 'subscription');
		
		$description = [];
		
		// Is it a basic package?
		if (array_key_exists('price', $this->attributes) && $this->price <= 0) {
			return $this->getBasicDescriptionArray($description, $isPromoPackage, $isSubsPackage, $locale);
		}
		
		if ($isPromoPackage && isset($this->promotion_time) && $this->promotion_time > 0) {
			$description[] = trans_choice('global.duration_of_promotion',
				getPlural($this->promotion_time),
				['number' => $this->promotion_time], $locale);
		}
		
		if ($isSubsPackage && isset($this->listings_limit) && $this->listings_limit > 0) {
			$description[] = trans_choice(
				'global.subscription_listings_limit',
				getPlural($this->listings_limit),
				['number' => $this->listings_limit, 'interval' => $this->interval],
				$locale
			);
		}
		
		if ($isPromoPackage && isset($this->facebook_ads_duration) && $this->facebook_ads_duration > 0) {
			$description[] = trans_choice('global.facebook_ads_included',
				getPlural($this->facebook_ads_duration),
				['number' => $this->facebook_ads_duration], $locale);
		}
		
		if ($isPromoPackage && isset($this->google_ads_duration) && $this->google_ads_duration > 0) {
			$description[] = trans_choice('global.google_ads_included',
				getPlural($this->google_ads_duration),
				['number' => $this->google_ads_duration], $locale);
		}
		
		if ($isPromoPackage && isset($this->twitter_ads_duration) && $this->twitter_ads_duration > 0) {
			$description[] = trans_choice('global.twitter_ads_included',
				getPlural($this->twitter_ads_duration),
				['number' => $this->twitter_ads_duration], $locale);
		}
		
		if ($isPromoPackage && isset($this->linkedin_ads_duration) && $this->linkedin_ads_duration > 0) {
			$description[] = trans_choice('global.linkedin_ads_included',
				getPlural($this->linkedin_ads_duration),
				['number' => $this->linkedin_ads_duration], $locale);
		}
		
		$otherOptions = [];
		if (isset($this->description)) {
			$otherOptions = preg_split('#[\n;.]+#ui', $this->description);
			$otherOptions = array_filter($otherOptions, function ($value) {
				return !is_null($value) && $value !== '';
			});
			$otherOptions = array_unique($otherOptions);
			if (count($otherOptions) > 0) {
				foreach ($otherOptions as $option) {
					$description[] = $option;
				}
			}
		}
		
		if (isset($this->expiration_time) && $this->expiration_time > 0) {
			if ($isSubsPackage) {
				$description[] = t('subscription_listings_expiration_time', ['number' => $this->expiration_time]);
			} else {
				$description[] = t('package_listing_expiration_time', ['number' => $this->expiration_time]);
			}
		}
		
		if (
			array_key_exists('promotion_time', $this->attributes)
			&& array_key_exists('interval', $this->attributes)
			&& array_key_exists('listings_limit', $this->attributes)
			&& array_key_exists('facebook_ads_duration', $this->attributes)
			&& array_key_exists('google_ads_duration', $this->attributes)
			&& array_key_exists('twitter_ads_duration', $this->attributes)
			&& array_key_exists('linkedin_ads_duration', $this->attributes)
			&& array_key_exists('expiration_time', $this->attributes)
		) {
			if (
				$this->promotion_time <= 0
				&& empty($this->interval)
				&& $this->listings_limit <= 0
				&& $this->facebook_ads_duration <= 0
				&& $this->google_ads_duration <= 0
				&& $this->twitter_ads_duration <= 0
				&& $this->linkedin_ads_duration <= 0
				&& empty($otherOptions)
				&& $this->expiration_time <= 0
			) {
				$description[] = t(
					'package_listing_expiration_time',
					['number' => (int)config('settings.cron.activated_listings_expiration', 30)]
				);
			}
		}
		
		return $description;
	}
	
	private function getBasicDescriptionArray($description, $isPromoPackage, $isSubsPackage, $locale): array
	{
		if ($isSubsPackage) {
			$listingsLimit = config('settings.listing_form.listings_limit');
			$description[] = trans_choice(
				'global.basic_subscription_listings_limit',
				getPlural($listingsLimit),
				['number' => $listingsLimit],
				$locale
			);
		}
		
		$expirationTime = config('settings.cron.activated_listings_expiration');
		if ($isSubsPackage) {
			$description[] = t('subscription_listings_expiration_time', ['number' => $expirationTime]);
		}
		if ($isPromoPackage) {
			$description[] = t('package_listing_expiration_time', ['number' => $expirationTime]);
		}
		
		return $description;
	}
}

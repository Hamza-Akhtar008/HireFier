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

use App\Helpers\Common\Date;
use App\Helpers\Common\Files\Storage\StorageDisk;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use App\Models\Scopes\LocalizedScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\CompanyTrait;
use App\Observers\CompanyObserver;
use App\Helpers\Services\RemoveFromString;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[ObservedBy([CompanyObserver::class])]
#[ScopedBy([LocalizedScope::class])]
class Company extends BaseModel
{
	use Crud, AppendsTrait, HasFactory;
	use CompanyTrait;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'companies';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = [
		'logo_url',
		'logo_url_small',
		'logo_url_medium',
		'logo_url_large',
		'country_flag_url'
	];
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = true;
	
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
		'user_id',
		'name',
		'logo_path',
		'description',
		'country_code',
		'city_id',
		'address',
		'phone',
		'fax',
		'email',
		'website',
		'facebook',
		'twitter',
		'linkedin',
		'pinterest',
	];
	
	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/
	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];
	}
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function posts(): HasMany
	{
		return $this->hasMany(Post::class, 'company_id');
	}
	
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
	
	public function city(): BelongsTo
	{
		return $this->belongsTo(City::class, 'city_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function createdAt(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = new Carbon($value);
				$value->timezone(Date::getAppTimeZone());
				
				return $value;
			},
		);
	}
	
	protected function updatedAt(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = new Carbon($value);
				$value->timezone(Date::getAppTimeZone());
				
				return $value;
			},
		);
	}
	
	protected function email(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isAdminPanel()) {
					if (
						isDemoDomain()
						&& request()->segment(2) != 'password'
					) {
						if (auth()->check()) {
							if (auth()->user()->getAuthIdentifier() != 1) {
								$value = emailPrefixMask($value);
							}
						}
						
						return $value;
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function phone(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$countryCode = config('country.code');
				if (!empty($this->country_code)) {
					$countryCode = $this->country_code;
				}
				
				return phoneE164($value, $countryCode);
			},
		);
	}
	
	protected function name(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => mb_ucwords($value),
		);
	}
	
	protected function description(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isAdminPanel()) {
					return $value;
				}
				
				if (!empty($this->user)) {
					if (!$this->user->hasAllPermissions(Permission::getStaffPermissions())) {
						$value = RemoveFromString::contactInfo($value, false, true);
					}
				} else {
					$value = RemoveFromString::contactInfo($value, false, true);
				}
				
				return $value;
			},
		);
	}
	
	protected function website(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => addHttp($value),
		);
	}
	
	protected function logoPath(): Attribute
	{
		return Attribute::make(
			get: function ($value, $attributes) {
				if (empty($value)) {
					$value = $attributes['logo_path'] ?? null;
				}
				
				// OLD PATH
				$value = $this->getLogoFromOldPath($value);
				
				// NEW PATH
				$disk = StorageDisk::getDisk();
				if (empty($value) || !$disk->exists($value)) {
					$value = config('larapen.media.picture');
				}
				
				return $value;
			},
		);
	}
	
	protected function logoUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getLogoUrl('picture-md');
			},
		);
	}
	
	protected function logoUrlSmall(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getLogoUrl('picture-sm');
			},
		);
	}
	
	protected function logoUrlMedium(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getLogoUrl('picture-md');
			},
		);
	}
	
	protected function logoUrlLarge(): Attribute
	{
		return Attribute::make(
			get: function () {
				return $this->getLogoUrl('picture-lg');
			},
		);
	}
	
	protected function countryFlagUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				return getCountryFlagUrl($this->country_code ?? null);
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function getLogoFromOldPath($value): ?string
	{
		// Fix path
		$oldBase = 'pictures/';
		$newBase = 'files/';
		if (str_contains($value, $oldBase)) {
			$value = $newBase . last(explode($oldBase, $value));
		}
		
		return $value;
	}
	
	private function getLogoUrl($resizeOptionsName = null, bool $webpFormat = false): ?string
	{
		$filename = self::getLogo($this->logo_path ?? null);
		$resizeOptionsName = $resizeOptionsName ?? 'picture-lg';
		
		return thumbParam($filename)->setOption($resizeOptionsName)->url();
	}
}

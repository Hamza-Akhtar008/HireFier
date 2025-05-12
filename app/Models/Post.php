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

use App\Casts\DateTimeCast;
use App\Casts\EmailCast;
use App\Casts\NullableDateTimeCast;
use App\Casts\PhoneCast;
use App\Casts\PhoneCountryCast;
use App\Casts\PhoneNationalCast;
use App\Helpers\Common\Date;
use App\Helpers\Common\Files\Storage\StorageDisk;
use App\Helpers\Common\Num;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use App\Models\Post\SimilarByCategory;
use App\Models\Post\SimilarByLocation;
use App\Models\Scopes\LocalizedScope;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\StrictActiveScope;
use App\Models\Scopes\ValidPeriodScope;
use App\Models\Scopes\VerifiedScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Models\Traits\Common\HasCountryCodeColumn;
use App\Models\Traits\PostTrait;
use App\Observers\PostObserver;
use App\Helpers\Services\RemoveFromString;
use App\Services\Auth\App\Models\Verifiable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\Feed\Feedable;

#[ObservedBy([PostObserver::class])]
#[ScopedBy([VerifiedScope::class, ReviewedScope::class, LocalizedScope::class])]
class Post extends BaseModel implements Feedable
{
	use Crud, AppendsTrait, HasCountryCodeColumn, Notifiable, HasFactory;
	use Verifiable;
	use PostTrait;
	use SimilarByCategory, SimilarByLocation;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';
	
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = [
		'reference',
		'slug',
		'url',
		'excerpt',
		'phone_intl',
		'created_at_formatted',
		'updated_at_formatted',
		'archived_at_formatted',
		'archived_manually_at_formatted',
		'logo_url',
		'logo_url_small',
		'logo_url_medium',
		'logo_url_large',
		'user_photo_url',
		'country_flag_url',
		'salary_formatted',
		'visits_formatted',
		'distance_info',
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
		'country_code',
		'user_id',
		'payment_id',
		'company_id',
		'company_name',
		'logo_path',
		'company_description',
		'category_id',
		'post_type_id',
		'title',
		'description',
		'tags',
		'salary_min',
		'salary_max',
		'salary_type_id',
		'currency_code',
		'negotiable',
		'start_date',
		'application_url',
		'contact_name',
		'address',
		'city_id',
		'lat',
		'lon',
		'create_from_ip',
		'latest_update_ip',
		'visits',
		
		'auth_field',
		'email',
		'phone',
		'phone_national',
		'phone_country',
		'email_token',
		'phone_token',
		'email_verified_at',
		'phone_verified_at',
		
		'otp_expires_at',
		'last_otp_sent_at',
		'otp_resend_attempts',
		'otp_resend_attempts_expires_at',
		'total_otp_resend_attempts',
		'locked_at',
		
		'phone_hidden',
		'accept_terms',
		'accept_marketing_offers',
		'featured',
		'tmp_token',
		'reviewed_at',
		'archived_at',
		'archived_manually_at',
		'deletion_mail_sent_at',
		'created_at',
		'updated_at',
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
		$casts = [
			'email'                 => EmailCast::class,
			'phone_country'         => PhoneCountryCast::class,
			'phone'                 => PhoneCast::class,
			'phone_national'        => PhoneNationalCast::class,
			'email_verified_at'     => NullableDateTimeCast::class,
			'phone_verified_at'     => NullableDateTimeCast::class,
			'created_at'            => DateTimeCast::class,
			'updated_at'            => DateTimeCast::class,
			'deleted_at'            => NullableDateTimeCast::class,
			'reviewed_at'           => NullableDateTimeCast::class,
			'archived_at'           => NullableDateTimeCast::class,
			'archived_manually_at'  => NullableDateTimeCast::class,
			'deletion_mail_sent_at' => NullableDateTimeCast::class,
		];
		
		if (Schema::hasColumn($this->table, 'otp_expires_at')) {
			$casts = array_merge($casts, [
				'otp_expires_at'                 => NullableDateTimeCast::class,
				'last_otp_sent_at'               => NullableDateTimeCast::class,
				'otp_resend_attempts'            => 'integer',
				'otp_resend_attempts_expires_at' => NullableDateTimeCast::class,
				'total_otp_resend_attempts'      => 'integer',
				'locked_at'                      => NullableDateTimeCast::class,
			]);
		}
		
		return $casts;
	}
	
	public function routeNotificationForMail()
	{
		return $this->email;
	}
	
	public function routeNotificationForVonage()
	{
		$phone = phoneE164($this->phone, $this->phone_country);
		
		return setPhoneSign($phone, 'vonage');
	}
	
	public function routeNotificationForTwilio()
	{
		$phone = phoneE164($this->phone, $this->phone_country);
		
		return setPhoneSign($phone, 'twilio');
	}
	
	/*
	|--------------------------------------------------------------------------
	| QUERIES
	|--------------------------------------------------------------------------
	*/
	
	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/
	public function postType(): BelongsTo
	{
		return $this->belongsTo(PostType::class, 'post_type_id');
	}
	
	public function category(): BelongsTo
	{
		return $this->belongsTo(Category::class, 'category_id');
	}
	
	public function city(): BelongsTo
	{
		return $this->belongsTo(City::class, 'city_id');
	}
	
	public function currency(): BelongsTo
	{
		return $this->belongsTo(Currency::class, 'currency_code', 'code');
	}
	
	/*
	 * The first valid payment (Covers the validity period).
	 * Its activation needs to be checked programmably (if needed).
	 * NOTE: By sorting the ID by ASC, allows the system to use the first valid payment as the current one.
	 */
	public function possiblePayment(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')->withoutGlobalScope(StrictActiveScope::class)->orderBy('id');
	}
	
	/*
	 * The first valid & active payment (Covers the validity period & is active)
	 * NOTE: By sorting the ID by ASC, allows the system to use the first valid payment as the current one.
	 */
	public function payment(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')->orderBy('id');
	}
	
	/*
	 * The first valid & active payment that is manually created
	 * NOTE: Used in the ListingsPurge command in cron job
	 */
	public function paymentNotManuallyCreated(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')->notManuallyCreated()->orderBy('id');
	}
	
	/*
	 * The ending later valid (or on hold) active payment (Covers the validity period & is active)
	 * This is useful to calculate the starting period to allow payable to have multiple valid & active payments
	 */
	public function paymentEndingLater(): MorphOne
	{
		return $this->morphOne(Payment::class, 'payable')
			->withoutGlobalScope(ValidPeriodScope::class)
			->where(function ($q) {
				$q->where(fn ($q) => $q->valid())->orWhere(fn ($q) => $q->onHold());
			})
			->orderByDesc('period_end');
	}
	
	/*
	 * Get all the listing payments
	 */
	public function payments(): MorphMany
	{
		return $this->morphMany(Payment::class, 'payable');
	}
	
	/*
	 * Get the first picture of the listing (as main picture)
	 */
	public function picture(): HasOne
	{
		return $this->hasOne(Picture::class, 'post_id')->orderBy('position')->orderByDesc('id');
	}
	
	/*
	 * Get all the listing pictures
	 */
	public function pictures(): HasMany
	{
		return $this->hasMany(Picture::class, 'post_id')->orderBy('position')->orderByDesc('id');
	}
	
	public function savedByLoggedUser(): HasOne
	{
		$userId = auth(getAuthGuard())->user()?->getAuthIdentifier() ?? '-1';
		
		return $this->hasOne(SavedPost::class, 'post_id')->where('user_id', $userId);
	}
	
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
	
	public function company(): BelongsTo
	{
		return $this->belongsTo(Company::class, 'company_id');
	}
	
	public function salaryType(): BelongsTo
	{
		return $this->belongsTo(SalaryType::class, 'salary_type_id');
	}
	
	public function subscription(): BelongsTo
	{
		return $this->belongsTo(Payment::class, 'payment_id');
	}
	
	/*
	|--------------------------------------------------------------------------
	| SCOPES
	|--------------------------------------------------------------------------
	*/
	public function scopeVerified(Builder $builder): Builder
	{
		$builder->where(function (Builder $query) {
			$query->whereNotNull('email_verified_at')->whereNotNull('phone_verified_at');
		});
		
		if (config('settings.listing_form.listings_review_activation') == '1') {
			$builder->whereNotNull('reviewed_at');
		}
		
		return $builder;
	}
	
	public function scopeUnverified(Builder $builder): Builder
	{
		$builder->where(function (Builder $query) {
			$query->whereNull('email_verified_at')->orWhereNull('phone_verified_at');
		});
		
		if (config('settings.listing_form.listings_review_activation') == '1') {
			$builder->orWhereNull('reviewed_at');
		}
		
		return $builder;
	}
	
	public function scopeArchived(Builder $builder): Builder
	{
		return $builder->whereNotNull('archived_at');
	}
	
	public function scopeUnarchived(Builder $builder): Builder
	{
		return $builder->whereNull('archived_at');
	}
	
	public function scopeReviewed(Builder $builder): Builder
	{
		if (config('settings.listing_form.listings_review_activation') == '1') {
			return $builder->whereNotNull('reviewed_at');
		} else {
			return $builder;
		}
	}
	
	public function scopeUnreviewed(Builder $builder): Builder
	{
		if (config('settings.listing_form.listings_review_activation') == '1') {
			return $builder->whereNull('reviewed_at');
		} else {
			return $builder;
		}
	}
	
	/*
	 * Display the unreviewed entries first
	 * ->orderByUnreviewedFirst()
	 */
	public function scopeOrderByUnreviewedFirst(Builder $builder): Builder
	{
		if (config('settings.listing_form.listings_review_activation') == '1') {
			return $builder->orderByRaw('CASE WHEN reviewed_at IS NULL THEN 0 ELSE 1 END ASC');
		}
		
		return $builder;
	}
	
	public function scopeWithCountryFix(Builder $builder): Builder
	{
		// Check the Domain Mapping plugin
		if (config('plugins.domainmapping.installed')) {
			return $builder->where('country_code', '=', config('country.code'));
		} else {
			return $builder;
		}
	}
	
	/*
	|--------------------------------------------------------------------------
	| ACCESSORS | MUTATORS
	|--------------------------------------------------------------------------
	*/
	protected function reference(): Attribute
	{
		return Attribute::make(
			get: function () {
				$value = $this->id ?? null;
				if (empty($value)) return $value;
				
				return hashId($value, false, false);
			},
		);
	}
	
	protected function visitsFormatted(): Attribute
	{
		return Attribute::make(
			get: function () {
				$number = (int)($this->visits ?? 0);
				$shortNumber = Num::short($number);
				
				$value = $shortNumber;
				$value .= ' ';
				$value .= trans_choice('global.count_views', getPlural($number), [], config('app.locale'));
				
				return $value;
			},
		);
	}
	
	protected function createdAtFormatted(): Attribute
	{
		return Attribute::make(
			get: fn () => Date::customFromNow($this->created_at ?? null),
		);
	}
	
	protected function updatedAtFormatted(): Attribute
	{
		return Attribute::make(
			get: fn () => Date::format($this->updated_at ?? null, 'datetime'),
		);
	}
	
	protected function archivedAtFormatted(): Attribute
	{
		return Attribute::make(
			get: fn () => Date::format($this->archived_at ?? null, 'datetime'),
		);
	}
	
	protected function archivedManuallyAtFormatted(): Attribute
	{
		return Attribute::make(
			get: fn () => Date::format($this->archived_manually_at ?? null, 'datetime'),
		);
	}
	
	protected function userPhotoUrl(): Attribute
	{
		return Attribute::make(
			get: function () {
				// Default Photo
				$defaultPhotoUrl = thumbParam(config('larapen.media.avatar'))->url();
				
				// If the relation is not loaded through the Eloquent 'with()' method,
				// then don't make additional query (to prevent performance issues).
				if (!$this->relationLoaded('user')) {
					return $defaultPhotoUrl;
				}
				
				$photoUrl = $this->user?->photo_url ?? null;
				
				return !empty($photoUrl) ? $photoUrl : $defaultPhotoUrl;
			},
		);
	}
	
	protected function phoneIntl(): Attribute
	{
		return Attribute::make(
			get: function () {
				$phoneCountry = $this->phone_country ?? config('country.code');
				$phone = $this->phone ?? null;
				
				$value = !empty($this->phone_national) ? $this->phone_national : $phone;
				
				if ($phoneCountry == config('country.code')) {
					return phoneNational($value, $phoneCountry);
				}
				
				return phoneIntl($value, $phoneCountry);
			},
		);
	}
	
	protected function title(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = mb_ucfirst($value);
				$cleanedValue = RemoveFromString::contactInfo($value, false, true);
				
				if (!$this->relationLoaded('user')) {
					return $cleanedValue;
				}
				
				if (!isAdminPanel()) {
					if (!empty($this->user)) {
						if (!$this->user->hasAllPermissions(Permission::getStaffPermissions())) {
							$value = $cleanedValue;
						}
					} else {
						$value = $cleanedValue;
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function slug(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				$value = (is_null($value) && isset($this->title)) ? $this->title : $value;
				
				$value = stripNonAsciiAndExtendedChars($value);
				$value = slugify($value);
				
				// To prevent 404 error when the slug starts by a banned slug/prefix,
				// Add a tilde (~) as prefix to it.
				$bannedSlugs = regexSimilarRoutesPrefixes();
				foreach ($bannedSlugs as $bannedSlug) {
					if (str_starts_with($value, $bannedSlug)) {
						$value = '~' . $value;
						break;
					}
				}
				
				return $value;
			},
		);
	}
	
	/*
	 * For API calls, to allow listing sharing
	 */
	protected function url(): Attribute
	{
		return Attribute::make(
			get: function () {
				if (isset($this->id) && isset($this->title)) {
					$path = str_replace(
						['{slug}', '{hashableId}', '{id}'],
						[$this->slug, hashId($this->id), $this->id],
						config('routes.post')
					);
				} else {
					$path = '#';
				}
				
				if (config('plugins.domainmapping.installed')) {
					$url = dmUrl($this->country_code, $path);
				} else {
					$url = url($path);
				}
				
				return $url;
			},
		);
	}
	
	protected function contactName(): Attribute
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
				
				$cleanedValue = RemoveFromString::contactInfo($value, false, true);
				
				if (!$this->relationLoaded('user')) {
					$value = $cleanedValue;
				} else {
					if (!empty($this->user)) {
						if (!$this->user->hasAllPermissions(Permission::getStaffPermissions())) {
							$value = $cleanedValue;
						}
					} else {
						$value = $cleanedValue;
					}
				}
				
				return htmlPurifierCleaner($value);
			},
		);
	}
	
	protected function excerpt(): Attribute
	{
		return Attribute::make(
			get: function () {
				$value = $this->description ?? '';
				$value = stripUtf8mb4Chars($value);
				$value = singleLineStringCleaner($value);
				
				return str($value)->limit(100)->toString();
			},
		);
	}
	
	protected function tags(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => tagCleaner($value, true),
			set: function ($value) {
				if (is_array($value)) {
					$value = implode(',', $value);
				}
				
				return !empty($value) ? mb_strtolower($value) : $value;
			},
		);
	}
	
	protected function countryFlagUrl(): Attribute
	{
		return Attribute::make(
			get: fn () => getCountryFlagUrl($this->country_code ?? null),
		);
	}
	
	protected function companyName(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => mb_ucwords($value),
		);
	}
	
	protected function companyDescription(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				if (isAdminPanel()) {
					return $value;
				}
				
				$cleanedValue = RemoveFromString::contactInfo($value, false, true);
				
				if (!$this->relationLoaded('user')) {
					$value = $cleanedValue;
				} else {
					if (!empty($this->user)) {
						if (!$this->user->hasAllPermissions(Permission::getStaffPermissions())) {
							$value = $cleanedValue;
						}
					} else {
						$value = $cleanedValue;
					}
				}
				
				$transformedValue = nl2br(urlsToLinks(multiLinesStringCleaner($value)));
				$apiValue = (doesRequestIsFromWebClient()) ? $transformedValue : normalizeWhitespace(strip_tags($value));
				
				return isFromApi() ? $apiValue : $value;
			},
		);
	}
	
	protected function logoPath(): Attribute
	{
		return Attribute::make(
			get: function ($value, $attributes) {
				$value = !empty($value) ? $value : ($attributes['logo_path'] ?? null);
				
				// OLD PATH
				$value = $this->getLogoFromOldPath($value);
				
				$defaultPicture = config('larapen.media.picture');
				if (empty($value)) return $defaultPicture;
				
				// NEW PATH
				$disk = StorageDisk::getDisk();
				if (!$disk->exists($value)) return $defaultPicture;
				
				return $value;
			},
			set: fn ($value) => $this->setLogo($value),
		);
	}
	
	protected function logoUrl(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->getLogoUrl('picture-md'),
		);
	}
	
	protected function logoUrlSmall(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->getLogoUrl('picture-sm'),
		);
	}
	
	protected function logoUrlMedium(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->getLogoUrl('picture-md'),
		);
	}
	
	protected function logoUrlLarge(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->getLogoUrl('picture-lg'),
		);
	}
	
	protected function salaryFormatted(): Attribute
	{
		return Attribute::make(
			get: function () {
				$salaryMin = $this->salary_min ?? 0;
				$salaryMax = $this->salary_max ?? 0;
				
				// Relation with Currency
				$currency = [];
				if ($this->relationLoaded('currency')) {
					if (!empty($this->currency)) {
						$currency = $this->currency->toArray();
					}
				}
				
				if ($salaryMin > 0 || $salaryMax > 0) {
					$valueMin = ($salaryMin > 0) ? Num::money($salaryMin, $currency) : '';
					$valueMax = ($salaryMax > 0) ? Num::money($salaryMax, $currency) : '';
					
					$value = ($salaryMax > 0)
						? (($salaryMin > 0) ? $valueMin . ' - ' . $valueMax : $valueMax)
						: $valueMin;
				} else {
					$value = Num::money('--', $currency);
				}
				
				return $value;
			},
		);
	}
	
	protected function distanceInfo(): Attribute
	{
		return Attribute::make(
			get: function () {
				if (!$this->relationLoaded('city')) {
					return null;
				}
				
				if (!isset($this->distance)) {
					return null;
				}
				
				if (!is_numeric($this->distance)) {
					return null;
				}
				
				return round($this->distance, 2) . getDistanceUnit();
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function setLogo($value): ?string
	{
		// Don't make an upload for Post->logo_path for logged users
		if (!str_contains(currentRouteAction(), 'Admin\PostController')) {
			$guard = getAuthGuard();
			if (auth($guard)->check()) return $value;
		}
		
		$value = getAsString($value);
		
		if ($value == url('/')) {
			return null;
		}
		
		// Retrieve current value without upload a new file
		if (str_starts_with($value, config('larapen.media.logo'))) {
			return null;
		}
		
		// Extract the value's country code
		$matches = [];
		preg_match('#files/([A-Za-z]{2})/\d+#i', $value, $matches);
		$valueCountryCode = !empty($matches[1]) ? $matches[1] : null;
		
		// Extract the value's ID
		$matches = [];
		preg_match('#files/[A-Za-z]{2}/(\d+)#i', $value, $matches);
		$valueId = !empty($matches[1]) ? $matches[1] : null;
		
		// Extract the value's filename
		$matches = [];
		preg_match('#files/[A-Za-z]{2}/\d+/(.+)#i', $value, $matches);
		$valueFilename = !empty($matches[1]) ? $matches[1] : null;
		
		// Destination Path
		if (empty($this->id) || empty($this->country_code)) {
			return null;
		}
		$destPath = 'files/' . strtolower($this->country_code) . '/' . $this->id;
		
		if (!empty($valueCountryCode) && !empty($valueId) && !empty($valueFilename)) {
			// Value's Path
			$valueDestinationPath = 'files/' . strtolower($valueCountryCode) . '/' . $valueId;
			if ($valueDestinationPath != $destPath) {
				$oldFilePath = $valueDestinationPath . '/' . $valueFilename;
				$newFilePath = $destPath . '/' . $valueFilename;
				
				// Copy the file
				$disk = StorageDisk::getDisk();
				$disk->copy($oldFilePath, $newFilePath);
				
				return $newFilePath;
			}
		}
		
		if (!str_starts_with($value, 'files/')) {
			$value = $destPath . last(explode($destPath, $value));
		}
		
		return $value;
	}
	
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

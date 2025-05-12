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

use App\Helpers\Common\Files\Storage\StorageDisk;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\LocalizedScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Observers\ResumeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([ResumeObserver::class])]
#[ScopedBy([ActiveScope::class, LocalizedScope::class])]
class Resume extends BaseModel
{
	use Crud, AppendsTrait, HasFactory;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'resumes';
	
	/**
	 * @var array<int, string>
	 */
	protected $appends = ['country_flag_url'];
	
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
		'name',
		'file_path',
		'active',
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
		return $this->hasMany(Post::class);
	}
	
	public function user(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'user_id', 'id');
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
	protected function name(): Attribute
	{
		return Attribute::make(
			get: function ($value, $attributes) {
				if (empty($value)) {
					if (isset($attributes['name'])) {
						$value = $attributes['name'];
					}
				}
				
				if (!empty($value)) {
					$value = last(explode('/', $value));
				}
				
				if (empty($value)) {
					if (!empty($this->file_path) && is_string($this->file_path)) {
						$value = last(explode('/', $this->file_path));
					}
				}
				
				return $value;
			},
		);
	}
	
	protected function filePath(): Attribute
	{
		return Attribute::make(
			get: function ($value, $attributes) {
				if (empty($value)) {
					if (isset($attributes['file_path'])) {
						$value = $attributes['file_path'];
					}
				}
				
				// OLD PATH
				$value = $this->getFilePathFromOldPath($value);
				
				// NEW PATH
				$pDisk = StorageDisk::getDisk('private');
				if (empty($value) || !$pDisk->exists($value)) {
					return null;
				}
				
				return $value;
			},
		);
	}
	
	protected function countryFlagUrl(): Attribute
	{
		return Attribute::make(
			get: function ($value) {
				return getCountryFlagUrl($this->country_code);
			},
		);
	}
	
	/*
	|--------------------------------------------------------------------------
	| OTHER PRIVATE METHODS
	|--------------------------------------------------------------------------
	*/
	private function getFilePathFromOldPath($value): ?string
	{
		// Fix path
		$oldBase = 'resumes/'; // ../path/to/resumes/
		$newBase = 'resumes/';
		if (str_contains($value, $oldBase)) {
			$value = $newBase . last(explode($oldBase, $value));
		}
		
		return $value;
	}
}

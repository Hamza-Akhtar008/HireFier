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

namespace App\Http\Resources;

use App\Helpers\Common\Files\Storage\StorageDisk;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class EntityCollection extends ResourceCollection
{
	protected string $resourceClass;
	protected array $params;
	
	/**
	 * EntityCollection constructor.
	 *
	 * @param $serviceName
	 * @param $resource
	 * @param array $params
	 */
	public function __construct($serviceName, $resource, array $params = [])
	{
		parent::__construct($resource);
		
		$this->params = $params;
		
		// Get the service short class name & the remaining namespace
		$serviceClassName = class_basename($serviceName);
		$serviceRemainingNamespace = str($serviceName)->replaceLast($serviceClassName, '')->toString();
		
		// Get the service resource class name
		$resourceClassName = str($serviceClassName)->replaceLast('Service', 'Resource')->toString();
		if (!str_ends_with($resourceClassName, 'Resource')) {
			$resourceClassName = str($serviceClassName)->replaceLast('Controller', 'Resource')->toString();
		}
		
		// Get the service resource full qualified class name
		if (!str_starts_with($resourceClassName, '\\')) {
			$this->resourceClass = $serviceRemainingNamespace . $resourceClassName;
			if (!class_exists($this->resourceClass)) {
				$this->resourceClass = '\\' . __NAMESPACE__ . '\\' . $resourceClassName;
			}
		} else {
			$this->resourceClass = $resourceClassName;
		}
	}
	
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param bool $unWrapped
	 * @return array
	 */
	public function toArray(Request $request, bool $unWrapped = false): array
	{
		if (empty($this->collection) || !($this->collection instanceof Collection)) {
			return [];
		}
		
		$collection = $this->collection->transform(function ($resource) {
			return new $this->resourceClass($resource, $this->params);
		});
		
		// ResumeCollection
		if (str_ends_with($this->resourceClass, 'ResumeResource')) {
			$collection = $this->applyResumeFilters($collection);
		}
		
		if ($unWrapped) {
			return $collection->toArray();
		}
		
		return [
			'data' => $collection,
		];
	}
	
	// PRIVATE
	
	/**
	 * @param \Illuminate\Support\Collection $collection
	 * @return \Illuminate\Support\Collection
	 */
	private function applyResumeFilters(Collection $collection): Collection
	{
		$isForApplyingJob = getIntAsBoolean($this->params['forApplyingJob'] ?? 0);
		
		// If the resumes list is for selection to apply to a job, make sure that the attached file exists
		if ($isForApplyingJob) {
			$pDisk = StorageDisk::getDisk('private');
			$limit = config('larapen.core.selectResumeInto', 5);
			
			$collection = $collection->reject(function ($entity) use ($pDisk) {
				$fileExists = (!empty($entity['file_path']) && $pDisk->exists($entity['file_path']));
				
				return !$fileExists;
			})->take($limit);
		}
		
		return $collection;
	}
}

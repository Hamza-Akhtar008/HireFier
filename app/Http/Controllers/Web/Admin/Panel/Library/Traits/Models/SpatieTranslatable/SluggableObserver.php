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

namespace App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\SpatieTranslatable;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;

class SluggableObserver extends \Cviebrock\EloquentSluggable\SluggableObserver
{
	/**
	 * @var \Cviebrock\EloquentSluggable\Services\SlugService
	 */
	private $slugService;
	
	/**
	 * @var \Illuminate\Contracts\Events\Dispatcher
	 */
	private $events;
	
	/**
	 * SluggableObserver constructor.
	 *
	 * @param \Cviebrock\EloquentSluggable\Services\SlugService $slugService
	 * @param \Illuminate\Contracts\Events\Dispatcher           $events
	 */
	public function __construct(SlugService $slugService, Dispatcher $events)
	{
		$this->slugService = $slugService;
		$this->events = $events;
	}
	
	/**
	 * @param \Illuminate\Database\Eloquent\Model $model
	 *
	 * @return bool|null
	 */
	public function saving(Model $model)
	{
		return $this->generateSlug($model, 'saving');
	}
	
	/**
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param string                              $event
	 *
	 * @return bool|null
	 */
	protected function generateSlug(Model $model, string $event)
	{
		// If the "slugging" event returns a value, abort
		if ($this->fireSluggingEvent($model, $event) !== null) {
			return;
		}
		$wasSlugged = $this->slugService->slug($model);
		
		$this->fireSluggedEvent($model, $wasSlugged);
	}
	
	/**
	 * Fire the namespaced validating event.
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param string                              $event
	 *
	 * @return mixed
	 */
	protected function fireSluggingEvent(Model $model, string $event)
	{
		return $this->events->until('eloquent.slugging: '.get_class($model), [$model, $event]);
	}
	
	/**
	 * Fire the namespaced post-validation event.
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param string                              $status
	 *
	 * @return void
	 */
	protected function fireSluggedEvent(Model $model, string $status)
	{
		$this->events->dispatch('eloquent.slugged: '.get_class($model), [$model, $status]);
	}
}

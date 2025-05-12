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

namespace App\Helpers\Services\Thumbnail;

use App\Models\Company;
use App\Models\Post;

class LogoThumbnail
{
	/**
	 * @param \App\Models\Company|\App\Models\Post|null $entry
	 * @return void
	 */
	public function generateFor(Company|Post|null $entry): void
	{
		if (empty($entry)) return;
		
		$this->generateThumbnail($entry);
	}
	
	/**
	 * @param $entries
	 * @return void
	 */
	public function generateForCollection($entries): void
	{
		if (empty($entries)) return;
		
		foreach ($entries as $entry) {
			if (empty($entry)) continue;
			if (!$entry instanceof Company && !$entry instanceof Post) continue;
			
			$this->generateThumbnail($entry);
		}
	}
	
	/**
	 * @param \App\Models\Company|\App\Models\Post $entry
	 * @return void
	 */
	protected function generateThumbnail(Company|Post $entry): void
	{
		$columnsWithParams = $this->getModelThumbnailColumns($entry);
		if (empty($columnsWithParams)) return;
		
		foreach ($columnsWithParams as $columnParams) {
			$filePath = $columnParams['filePath'] ?? null;
			$webpFormat = $columnParams['webpFormat'] ?? false;
			
			thumbImage($filePath)->resize($columnParams, $webpFormat);
		}
	}
	
	/**
	 * Get the model's thumbnail columns and their parameters
	 *
	 * @param \App\Models\Company|\App\Models\Post $entry
	 * @return array
	 */
	protected function getModelThumbnailColumns(Company|Post $entry): array
	{
		$appendedColumns = $entry->getAppends();
		
		$attribute = 'logo_path';
		
		$appendedColumns = collect($appendedColumns)
			->filter(function ($item) use ($attribute) {
				$attrBase = str($attribute)->replaceEnd('_path', '')->toString();
				
				return str_contains($item, $attrBase . '_url');
			})->mapWithKeys(function ($item, $key) use ($entry, $attribute) {
				$filePath = $entry->{$attribute} ?? null;
				
				$defaultResizeOptionName = 'picture-lg';
				$optionAlias = [
					'small'  => 'picture-sm',
					'medium' => 'picture-md',
					'large'  => 'picture-lg',
				];
				
				$resizeOptionAlias = str($item)->afterLast('_')->toString();
				$resizeOptionAlias = ($resizeOptionAlias != 'url') ? $resizeOptionAlias : null;
				$resizeOptionsName = $optionAlias[$resizeOptionAlias] ?? $defaultResizeOptionName;
				
				$webp = str($item)->before('_')->toString();
				$webpFormat = ($webp == 'webp');
				
				$params = thumbParam($filePath)->setOption($resizeOptionsName)->resizeParameters();
				$params['webpFormat'] = $webpFormat;
				
				return [$key => $params];
			})->toArray();
		
		return collect($appendedColumns)->toArray();
	}
}

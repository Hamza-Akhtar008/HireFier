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

namespace App\Http\Controllers\Web\Admin\Panel\Traits;

use App\Helpers\Common\HierarchicalData\Library\AdjacentToNested;
use App\Models\Category;

trait Reorder
{
	/**
	 * Reorder the items in the database using the Nested Set pattern.
	 * Database columns needed: id, parent_id, lft, rgt, depth, name/title
	 *
	 * @param null $parentId
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 */
	public function reorder($parentId = null)
	{
		$this->xPanel->hasAccessOrFail('reorder');
		
		// Get all results for that entity
		$this->data['entries'] = $this->xPanel->getEntries();
		$this->data['parent_id'] = $this->parentId;
		
		$this->data['xPanel'] = $this->xPanel;
		$this->data['title'] = trans('admin.reorder') . ' ' . $this->xPanel->entityName;
		
		return view('admin.panel.reorder', $this->data);
	}
	
	/**
	 * Save the new order, using the Nested Set pattern.
	 *
	 * Database columns needed: id, parent_id, lft, rgt, depth, name/title
	 *
	 * @return bool|string
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	public function saveReorder(): bool|string
	{
		// if reorder_table_permission is false, abort
		$this->xPanel->hasAccessOrFail('reorder');
		
		$model = $this->xPanel->model;
		$count = 0;
		$allEntries = request()->input('tree');
		
		$table = null;
		if (is_array($allEntries) && count($allEntries)) {
			foreach ($allEntries as $key => $entry) {
				if ($entry['item_id'] != '' && $entry['item_id'] != null) {
					$entry['parent_id'] = $this->parentId;
					$modelPrimaryKey = (new $model)->getKeyName();
					$items = $model::where($modelPrimaryKey, $entry['item_id'])->get();
					if ($items->count() > 0) {
						foreach ($items as $item) {
							if (empty($table)) {
								$table = $item->getTable();
							}
							if ($item->getConnection()->getSchemaBuilder()->hasColumn($table, 'parent_id')) {
								$item->parent_id = $entry['parent_id'];
							}
							
							$item->lft = $entry['left'];
							$item->rgt = $entry['right'];
							$item->save();
						}
					} else {
						break;
					}
					
					$count++;
				}
			}
			
			// Rebuild Categories Nodes
			if ($table == (new Category())->getTable()) {
				$this->rebuildCategoriesNodes($table);
			}
		} else {
			return false;
		}
		
		return getAsString(trans('admin.reorder_success_for_x_items', ['count' => $count]));
	}
	
	/**
	 * Rebuild Categories Nodes
	 *
	 * Convert Adjacent List to Nested Set
	 * NOTE:
	 * - The items order is saved, using the 'lft' column value order
	 * - Need to use adjacent list model to add, update or delete nodes
	 *
	 * @param $table
	 * @throws \App\Exceptions\Custom\CustomException
	 * @todo: Not optimal. Find a better way to do it.
	 *
	 */
	protected function rebuildCategoriesNodes($table): void
	{
		$params = [
			'adjacentTable' => $table,
			'nestedTable'   => $table,
		];
		
		$transformer = new AdjacentToNested($params);
		$transformer->ordered = true;
		
		$transformer->getAndSetAdjacentItemsIds();
		$transformer->convertChildrenRecursively(0);
		$transformer->setNodesDepth();
	}
}

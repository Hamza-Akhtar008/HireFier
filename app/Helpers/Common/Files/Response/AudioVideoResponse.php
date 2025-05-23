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

namespace App\Helpers\Common\Files\Response;

use Illuminate\Filesystem\FilesystemAdapter;

class AudioVideoResponse
{
	/**
	 * Stream specified video/audio file.
	 *
	 * @param $disk
	 * @param string|null $filePath
	 * @return void
	 * @throws \League\Flysystem\FilesystemException
	 */
	public static function create($disk, ?string $filePath)
	{
		if (!$disk instanceof FilesystemAdapter) {
			abort(500);
		}
		
		if (empty($filePath) || !$disk->exists($filePath)) {
			abort(404);
		}
		
		$mime = $disk->mimeType($filePath);
		$size = $disk->size($filePath);
		$time = date('r', $disk->lastModified($filePath));
		$fm = $disk->getDriver()->readStream($filePath);
		$begin = 0;
		$end = $size - 1;
		$filename = last(explode(DIRECTORY_SEPARATOR, $filePath));
		
		if (isset($_SERVER['HTTP_RANGE'])) {
			if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
				$begin = intval($matches[1]);
				if (!empty($matches[2])) {
					$end = intval($matches[2]);
				}
			}
		}
		
		if (isset($_SERVER['HTTP_RANGE'])) {
			header('HTTP/1.1 206 Partial Content');
		} else {
			header('HTTP/1.1 200 OK');
		}
		
		header("Content-Type: $mime");
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Accept-Ranges: bytes');
		header('Content-Length:' . (($end - $begin) + 1));
		if (isset($_SERVER['HTTP_RANGE'])) {
			header("Content-Range: bytes $begin-$end/$size");
		}
		header("Content-Disposition: inline; filename={$filename}");
		header("Content-Transfer-Encoding: binary");
		header("Last-Modified: $time");
		
		$cur = $begin;
		fseek($fm, $begin, 0);
		
		while (!feof($fm) && $cur <= $end && (connection_status() == 0)) {
			print fread($fm, min(1024 * 16, ($end - $cur) + 1));
			$cur += 1024 * 16;
		}
	}
}

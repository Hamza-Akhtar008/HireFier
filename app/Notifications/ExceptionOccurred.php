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

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ExceptionOccurred extends BaseNotification
{
	public array $content;
	
	public function __construct(array $content)
	{
		$this->content = $content;
	}
	
	protected function determineViaChannels($notifiable): array
	{
		return ['mail'];
	}
	
	public function toMail($notifiable): MailMessage
	{
		$default = '--';
		$errorMessage = !empty($this->content['message']) ? $this->content['message'] : $default;
		$file = !empty($this->content['file']) ? $this->content['file'] : $default;
		$line = !empty($this->content['line']) ? $this->content['line'] : $default;
		$ipLink = (isset($this->content['ip'])) ? config('larapen.core.ipLinkBase') . $this->content['ip'] : $default;
		$method = !empty($this->content['method']) ? $this->content['method'] : $default;
		$endpoint = !empty($this->content['endpoint']) ? $this->content['endpoint'] : $default;
		$url = !empty($this->content['url']) ? $this->content['url'] : $default;
		$userAgent = !empty($this->content['userAgent']) ? $this->content['userAgent'] : $default;
		$referer = !empty($this->content['referer']) ? $this->content['referer'] : $default;
		
		$mailMessage = (new MailMessage);
		$mailMessage->subject('ErrorException on ' . config('app.name'));
		
		// The Error
		$mailMessage->greeting('Error:');
		$mailMessage->line($errorMessage);
		$mailMessage->line('in <strong>' . $file . '</strong> line <strong>' . $line . '</strong>');
		
		// The Request
		$mailMessage->line('<br><h4>----- Request -----</h4>');
		$reqOut = '<strong>Method:</strong> ' . $method;
		$reqOut .= '<br><strong>Endpoint:</strong> ' . $endpoint;
		$reqOut .= '<br><strong>URL:</strong> ' . $url;
		$reqOut .= '<br><strong>IP:</strong> <a href="' . $ipLink . '" target="_blank">' . $ipLink . '</a>';
		$reqOut .= '<br><strong>User agent:</strong> ' . $userAgent;
		$reqOut .= '<br><strong>Referer:</strong> ' . $referer;
		$mailMessage->line($reqOut);
		
		// The Trace
		$mailMessage->line('<br><h4>----- Trace -----</h4>');
		$traceOut = '';
		$trace = $this->content['trace'] ?? [];
		foreach ($trace as $value) {
			$class = $value['class'] ?? $default;
			$function = $value['function'] ?? $default;
			$file = $value['file'] ?? $default;
			$line = $value['line'] ?? $default;
			
			if (!empty($traceOut)) {
				$traceOut .= '<br>';
			}
			
			$traceOut .= 'at <span title="' . $class . '">' . basename($class) . '</span>->' . $function . '() ';
			$traceOut .= 'in <strong>' . $file . '</strong> line ' . $line;
		}
		if (empty($traceOut)) {
			$traceOut .= '...';
		}
		$mailMessage->line($traceOut);
		
		// Team Salutation
		$mailMessage->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
		
		return $mailMessage;
	}
}

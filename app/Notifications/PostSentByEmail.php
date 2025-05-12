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

class PostSentByEmail extends BaseNotification
{
	protected ?object $post;
	protected ?array $mailData;
	
	public function __construct(?object $post, ?array $mailData)
	{
		$this->post = $post;
		$this->mailData = is_array($mailData) ? $mailData : [];
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		if (isDemoDomain()) return false;
		
		return (!empty($this->post) && !empty($this->mailData));
	}
	
	protected function determineViaChannels($notifiable): array
	{
		return ['mail'];
	}
	
	public function toMail($notifiable): MailMessage
	{
		$postUrl = urlGen()->post($this->post);
		$senderEmail = data_get($this->mailData, 'sender_email');
		
		return (new MailMessage)
			->replyTo($senderEmail, $senderEmail)
			->subject(trans('mail.post_sent_by_email_title', [
				'appName'     => config('app.name'),
				'countryCode' => $this->post->country_code,
			]))
			->line(trans('mail.post_sent_by_email_content_1', ['senderEmail' => $senderEmail]))
			->line(trans('mail.post_sent_by_email_content_2'))
			->line(trans('mail.Job URL') . ':  <a href="' . $postUrl . '">' . $postUrl . '</a>')
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
	}
}

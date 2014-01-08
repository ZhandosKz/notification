<?php

namespace common\modules\notification\sender;

use common\modules\notification\group\BaseNotificationGroup;
use Yii;

class EmailSender extends BaseSender
{
	protected function _sendInternal(BaseNotificationGroup $group)
	{
		/**
		 * @var \yii\swiftmailer\Message $message
		 */
		$message = Yii::$app->mail->compose('@common/modules/notification/views/mails/notification', [
			'content' => $group->body,
			'user' => $group->baseNotification->user,
			'reason' => $group->baseNotification->template->reason
		]);
		$message->setFrom('support@trytopic.com');
		$message->setTo($group->baseNotification->user->email);

		$subject = $group->baseNotification->template->subject;

		if (YII_ENV === 'dev') {
			$subject = '(DEV) '.$subject;
		}

		$message->setSubject($subject);

		return $message->send();
	}
}
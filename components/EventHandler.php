<?php

namespace common\modules\notification\components;

use yii\base;
use common\modules\notification\models;

class EventHandler extends base\Object
{
	public static function callback(base\Event $event)
	{
		/**
		 * @var models\NotificationTemplate $notificationTemplate
		 */
		$notificationTemplate = models\NotificationTemplate::find([
			'target_class' => get_class($event->sender),
			'target_event' => $event->name
		]);

		if (!$notificationTemplate) {
			throw new base\ErrorException('Шаблон уведомления не найден');
		}

		$notification = new models\Notification();

		$user = (isset($event->data['user'])) ? $event->data['user'] : null;

		if ($user) {
			unset($event->data['user']);
		}

		$notification->createByTemplate($notificationTemplate, $event->data, $user);
	}
}
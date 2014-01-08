<?php

namespace common\modules\notification\controllers;

use common\modules\notification\group\NotificationGroup;
use yii\console\Controller;
use common\modules\notification\models;


class SenderController extends Controller
{


	public function actionIndex()
	{

		$baseNotifications = models\Notification::findBaseSend()->limit(20)->all();

		/**
		 * @var models\Notification $notification
		 */
		foreach ($baseNotifications as $notification) {
			$group = new NotificationGroup(['baseNotification' => $notification]);
			if ($group->send()) {
				echo "notification ".$notification->template->target_class." for user #".$notification->user->primaryKey." sended\n";
			}
		}

	}

}
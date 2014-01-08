<?php

namespace common\modules\notification\group;

use common\modules\notification\models;
use yii\base\ErrorException;
use yii\base\Object;
use Yii;

/**
 * Class BaseNotificationGroup
 * @property string $body
 * @package common\modules\notification\group
 */
abstract class BaseNotificationGroup extends Object
{
	/**
	 * @var models\Notification
	 */
	public $baseNotification;

	/**
	 * @var array
	 */
	protected $_notifications = [];

	protected $_notificationsCanceled = [];

	public function composeNotifications()
	{
		$this->_notifications = $this->_notificationsCanceled = [];

		/**
		 * @var models\Notification $notification
		 */
		foreach (models\Notification::findCumulativeNotifications($this->baseNotification) as $notification) {

			// Проставляем связанные данные насильно, дабы избежать лишних запросов
			$notification->populateRelation('template', $this->baseNotification->template);
			$notification->populateRelation('user', $this->baseNotification->user);

			$notification->scenario = models\Notification::PRE_SEND_VALIDATION_SCENARIO;

			if (!$notification->validate()) {
				$this->_notificationsCanceled[] = $notification;
				continue;
			}

			// Отправляем его в пул
			array_unshift($this->_notifications, $notification);
		}

		return $this;
	}

	/**
	 * Сохранение нотификейшенов без отправки
	 * @return $this
	 * @throws \Exception
	 * @throws \yii\base\ErrorException
	 */
	public function save()
	{
		$transaction = Yii::$app->db->beginTransaction();
		try {
			$this->_saveCanceled();
			$this->_saveSended();
			$transaction->commit();
		} catch (ErrorException $e) {
			$transaction->rollback();
			throw $e;
		}

		return $this;
	}

	/**
	 * Сохранение в БД отмененных уведомлений
	 * @return $this
	 * @throws \yii\base\ErrorException
	 */
	protected function _saveCanceled()
	{
		/**
		 * @var models\Notification $notification
		 */
		foreach ($this->_notificationsCanceled as $notification) {
			$notification->scenario = models\Notification::UPDATE_SCENARIO;
			$notification->status = models\Notification::STATUS_CANCELED;
			if (!$notification->save()) {
				throw new ErrorException('Ошибка сохранения уведомления, для указание его отмененным');
			}
		}

		return $this;
	}

	/**
	 * Сохранение в БД отправленных уведомлений
	 * @return $this
	 * @throws \yii\base\ErrorException
	 */
	protected function _saveSended()
	{
		/**
		 * @var models\Notification $notification
		 */
		foreach ($this->_notifications as $notification) {
			$notification->scenario = models\Notification::UPDATE_SCENARIO;
			$notification->status = models\Notification::STATUS_SENDED;
			if (!$notification->save()) {
				throw new ErrorException('Ошибка сохранения уведомления, для указание его отправленным');
			}
		}

		return $this;
	}

	/**
	 * Получение сконкатенированного текста группы уведомлений
	 * @return mixed
	 */
	public function getBody()
	{
		return array_reduce($this->_notifications, function($prev, models\Notification $notification) {

			/**
			 * @var models\Notification $notification
			 */
			return $prev.$notification->text;
		});
	}

	/**
	 * Отправка уведомлений и сохранение их в БД
	 * @return bool
	 * @throws \yii\base\ErrorException
	 */
	public function send()
	{
		if (empty($this->_notifications)) {
			$this->composeNotifications();
		}

		$transaction = Yii::$app->db->beginTransaction();

		try {

			$this->_saveCanceled();
			$this->_saveSended();
			$sended = $this->baseNotification->template->getSender()->send($this);
			$transaction->commit();

		} catch (ErrorException $e) {
			$transaction->rollback();
			throw $e;
		}

		return $sended;
	}

}
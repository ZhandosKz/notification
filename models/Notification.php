<?php

namespace common\modules\notification\models;

use common\modules\notification\helpers\TextHelper;
use common\models\User;
use yii\base\ErrorException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Json;
use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $template_id
 * @property string $params
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $status
 * @property string $text
 * @property NotificationTemplate $template
 * @property User $user
 */
class Notification extends \yii\db\ActiveRecord
{
	const STATUS_IN_QUEUE = 0;
	const STATUS_SENDED = 1;
	const STATUS_CANCELED = 2;

	const PRE_SEND_VALIDATION_SCENARIO = 'send';
	const UPDATE_SCENARIO = 'update';

	public function behaviors ()
	{
		return [
			'timestamp' => [
				'class' => 'yii\behaviors\AutoTimestamp',
				'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => [
						'create_time',
						'update_time'
					],
					ActiveRecord::EVENT_BEFORE_UPDATE => 'update_time',
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'notification';
	}

	public function scenarios()
	{
		return [
			self::UPDATE_SCENARIO => ['status'],
			'create' => ['user_id', 'template_id', 'params'],
			self::PRE_SEND_VALIDATION_SCENARIO => ['id']
		];
	}
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['user_id', 'template_id', 'status'], 'required'],
			[['user_id', 'template_id', 'create_time', 'update_time', 'status'], 'integer'],
			[['params'], 'string'],
			['id', 'sendValidationCallback', 'on' => self::PRE_SEND_VALIDATION_SCENARIO]
		];
	}

	public function sendValidationCallback()
	{

		$template = $this->template;

		if (!$template->validation_class || !$template->validation_method) {
			return;
		}

		if (!call_user_func([$template->validation_class, $template->validation_method], $this)) {
			$this->addError('id', 'Notification sending canceled');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'user_id' => 'User ID',
			'template_id' => 'Template ID',
			'params' => 'Params',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'status' => 'Status'
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getTemplate()
	{
		return $this->hasOne(NotificationTemplate::className(), ['id' => 'template_id']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getUser()
	{
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	/**
	 * Создание уведомление на основе шаблона
	 * @param NotificationTemplate $template
	 * @param null $params Параметры уведомления подставляемые в текст шаблона
	 * @param User $user
	 * @return bool
	 * @throws \yii\base\ErrorException
	 */
	public function createByTemplate(NotificationTemplate $template, $params = null, $user = null)
	{
		if ($params === null) {
			$params = [];
		}

		if ($user === null) {
			if (Yii::$app->user->isGuest) {
				throw new ErrorException('Не указан пользователь для отправления уведомления и не авторизован пользователь');
			}
			$user = Yii::$app->user->identity;
		}

		if (empty($user->email)) {
			return true;
		}

		$this->scenario = 'create';

		$this->setAttributes([
			'user_id' => $user->primaryKey,
			'template_id' => $template->primaryKey,
			'params' => Json::encode($params)
		]);


		return $this->save();
	}

	/**
	 * Получение текста шаблона с подставленными параметрами уведомления
	 * @return string
	 */
	public function getText()
	{
		return TextHelper::parsePlaceholders($this->template->text, $this->getData());
	}

	/**
	 * Получение параметров для вставки в шаблон уведомления
	 * @return array
	 */
	public function getData()
	{
		$params = Json::decode($this->params);

		return array_merge((is_array($params)) ? $params : [], [
			'user_to_username' => Html::encode($this->user->username),
			'user_to_email' => Html::encode($this->user->email),
			'user_to_full_name' => Html::encode($this->user->full_name),
			'user_to_cell_phone' => Html::encode($this->user->cell_phone),
		]);
	}

	/**
	 * Получение базовых нотификейшенов для кумулятивной (по ним будет выборка однотипных нотификейшенов) отправки
	 * @param null $time UNIX timestamp
	 * @return ActiveQuery
	 */
	public static function findBaseSend($time = null)
	{
		if ($time === null) {
			$time = time();
		}

		return static::find()
			->select('notification.*')
			->from('(SELECT * FROM notification n ORDER BY n.create_time DESC) AS notification')
			->leftJoin('notification_template', 'notification.template_id = notification_template.id')
			->where('notification.status = :notification_status AND notification_template.status = :template_status AND notification.create_time + notification_template.hold_time <= :time', [
				':notification_status' => self::STATUS_IN_QUEUE,
				':template_status' => NotificationTemplate::STATUS_ACTIVE,
				':time' => $time
			])
			->groupBy('notification.user_id, notification.template_id');
	}

	/**
	 * Получение нотификейшенов для отправки исходя из базового нотификейшены
	 * @see findBaseSend
	 * @param Notification $baseNotification
	 * @return mixed
	 */
	public static function findCumulativeNotifications(Notification $baseNotification)
	{

		return static::find()
			->where('user_id = :user_id AND template_id = :template_id AND status = :notification_status AND create_time <= :time', [
				':user_id' => $baseNotification->user_id,
				':template_id' => $baseNotification->template_id,
				':time' => $baseNotification->create_time,
				':notification_status' => self::STATUS_IN_QUEUE
			])
			->all();
	}
}

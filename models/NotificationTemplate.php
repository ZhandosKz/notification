<?php

namespace common\modules\notification\models;

use common\modules\notification\sender;
use yii\base\ErrorException;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "notification_template".
 *
 * @property integer $id
 * @property string $target_class
 * @property string $target_event
 * @property string $validation_class
 * @property string $validation_method
 * @property string $sender_class
 * @property integer $status
 * @property string $text
 * @property integer $hold_time
 * @property integer $create_time
 * @property integer $update_time
 * @property string $reason
 * @property string $subject
 * @property string $description
 * @property Notification[] $activeNotifications
 * @property Notification[] $notifications
 */
class NotificationTemplate extends \yii\db\ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_UNACTIVE = 0;

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
		return 'notification_template';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['target_class', 'target_event', 'sender_class'], 'required'],
			[['status', 'create_time', 'update_time', 'hold_time'], 'integer'],
			[['text'], 'string'],
			[['target_class', 'target_event', 'validation_class', 'validation_event', 'sender_class'], 'string', 'max' => 255]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'target_class' => 'Target Alias',
			'target_event' => 'Target Event',
			'validation_class' => 'Validation Class',
			'validation_event' => 'Validation Event',
			'sender_class' => 'Sender Class',
			'status' => 'Status',
			'hold_time' => 'Hold Time',
			'text' => 'Text',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getNotifications()
	{
		return $this->hasMany(Notification::className(), ['template_id' => 'id']);
	}

	/**
	 * @return sender\SenderInterface
	 */
	public function getSender()
	{
		return sender\SenderFactory::create([
			'class' => $this->sender_class
		]);
	}
}

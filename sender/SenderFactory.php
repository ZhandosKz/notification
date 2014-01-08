<?php

namespace common\modules\notification\sender;

use common\modules\notification\sender\SenderInterface;
use yii\base\ErrorException;
use Yii;
use yii\base\Object;

/**
 * Class SenderFactory
 * @package common\modules\notification\sender
 */
class SenderFactory extends Object
{
	private static $_instances = [];

	public static function create(array $options)
	{
		if (empty($options['class'])) {
			throw new ErrorException('Не указан класс для создания Sender-а');
		}

		if (array_key_exists($options['class'], self::$_instances)) {
			return self::$_instances[$options['class']];
		}

		if (!(self::$_instances[$options['class']] = Yii::createObject($options)) instanceof SenderInterface) {
			throw new ErrorException('Sender не реализует интерфейс для отправки уведомлений');
		}

		return self::$_instances[$options['class']];
	}

	public static function getInstances()
	{
		return self::$_instances;
	}

	public static function clearInstances()
	{
		self::$_instances = [];
	}
}
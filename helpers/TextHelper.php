<?php

namespace common\modules\notification\helpers;

class TextHelper
{
	/**
	 * Функция для подстановки placeholder-ов, за основу взята функция Yii::t
	 * @param $text
	 * @param $params
	 * @return string
	 */
	public static function parsePlaceholders($text, $params)
	{
		$p = [];
		foreach ((array) $params as $name => $value) {
			$p['{' . $name . '}'] = $value;
		}
		return ($p === []) ? $text : strtr($text, $p);
	}
}
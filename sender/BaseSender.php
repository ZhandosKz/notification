<?php

namespace common\modules\notification\sender;

use common\modules\notification\group\BaseNotificationGroup;
use yii\base\Object;

abstract class BaseSender extends Object implements SenderInterface
{
	public function send(BaseNotificationGroup $group)
	{
		if (empty($group->body)) {
			return false;
		}

		return $this->_sendInternal($group);
	}

	abstract protected function _sendInternal(BaseNotificationGroup $group);
}
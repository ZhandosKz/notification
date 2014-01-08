<?php

namespace common\modules\notification\sender;

use common\modules\notification\group\BaseNotificationGroup;
use common\modules\notification\models;


class SmsSender extends BaseSender
{
	protected function _sendInternal(BaseNotificationGroup $group)
	{
		return true;
	}
}
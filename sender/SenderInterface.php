<?php

namespace common\modules\notification\sender;

use common\modules\notification\group\BaseNotificationGroup;

interface SenderInterface
{
	/**
	 * @param BaseNotificationGroup $group
	 * @return bool
	 */
	public function send(BaseNotificationGroup $group);
}
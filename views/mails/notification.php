<?php
/**
 * @var \common\models\User $user
 * @var string $reason
 */
?>

<table cellpadding="0" cellspacing="0" style="height: 100% !important; width: 100% !important;background: #F4F4F4;color: #606060;font-family: 'Trebuchet MS','Lucida Grande','Lucida Sans Unicode','Lucida Sans',Tahoma,sans-serif;font-size: 15px;">
	<tr>
		<td align="center">
			<table id="wrapper" align="center" width="600" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width:600px !important;">
				<tr>
					<td class="header" align="center" style="text-align: center;font-size: 14px;line-height: 125%;padding: 9px 18px;">
						<?=Yii::t('notification', 'Dear {full_name}', ['full_name' => $user->full_name])?>,
					</td>
				</tr>
				<tr>
					<td class="container" style="background: #fff;line-height: 150%;">
						<?=$content?>
					</td>
				</tr>
				<tr>
					<td class="footer" style="font-size: 11px;padding-top: 100px;">
						<em>Copyright © <?=date('Y')?> <a href="http://trytopic.com" style="color: #1155CC; text-decoration: none;">TryTopic</a>, Inc., All rights reserved.</em><br/>
						<?=$reason?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

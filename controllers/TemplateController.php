<?php


namespace common\modules\notification\controllers;

use common\modules\notification\models;
use yii\data\ActiveDataProvider;

class TemplateController extends \yii\web\Controller
{
	public function actionIndex()
	{
        return $this->render('index', [
                'dataProvider' => new ActiveDataProvider(['query' => models\NotificationTemplate::find()])
            ]);
	}

    public function actionView()
    {

    }

    public function actionCreate()
    {

    }

    public function actionUpdate()
    {

    }

    public function actionDelete()
    {

    }
}
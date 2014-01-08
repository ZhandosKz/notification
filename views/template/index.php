<?php

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 */
echo \yii\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            //'',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}{delete}'
            ],
        ]
    ]);
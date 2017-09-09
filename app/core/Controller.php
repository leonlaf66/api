<?php
namespace module\core;

use yii\filters\Cors;
use yii\helpers\ArrayHelper;

class Controller extends \deepziyu\yii\rest\Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*']
                ]
            ]
        ]);
    }
}
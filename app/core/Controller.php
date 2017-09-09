<?php
namespace module\core;

use yii\filters\Cors;
use yii\helpers\ArrayHelper;

class Controller extends \deepziyu\yii\rest\Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'corsFilter'=>[
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['app-token', 'language']
                ]
            ],
        ]);
    }
}
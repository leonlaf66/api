<?php
namespace module\core;

use yii\filters\Cors;
use yii\helpers\ArrayHelper;

class Controller extends \deepziyu\yii\rest\Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            // For cross-domain AJAX request
            'corsFilter'  => [
                'class' => Cors::className(),
                'cors'  => [
                    // restrict access to domains:
                    'Origin'                           => ['*'],
                    'Access-Control-Request-Method'    => ['GET', 'POST', 'DELETE', 'OPTIONS'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age'           => 3600,
                ],
            ],

        ]);
    }
}
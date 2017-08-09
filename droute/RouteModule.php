<?php

namespace droute;

use yii\base\Exception;

class RouteModule extends \yii\base\Module
{
    public $controllerNamespace = 'droute\controllers';

    public function init()
    {
        if(!YII_DEBUG){
            throw new Exception('only accessed on debug evn!',500);
        }
        parent::init();

        // custom initialization code goes here
    }
}

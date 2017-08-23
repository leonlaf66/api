<?php
return \yii\helpers\ArrayHelper::merge(get_fdn_etc(), [
    'id' => 'usleju-api',
    'appToken' => 'b2e476cb5ddcbf81c337218d5b5d43fa83bd6a8d4c9b7ba4ea047c70d22a828c',
    'basePath' => dirname(__DIR__),
    'passportUrl' => 'http://passport.usleju.local',
    'bootstrap' => [
        [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => 'json',
                'application/xml' => 'xml',
            ],
            'languages' => [
                'zh-CN',
                'en-US',
            ],
        ]
    ],
    'components' => [
        'errorHandler' => [
            'class'=>'deepziyu\yii\rest\ErrorHandler'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                //模块化的路径
                "GET,POST,PUT,DELETE <module>/<controller:[\w-]+>/<action:[\w-]+>" => "<module>/<controller>/<action>",
                //基本路径
                "GET,POST,PUT,DELETE <controller:[\w-]+>/<action:[\w-]+>" => "<controller>/<action>",
            ],
        ],
        'user' => [
            'identityClass' => '\common\customer\UserIdentity',
            'enableAutoLogin' => false,
            'loginUrl'=>''
        ],
        'request' => [
            'class' => '\yii\web\Request',
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            //返回异常统一处理
            'on beforeSend' => function ($event) {
                //$event->sender->format = 'json';
            },
        ],
    ],
    'modules'=>[
        'route'=>'droute\RouteModule',
        'estate'=>'module\estate\Module',
        'catalog'=>'module\catalog\Module',
        'passport'=>'module\passport\Module',
        'member'=>'module\member\Module',
        'support'=>'module\support\Module'
    ],
    'aliases'=>[
        '@bower'=>APP_ROOT.'/vendor/bower',
        'module'=>APP_ROOT.'/app'
    ]
]);
<?php
namespace module\member\controllers;

class ProfileController extends \deepziyu\yii\rest\Controller
{   
    public $enableAuth = true;

    /**
     * 获取用户信息
     * @desc 获取用户信息, 需要登陆，只能获取当前已登陆的用户信息
     * @return object - 用户信息
     */
    public function actionGet()
    {
        return \common\customer\Profile::findOne(\WS::$app->user->id);
    }
}

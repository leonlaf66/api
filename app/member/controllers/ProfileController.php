<?php
namespace module\member\controllers;

class ProfileController extends \deepziyu\yii\rest\Controller
{   
    public $enableAuth = true;

    /**
     * 获取用户信息
     * @desc 获取用户信息
     * @return array profile 用户信息
     */
    public function actionGet()
    {
        return [
            'id' => 123,
            'name' => 'Eddylee'
        ];
    }
}

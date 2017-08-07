<?php
namespace module\passport\controllers;

use module\core\Exception;

class AccountController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 会员登陆
     * @desc 会员登陆
     * @param string $username 登陆用户名
     * @param string $password 登陆密码
     * @return array account 用户授权结果
     */
    public function actionLogin($username, $password)
    {
        $user = \common\customer\Account::findByEmail($username);
        if (!$user) {
            throw new Exception(403, "不存在的用户!");
        }

        if (!$user->validatePassword($password)) {
            throw new Exception(403, "错误的用户名或密码!");
        }

        if (!$user->getIsConfirmed()) {
            throw new Exception(403, "未验证的邮箱地址!");
        }

        return [
            'profile' => $user->getProfile(),
            'access_token' => $user->access_token
        ];
    }
}

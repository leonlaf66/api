<?php
namespace module\passport\controllers;

use WS;
use module\core\Exception;
use common\customer\forms\RegisterForm;

class AccountController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 会员登陆
     * @desc 通过email+password进行登陆
     * @param string $username 登陆用户名(用户名/邮箱地址)
     * @param string $password 登陆密码
     * @return object profile 用户信息
     * @return string access_token 用户的access token
     */
    public function actionLogin($username, $password)
    {
        $user = \common\customer\Account::findByAid($username);
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

    /**
     * 微信登陆
     * @method POST
     * @desc 微信登陆
     * @param string $open_id
     * @return string - 用户的Access token
     */
    public function actionWechatLogin($open_id)
    {
        $now = date('Y-m-d H:i:s', time());
        $accessToken = WS::$app->security->generateRandomString();

        $data = [
            'auth_key' => WS::$app->getSecurity()->generateRandomString(),
            'access_token' => $accessToken,
            'created_at' => $now,
            'updated_at' => $now,
            'registration_ip' => WS::$app->request->getUserIP(),
            'open_id' => $open_id,
            'confirmed_at' => $now
        ];

        $result = WS::$app->db->createCommand()
            ->insert('user', $data)
            ->execute();

        return $result > 0 ? ['access_token' => $accessToken] : false;
    }

    /**
     * 会员注册
     * @method POST
     * @desc 通过email进行会员注册
     * @data string $email 邮箱地址
     * @data string $password 用户密码
     * @return boolean - 用户注册结果, 需要验证邮箱地址
     */
    public function actionRegister()
    {
        $registerForm = new RegisterForm();
        if(WS::$app->request->isPost) {
            $postData = ['RegisterForm' => WS::$app->request->post()];
            $registerForm->load($postData);
            $registerForm->confirm_password = $registerForm->password;
            $registerForm->accept_agreed = true;

            if($registerForm->validate()) {
                if($user = $registerForm->accountRegister()) {
                    $url = WS::$app->passportUrl.'/email-confirm/?id='.$user->id.'&token='.$user->access_token.'&from=app';
                    $user->sendConfirmEmail($url);
                }
            } else {
                return [
                    'errors' => $registerForm->errors
                ];
            }

            return true;
        }

        return false;
    }

    /**
     * 找回密码
     * @desc 通过邮件地址获取新的临时密码
     * @param string $username 用户名(邮箱地址)
     * @return boolean - 找回密码结果
     */
    public function actionForgotPassword($username)
    {
        $forgotForm = new ForgotPasswordForm();
        $forgotForm->email  = $username;
        if($forgotForm->validate()) {
            if($account = $forgotForm->getAccount()) {
                $account->sendTempPasswordEmail();
            } else {
                throw new Exception(403, "不存在的用户!");
            }
        } else {
            throw new Exception(403, "系统错误或不存在的用户!");
        }

        return true;
    }
}

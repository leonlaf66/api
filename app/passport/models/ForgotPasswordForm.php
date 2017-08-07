<?php

namespace module\passport\models;

use WS;
use common\customer\Account;

class ForgotPasswordForm extends \common\customer\forms\ForgotPasswordForm
{
    public $email;
    public $isProcessed = false;

    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'exist', 'targetClass'=>Account::className(),  'message' => 'Account not exist!']
        ];
    }

    public function attributeLabels()
    {
        return [
            'email'=>tt('Email Address', '邮箱地址')
        ];
    }

    public function getAccount()
    {
        return Account::findByEmail($this->email);
    }
}

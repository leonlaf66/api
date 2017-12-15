<?php
namespace module\support\controllers;

use WS;

class ExceptionController extends \deepziyu\yii\rest\Controller
{
    public function actionReport($url)
    {
        $content = \WS::$app->request->post('content');
        $content = '['.date('Y-m-d H:i:s').']'.$url."\r\nMessage: ".$content."\r\n";
        file_put_contents(APP_ROOT.'/exceptions.log', $content, FILE_APPEND);
    }
}

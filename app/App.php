<?php
class App extends \common\supports\ApiApp
{
    public $appToken = '';
    public $baseUrl = '/';
    public $passportUrl = '';
    public $translationStatus = false;

    public function bootstrap()
    {
        if ($_SERVER['REQUEST_URI'] != '/route/api/index') {
            $this->authAppToken();
        }
        $this->initSite();
        parent::bootstrap();
    }

    protected function authAppToken()
    {
        if (\Yii::$app->request->get('app-token') !== $this->appToken && \Yii::$app->request->headers->get('app-token') !== $this->appToken) {
            echo json_encode([
                'app-token' => \Yii::$app->request->headers->get('app-token'),
                'response' => [
                    'code' => 401,
                    'message' => 'APP授权失败'
                ]
            ]);
            \Yii::$app->end();
        }
    }

    protected function initSite()
    {
        if ($areaId = WS::$app->request->get('area_id')) {
            WS::$app->area->initArea($areaId);
        } elseif ($areaId = \Yii::$app->request->headers->get('area-id')) {
            WS::$app->area->initArea($areaId);
        }
    }

    public function beforeAction($action)
    {
        if (in_array($this->controller->module->id, ['catalog', 'estate'])) {
            if (is_null(WS::$app->area->id)) {
                echo json_encode([
                    'response' => [
                        'code' => 403,
                        'message' => '必须指定area_id'
                    ]
                ]);exit;
            }
        }
        return parent::beforeAction($action);
    }
}

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
        $siteId = null;

        if ($areaId = WS::$app->request->get('area_id')) {
            WS::$app->area->initArea($areaId);
        }
    }
}

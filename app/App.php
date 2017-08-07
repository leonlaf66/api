<?php
class App extends \common\web\ApiApp
{
    public $appToken = '';
    public $baseUrl = '/';
    public $siteMaps = [
        'ma' => 'MA'
    ];
    public $stateId = null;

    public function bootstrap()
    {
        $this->authAppToken();
        $this->initSite();
        parent::bootstrap();
    }

    protected function authAppToken()
    {
        if (\Yii::$app->request->headers->get('app-token') !== $this->appToken) {
            echo json_encode([
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
        $cookies = \WS::$app->response->cookies;
        $siteId = null;

        if (isset($cookies['state_id'])) {
            $siteId = $cookies->getValue('state_id');
        } else {
            $parts = explode('.', $_SERVER["HTTP_HOST"]);
            if (isset($this->siteMaps[$parts[0]])) {
                $siteId = $parts[0];

                $this->stateId = $this->siteMaps[$siteId];

                // 记录城市 
                \WS::$app->response->cookies->add(new \yii\web\Cookie([
                    'name' => 'state_id',
                    'value' => $this->stateId,
                    'expire' => 0,
                    'domain' => domain()
                ]));

                \WS::$app->response->cookies->add(new \yii\web\Cookie([
                    'name' => 'house_base_url',
                    'value' => $this->request->getHostInfo().'/',
                    'expire' => 0,
                    'domain' => domain()
                ]));
            }
        }
    }
}

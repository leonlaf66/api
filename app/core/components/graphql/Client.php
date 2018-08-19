<?php
namespace module\core\components\graphql;

class Client extends \yii\base\Component
{
    public $baseUrl = '';
    public $appToken = null;

    protected $_client = null;

    public function init()
    {
        $this->_client = new \EUAutomation\GraphQL\Client($this->baseUrl);
    }

    public function request($gqlId, $variables = [], $headers = [], $defValue = null)
    {
        $headers = array_merge($headers, [
            'app-token' => $this->appToken,
            'language' => \yii::$app->language,
            'area-id' => \WS::$app->area->id
        ]);

        $query = $this->getGraphqlQuery($gqlId);
        $response = $this->_client->response($query, $variables, $headers);
        if ($response->hasErrors()) {
            return $defValue;
        }

        return $response->all();
    }

    private function getGraphqlQuery($gqlId)
    {
        return file_get_contents(\yii::$app->controller->module->basePath . '/gql/' . $gqlId . '.gql');
    }
}
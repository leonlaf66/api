<?php
namespace module\core\components\graphql;

use deepziyu\yii\rest\ApiException;

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
        $response = null;
        try {
            $response = $this->_client->response($query, $variables, $headers);
        } catch(\Exception $e) {
            throw new ApiException(400, $e->getMessage(), $e);
        }
        if ($response->hasErrors()) {
            $error = $response->errors()[0];
            throw new ApiException($error->extensions->code, 'SERVICE-ERROR:'.$error->message);
        }

        return $response->all();
    }

    private function getGraphqlQuery($gqlId)
    {
        return file_get_contents(\yii::$app->controller->module->basePath . '/gql/' . $gqlId . '.gql');
    }
}
<?php
namespace module\support;

use WS;
use yii\base\UserException;
use yii\web\HttpException;

class ErrorHandler extends \deepziyu\yii\rest\ErrorHandler
{
    protected function convertExceptionToArray($exception)
    {
        if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, WS::t('yii', 'An internal server error occurred.'));
        }
        $message = $exception->getMessage();
        if ($exception->statusCode == '404') {
            $message = tt('Illegal API interface.', '非法的API接口.');
        }
        $array = [
            'code' => $exception->getCode(),
            'data' => null,
            'message' => $message,
        ];
        if ($exception instanceof HttpException) {
            $array['code'] = $exception->statusCode;
        }
        if($exception instanceof ApiException && !empty($exception->model)){
            $array = [
                'code' => 422,
                'data' => $exception->model->getErrors(),
                'message' => 'Data Validation Failed.',
            ];
        }

        if (YII_DEBUG) {
            $array['server'] = [];
            $array['server']['type'] = get_class($exception);
            if (!$exception instanceof UserException) {
                $array['server']['file'] = $exception->getFile();
                $array['server']['line'] = $exception->getLine();
                $array['server']['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof \yii\db\Exception) {
                    $array['server']['error-info'] = $exception->errorInfo;
                }
            }
            if (($prev = $exception->getPrevious()) !== null) {
                $array['server']['previous'] = $this->convertExceptionToArray($prev);
            }
        }

        return $array;
    }
}

<?php
namespace module\support\controllers;

use WS;

class ConfigsController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 提交app配置
     * @desc 创建或更新app配置
     * @method POST
     * @param string $app_id 所属app的标识，建议为 "iphone", "ipad", "android", "web" 等
     * @data string $config_id 配置标识
     * @data string $config_content 配置内容
     * @return boolan - 提交成功与否
     */
    public function actionSubmit($app_id)
    {
        $configId = WS::$app->request->post('config_id');
        $configContent = json_encode(WS::$app->request->post('config_content'));

        return \common\supports\AppConfigs::submit($app_id, $configId, $configContent);
    }

    /**
     * 获取app配置
     * @desc 获取指定app_id，指定config_id的配置内容
     * @param string $app_id 所属app的标识，建议为 "iphone", "ipad", "android", "web" 等
     * @param string $config_id 需要获取配置的标识
     * @return string - 配置内容
     */
    public function actionGet($app_id, $config_id)
    {
        $config = \common\supports\AppConfigs::get($app_id, $config_id);
        return $config ? json_decode($config->config_content) : null;
    }
}

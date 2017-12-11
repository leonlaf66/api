<?php
namespace module\catalog;

class Module extends \module\core\Module 
{
    public function runAction($route, $params = [])
    {
        var_dump(\WS::$app->controller->module->id);exit;
        return parent::runAction($route, $params);
    }
}
<?php
namespace module\core;

class Module extends \yii\base\Module
{
    public $urlRules = [];

    protected $_configs = null;

    public function init()
    {
        \WS::$app->getUrlManager()->addRules($this->urlRules);
        
        return parent::init();
    }

    public function getConfigs($type) 
    {
        if(! isset($this->_configs[$type])) {
            $this->_configs[$type] = include(APP_ROOT.'/app/'.$this->id.'/etc/'.$type.'.php');
        }
        return $this->_configs[$type];
    }
}
<?php
namespace module\core\models;

class ActiveRecord extends \yii\db\ActiveRecord
{
    public static $_underscoreCache = [];
    
    public function setData($key, $value)
    {
        return parent::__set($key, $value);
    }

    public function getData($key, $defValue=null)
    {
        $value = parent::__get($key);
        return $value ? $value : $defValue;
    }

    public function __call($method, $args) {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_underscore(substr($method,3));
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
                return $data;

            case 'set' :
                $key = $this->_underscore(substr($method,3));
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);
                return $result;

            case 'uns' :
                $key = $this->_underscore(substr($method,3));
                $result = parent::__unset($key);
                return $result;

            case 'has' :
                $key = $this->_underscore(substr($method,3));
                return parent::__isset($key);
        }
        throw new \Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
    }

    public function __set($var, $value)
    {
        $var = $this->_underscore($var);
        $this->setData($var, $value);
    }

    public function __get($var)
    {
        $var = $this->_underscore($var);
        return $this->getData($var);
    }

    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$_underscoreCache[$name] = $result;
        return $result;
    }
}
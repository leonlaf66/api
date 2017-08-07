<?php
namespace module\core\components;

class AbstractBase extends \yii\base\Component
{
    protected $_data = [];

    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function getData($key, $defValue=null)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : $defValue;
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
                $result = $this->unsetData($key);
                return $result;

            case 'has' :
                $key = $this->_underscore(substr($method,3));
                return isset($this->_data[$key]);
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

    public function unsetData($key=null)
    {
        $this->_hasDataChanges = true;
        if (is_null($key)) {
            $this->_data = array();
        } else {
            unset($this->_data[$key]);
            if (isset($this->_syncFieldsMap[$key])) {
                $fullFieldName = $this->_syncFieldsMap[$key];
                unset($this->_data[$fullFieldName]);
            }
        }
        return $this;
    }
}
<?php
namespace module\core\models;

class Storage extends \yii\db\ActiveRecord
{
    public static function tableName()  
    {  
        return 'storage';
    }

    public function getImageName()
    {
    	return $this->filepath . '/' . $this->filename;
    }
}
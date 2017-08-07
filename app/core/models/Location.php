<?php
namespace module\core\models;

class Location
{
	const DEFAULT_STATE = 1;

	public static function model()
	{	
		static $instance = null;
		if(is_null($instance)) $instance = new self();
		return $instance;
	}

	public function getQuery()
	{
		static $query = null;
		if(is_null($query)) {
			$query = new \yii\db\Query();
		}
		return $query;
	}

	/**
	 * return array() elements: name
	 */
	public function getStates()
	{
		return $this->getQuery()
			->select('*')
			->from($this->tableName('location_state'))
			->createCommand()
			->queryAll();
	}

	/**
	 * params:$state state name
	 * return array() city name
	 */
	public function getCities($state)
	{
		return $this->getQuery()
			->select('*')
			->from($this->tableName('location_city'))
			->where('state_id=:state_id', array(':state_id'=>$state))
			->createCommand()
			->queryAll();
	}

	/**
	 * params: $city city name
	 * return array() area name
	 */
	public function getAreas($city)
	{
		return $this->getQuery()
			->select('*')
			->from($this->tableName('location_area'))
			->where('city_id=:city_id', array(':city_id'=>$city))
			->createCommand()
			->queryAll();
	}

	public function getDefaultCitys()
	{
		return $this->getCities(self::DEFAULT_STATE);
	}

	public function getQueuedDefaultCitys()
	{
		$citys = $this->getDefaultCitys();
		$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$groupNames = array();
		foreach(str_split($letters, 3) as $group) {
			foreach(str_split($group) as $letter) {
				$groupNames[$letter] = $group;
			}
		}
		
		$resultArray = array();
		foreach($citys as $city) {
			$city = $city['name'];
			$c = strtoupper(substr($city, 0, 1));
			$group = $groupNames[$c];

			$resultArray[$group][] = $city;
		}
		return $resultArray;
	}

	public function tableName($name)
	{
		return $name;
	}
}
<?php
namespace module\listhub\estate\helpers;

class SearchMap
{
    public static function apply($req, $query) {
        // 应用售/租房
        self::applyType($req->get('type', 'purchase'), $query);
        // 应用q搜索
        $city = self::applySearchCity($req->get('q', ''), $query);
        // 应用筛选
        self::applyFilters($req->get('filters', []), $query);

        return $city;
    }

    public static function applyType($type, $query)
    {
        if ($type === 'lease') {
            $query->andFilterWhere(['=', 'prop_type', 'RN']);
        } else {
            $query->andFilterWhere(['<>', 'prop_type', 'RN']);
        }
    }

    public static function applySearchCity($q, $query)
    {
        $city = null;
        if (! empty($q)) {
            if (is_numeric($q) && strlen($q) === 5) {
                $query->andWhere(['zip_code' => $q]);
                $city = \models\City::findByPostalcode(\WS::$app->area->stateId, $q);
            } elseif (preg_match('/[a-zA-Z]{0,2}[0-9]{5,10}/', $q)) {
                $query->andWhere(['id' => $q]);
                if ($rets = \common\listhub\estate\House::findOne($q)) {
                    $city = $rets->city;
                }
            } else {
                $cityName = ucwords($q);
                $city = \models\City::findByName(\WS::$app->area->stateId, $cityName);
                if ($city) {
                    $query->andWhere('city_id=:city_id or parent_city_id=:city_id', [':city_id' => $city->id]);
                } else {
                    $query->where('1=2');
                }
            }
        }

        return $city;
    }

    public static function applyFilters($filters, $query)
    {
        $filterRules = include(dirname(__DIR__).'/etc/filters.php');

        foreach ($filters as $field => $value) {
            if (isset($filterRules[$field])) {
                ($filterRules[$field])($value, $query);
            }
        }
    }
}
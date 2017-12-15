<?php
namespace module\listhub\estate\helpers;

class SearchGeneral
{
    public static function apply($req, $query) {
        // 应用售/租房
        self::applyType($req->get('type', 'purchase'), $query);
        // 应用q搜索
        self::applySearchText($req->get('q', ''), $query);
        // 应用筛选
        self::applyFilters($req->get('filters', []), $query);
        // 应用排序
        self::applySortOrder($req->get('order', '0'), $query);
    }

    public static function applyType($type, $query)
    {
        if ($type === 'lease') {
            $query->andWhere(['=', 'prop_type', 'RN']);
        } else {
            $query->andWhere(['<>', 'prop_type', 'RN']);
        }
    }

    public static function applySearchText($q, $query)
    {
        if (! empty($q)) {
            if (is_numeric($q) && strlen($q) === 5) {
                $query->andWhere(['zip_code' => $q]);
            } elseif (preg_match('/[a-zA-Z]{0,2}[0-9]{5,10}/', $q)) {
                $query->andWhere(['id' => $q]);
            } else {
                $cityName = ucwords($q);
                $city = \models\City::findByName(\WS::$app->area->stateId, $cityName);
                if ($city) {
                    $query->andWhere(['city_id' => $city->id]);
                } else {
                    $query->where('1=2');
                }
            }
        }
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

    public static function applySortOrder($type, $query)
    {
        $maps = [
            '0' => ['list_date' => SORT_DESC, 'id' => SORT_DESC],
            '1' => ['list_price' =>  SORT_ASC, 'id' => SORT_DESC],
            '2' => ['list_price' => SORT_DESC, 'id' => SORT_DESC],
            '3' => ['no_bedrooms' => SORT_DESC, 'id' => SORT_DESC],
            '4' => ['no_bedrooms' => SORT_ASC, 'id' => SORT_DESC]
        ];

        if (! isset($maps[$type])) $type = '0';

        $query->orderBy($maps[$type]);
    }
}
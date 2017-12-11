<?php
namespace module\listhub\estate\helpers;

class SearchGeneral
{
    public static function apply($req, $search) {
        // 应用售/租房
        self::applyType($req->get('type', 'purchase'), $search);
        // 应用q搜索
        self::applySearchText($req->get('q', ''), $search);
        // 应用筛选
        self::applyFilters($req->get('filters', []), $search);
        // 应用排序
        self::applySortOrder($req->get('order', '0'), $search);
    }

    public static function applyType($type, $search)
    {
        if ($type === 'lease') {
            $search->query->andFilterWhere(['=', 'prop_type', 'RN']);
        } else {
            $search->query->andFilterWhere(['<>', 'prop_type', 'RN']);
        }
    }

    public static function applySearchText($q, $search)
    {
        if (! empty($q)) {
            if (is_numeric($q) && strlen($q) === 5) {
                $search->query->andWhere(['zip_code' => $q]);
            } elseif (preg_match('/[a-zA-Z]{0,2}[0-9]{5,10}/', $q)) {
                $search->query->andWhere(['id' => $q]);
            } else {
                $cityName = ucwords($q);
                $city = \models\City::findByName(\WS::$app->area->stateId, $cityName);
                if ($city) {
                    $search->query->andWhere(['city_id' => $city->id]);
                } else {
                    $search->query->where('1=2');
                }
            }
        }
    }

    public static function applyFilters($filters, $search)
    {
        $filterRules = include(dirname(__DIR__).'/etc/filters.php');

        foreach ($filters as $field => $value) {
            if (isset($filterRules[$field])) {
                ($filterRules[$field])($value, $search);
            }
        }
    }

    public static function applySortOrder($type, $search)
    {
        $maps = [
            '0' => ['list_date' => SORT_DESC, 'id' => SORT_DESC],
            '1' => ['list_price' =>  SORT_ASC, 'id' => SORT_DESC],
            '2' => ['list_price' => SORT_DESC, 'id' => SORT_DESC],
            '3' => ['no_bedrooms' => SORT_DESC, 'id' => SORT_DESC],
            '4' => ['no_bedrooms' => SORT_ASC, 'id' => SORT_DESC]
        ];

        if (! isset($maps[$type])) $type = '0';

        $search->query->orderBy($maps[$type]);
    }
}
<?php
namespace module\estate\helpers;

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
        if ($q && strlen($q) > 0) {
            $town = \common\catalog\Town::searchKeywords($q);
            if ($town) { // 城市
                $search->query->andWhere(['town' => $town->short_name]);
            } else {
                $zipcode = \common\catalog\Zipcode::searchKeywords($q);
                if ($zipcode) { // zip
                    $search->query->andWhere(['town' => $zipcode->city_short_name]);
                } else { // 普通搜索
                    $qWhere = "to_tsvector('english', location) @@ plainto_tsquery('english', '{$q}')";
                    $search->query->andWhere($qWhere);
                }
            }

            if (is_numeric($q)) { // mls id
                $search->query->orWhere(['id' => $q]);
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
            '0' => 'id DESC',
            '1'=>'list_price ASC',
            '2'=>'list_price DESC',
            '3'=>'no_bedrooms DESC',
            '4'=>'no_bedrooms ASC'
        ];

        if (! isset($maps[$type])) return;

        $search->query->orderBy($maps[$type]);
    }
}
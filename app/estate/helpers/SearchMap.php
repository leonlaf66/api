<?php
namespace module\estate\helpers;

class SearchMap
{
    public static function apply($req, $query) {
        // 应用售/租房
        self::applyType($req->get('type', 'purchase'), $query);
        // 应用q搜索
        $townCode = self::applySearchTown($req->get('q', ''), $query);
        // 应用筛选
        $townCodes = self::applyFilters($req->get('filters', []), $query);

        if ($townCode) {
            if (! in_array($townCode, $townCodes)) {
                $townCodes[] = $townCode;
            }
        }
        
        return $townCodes;
    }

    public static function applyType($type, $query)
    {
        if ($type === 'lease') {
            $query->andFilterWhere(['=', 'prop_type', 'RN']);
        } else {
            $query->andFilterWhere(['<>', 'prop_type', 'RN']);
        }
    }

    public static function applySearchTown($q, $query)
    {
        $townCode = null;
        if ($q && strlen($q) > 0) {
            $town = \models\Town::searchKeywords(ucwords($q), 'MA');
            if ($town) { // 城市
                $query->andWhere(['town' => $town->short_name]);
                $townCode = $town->short_name;
            } else {
                $zipcode = \models\ZipcodeTown::searchKeywords($q);
                if ($zipcode) { // zip
                    $query->andWhere(['town' => $zipcode->city_short_name]);
                    $townCode = $zipcode->city_short_name;
                } else { // 普通搜索
                    $qWhere = "1=2";
                    $query->andWhere($qWhere);
                }
            }

            if (is_numeric($q)) { // mls id
                $query->orWhere(['id' => $q]);
            }
        }

        return $townCode;
    }

    public static function applyFilters($filters, $query)
    {
        $filterRules = include(dirname(__DIR__).'/etc/filters.php');

        $towns = [];
        foreach ($filters as $field => $value) {
            if (isset($filterRules[$field])) {
                if($result = ($filterRules[$field])($value, $query)) {
                    $towns = $result;
                }
            }
        }

        return $towns;
    }
}
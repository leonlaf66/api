<?php
$baseFilters = include(dirname(__DIR__).'/../../estate/etc/filters.php');

return array_merge($baseFilters, [
    'city_code' => function ($cityCode, $query) {
        // disabled
    },
    'city_id' => function ($cityId, $search) {
        $query->andWhere(['=', 'city_id', $cityId]);
    },
    'agrage' => function ($val, $search) {
        // disabled
    },
    'school_district' => function ($townCode, $query) {
       //disabled
    },
    'subway_line' => function ($lineId, $query) {
       //disabled
    },
    'subway_stations' => function ($stationIds, $query) {
       //disabled
    }
]);
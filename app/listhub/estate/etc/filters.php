<?php
$baseFilters = include(dirname(__DIR__).'/../../estate/etc/filters.php');

return array_merge($baseFilters, [
    'city_id' => function ($cityId, $query) {
        $query->andWhere('city_id=:city_id or parent_city_id=:city_id', [':city_id' => $cityId]);
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
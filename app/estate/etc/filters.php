<?php
return [
    'latlon' => function ($vals, $search) {
        list($lat, $lon) = explode(',', $vals);
        $search->query->andWhere('earth_box(ll_to_earth(latitude, longitude),2000) @> ll_to_earth(:lat, :lon)', [':lat' => $lat, ':lon' => $lon]);
    },
    'city_code' => function ($code, $search) {
        $code = strtoupper($code);
        $search->query->andWhere(['=', 'town', $code]);
    },
    'list_price' => function ($range, $search) {
        list($start, $end) = array_values(array_merge([
            'from' => 0,
            'to' => 9999999999
        ], $range));

        $search->query->andWhere(['>', 'list_price', $start]);
        $search->query->andWhere(['<', 'list_price', $end]);
    },
    'prop-type' => function ($types, $search) {
        $search->query->andWhere(['in', 'prop_type', $types]);
    },
    'square' => function ($range, $search) {
        list($start, $end) = array_values(array_merge([
            'from' => 0,
            'to' => 9999999999
        ], $range));

        $search->query->andWhere(['>', 'square_feet', $start]);
        $search->query->andWhere(['<', 'square_feet', $end]);
    },
    'beds' => function ($val, $search) {
        $val = intval($val);
        $search->query->andWhere(['>=', 'no_bedrooms', $val]);
    },
    'baths' => function ($val, $search) {
        $val = intval($val);
        $search->query->andWhere(['>=', 'no_bathrooms', $val]);
    },
    'parking' => function ($val, $search) {
        $val = intval($val);
        $search->query->andWhere(['>=', 'parking_spaces', $val]);
    },
    'agrage' => function ($val, $search) {
        $val = intval($val);
        if ($val === 1) {
            $search->query->andWhere(['>', 'garage_spaces', 0]);
        } else {
            $search->query->andWhere(['=', 'garage_spaces', 0]);
        }
    },
    'market-days' => function ($val, $search) {
        if($value !== '') {
            $getRangeFns = [
                '1'=>function(){
                    $now = time();
                    return [$now - 86400 * 2, $now];
                },
                '2'=>function(){
                    $now = time();
                    return [$now - 86400 * 7, $now];
                },
                '3'=>function(){
                    $now = time();
                    return [$now - 86400 * 30, $now];
                }
            ];

            if(isset($getRangeFns[$value])) {
                $getRangeFn = $getRangeFns[$value];
                list($start, $end) = $getRangeFn();
                $start = date('Y-m-d', $start);
                $end = date('Y-m-d', $end);
                $search->query->andWhere('list_date>=:start and list_date <=:end', [
                    ':start'=>$start,
                    ':end'=>$end
                ]);
            }
        }
    },
    'school_district' => function ($townCode, $search) {
        $townCodes = explode('/', strtoupper($townCode));
        $search->query->andWhere(['in', 'town', $townCodes]);
        return $townCodes;
    },
    'subway_line' => function ($lineId, $search) {
        $search->query->andWhere(['@>', 'subway_lines', '{'.strtoupper($lineId).'}']);
    },
    'subway_stations' => function ($stationIds, $search) {
        $search->query->andWhere(['&&', 'subway_stations', '{'.implode(',', $stationIds).'}']);
    }
];
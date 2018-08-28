<?php
return [
    'latlon' => function ($vals) {
        list($lat, $lon) = explode(',', $vals);

        return ['latlng', ['lat' => $lat, 'lng' => $lon]];
    },
    'city_id' => function ($cityId) {
        if (\yii::$app->area->id === 'ma' && !is_numeric($cityId)) {
            $cityId = (new \yii\db\Query())
                ->from('town')
                ->select('id')
                ->where(['short_name' => $cityId])
                ->scalar();
        }
        return ['city_id', $cityId];
    },
    'list_price' => function ($range) {
        $priceRange = array_merge([
            'from' => 0,
            'to' => 9999999999
        ], $range);

        return ['price', $priceRange];
    },
    'prop-type' => function ($types) {
        return ['props', $types];
    },
    'square' => function ($range) {
        $squareRange = array_merge([
            'from' => 0,
            'to' => 9999999999
        ], $range);

        return ['square', $squareRange];
    },
    'beds' => function ($val) {
        return ['beds', intval($val)];
    },
    'baths' => function ($val) {
        return ['baths', $val];
    },
    'parking' => function ($val) {
        $val = intval($val);
        return ['parking', $val];
    },
    'agrage' => function ($val) {
        return ['garage', intval($val) === 1];
    },
    'market-days' => function ($val) {
        return ['ldays', intval($val)];
    },
    'school_district' => function ($townCode) {
        $townCodes = explode('/', $townCode);
        $cityIds = (new \yii\db\Query())
            ->from('town')
            ->select('id')
            ->where(['in', 'short_name', $townCodes])
            ->column();

        return ['city_ids', $cityIds];
    },
    'subway_line' => function ($lineId) {
        if (!is_numeric($lineId)) {
            $lineId = (new \yii\db\Query())
                ->from('subway_line')
                ->select('id')
                ->where(['code' => $lineId])
                ->scalar();
        }
        return ['subway_line', $lineId];
    },
    'subway_stations' => function ($stationIds) {
        if (is_string($stationIds)) $stationIds = explode(',', $stationIds);
        return ['subway_stations', $stationIds];
    }
];
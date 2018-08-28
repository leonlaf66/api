<?php
namespace module\listhub\estate\controllers;

use \module\listhub\estate\helpers\SearchGeneral;
use \module\listhub\estate\helpers\SearchMap;

class House
{
    public static function hotAreas($context, $req)
    {
        $area = \WS::$app->area;

        $hotCities = [
            'ny' => ['Great Neck', 'Jericho', 'Manhasset', 'Syosset', 'New Hyde Park', 'Garden City'],
            'il' => ['Chicago', 'Winnetka', 'Hinsdale', 'Clarendon Hills', 'Buffalo Grove'],
            'ga' => ['Atlanta', 'Johns Creek', 'Decatur', 'Alpharetta'],
            'ca' => ['Los Angeles', 'Santa Monica', 'Irvine', 'Temecula', 'San Marino', 'Palos Verdes Estates']
        ];

        $names = $hotCities[$area->id];
        $items = \models\City::find()
            ->where(['state' => $area->getStateId()])
            ->andWhere(['in', 'name', $names])
            ->all();

        $itemsMap = \common\helper\ArrayHelper::index($items, 'name');

        $resultItems = [];
        foreach($names as $name) {
            if (isset($itemsMap[$name])) {
                $d = $itemsMap[$name];
                $resultItems[$d->id] = tt($d->name, $d->name_cn);
            }
        }

        return $resultItems;
    }
}
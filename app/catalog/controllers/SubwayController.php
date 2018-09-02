<?php
namespace module\catalog\controllers;

use WS;
use models\SchoolDistrict;

class SubwayController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 地铁选项列表
     * @desc 地铁选项列表
     * @param string $area_id 区域id
     * @return [] - 地铁选项列表
     */
    public function actionMaps($area_id = 'ma')
    {
        if (\yii::$app->area->id !== 'ma') return [];

        $items = \models\SubwayStation::dictOptions();

        return array_map(function ($d) {
            unset($d['code']);
            unset($d['sort_order']);
            $d['name'] = tt($d['name'], $d['name_zh']);
            unset($d['name_zh']);
            $d['stations'] = array_values($d['stations']);
            foreach ($d['stations'] as $key => $station) {
                unset($d['stations'][$key]['line_code']);
                unset($d['stations'][$key]['latitude']);
                unset($d['stations'][$key]['longitude']);
                unset($d['stations'][$key]['sort_order']);
                $d['stations'][$key]['name'] = tt($d['stations'][$key]['name'], $d['stations'][$key]['name_zh']);
                unset($d['stations'][$key]['name_zh']);
            }
            return $d;
        }, $items);
    }
}

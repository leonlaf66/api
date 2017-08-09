<?php
namespace module\catalog\controllers;

use WS;
use common\catalog\SchoolDistrict;

class SubwayController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 地铁选项列表
     * @desc 地铁选项列表
     * @return [] list 地铁选项列表
     */
    public function actionMaps()
    {
        $items = \common\catalog\subway\Station::dictOptions();

        return array_map(function ($d) {
            unset($d['code']);
            unset($d['sort_order']);
            foreach ($d['stations'] as $key => $station) {
                unset($d['stations'][$key]['line_code']);
                unset($d['stations'][$key]['latitude']);
                unset($d['stations'][$key]['longitude']);
                unset($d['stations'][$key]['sort_order']);
            }
            return $d;
        }, $items);
    }
}

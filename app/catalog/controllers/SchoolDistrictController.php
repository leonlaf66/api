<?php
namespace module\catalog\controllers;

use WS;
use common\catalog\SchoolDistrict;

class SchoolDistrictController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 学区列表
     * @desc 学区列表
     * @return total 学区总数
     * @return items 学区集合 结构详见<a href="/help?schooldistrict-list-results" target="_blank">Results</a>
     * @return summery 汇总
     */
    public function actionList()
    {
        $items = SchoolDistrict::xFind()->all();

        $houseSummeryTotal = 0;
        $items = array_map(function ($d) use (& $houseSummeryTotal) {
            $houseSummeryTotal += $d->getSummary('total');
            $averagePrice = $d->getSummary('average-price');
            $averagePrice = tt('$'.$averagePrice, number_format(str_replace(',', '', $averagePrice) / 10000.0, 1).'万美元');
            $avgIncome = $d->special->special2;
            $avgIncome = tt('$'.$avgIncome, number_format(str_replace(',', '', $avgIncome) / 10000.0, 1).'万美元');

            return [
                'id' => $d->id,
                'code' => strtolower($d->code),
                'summary' => [
                    'house_total' => $d->getSummary('total'),
                    'average_price' => $averagePrice,
                ],
                'name' => $d->getItemData('name'),
                'image' => media_url('school-area/images').'/'.str_replace('/', '&', strtoupper($d->code)).'.jpg',
                'ranking' => $d->sort_order,
                'rating' => $d->rating,
                'sat' => $d->sat->local,
                'k12' => $d->k12,
                'student_num' => $d->special->special1,
                'government_subsidies' => $d->special->special3,
                'avg_income' => $avgIncome,
                'racial' => $d->racials
            ];
        }, $items);

        return [
            'total' => count($items),
            'items' => $items,
            'summery' => [
                'house_total' => $houseSummeryTotal
            ]
        ];
    }

    /**
     * 学区详情
     * @desc 学区详情
     * @param number $id 学区ID
     * @return object - 学区信息
     */
    public function actionGet($id)
    {
        $item = SchoolDistrict::findOne($id);

        // 获取热门房源
        $towns = explode('/', $item->code);
        $houses = \common\estate\RetsIndex::find()
            ->where(['in', 'town', $towns])
            ->andWhere(['=', 'prop_type', 'SF'])
            ->andWhere(['>', 'list_price', 700000])
            ->andWhere(['is_show' => true])
            ->orderBy(['id' => 'DESC'])
            ->limit(10)
            ->all();
        $houses = array_map(function ($d) {
            $e = $d->entity();
            $r = $e->render();
            return [
                'id' => $d->id,
                'location' => $e->location,
                'image' => $e->getPhoto(0, 500, 500),
                'list_price' => $d->list_price,
                'prop_type_name' => $r->get('prop_type_name')['value'],
                'rooms_descriptions' => $r->get('rooms_descriptions')['value']
            ];
        }, $houses);
        
        $k12 = $item->k12;
        $item = array_merge([
            'id' => $item->id,
            'summary' => [
                'house_total' => $item->getSummary('total'),
                'average_price' => $item->getSummary('average-price'),
            ],
            'image' => media_url('school-area/images').'/'.str_replace('/', '&', strtoupper($item->code)).'.jpg',
            'k12' => [],
            'hot_houses' => $houses
        ], (array)json_decode($item->json));

        $item['k12'] = $k12;

        // special相关字段更名
        $fieldRenameMaps = [
            'special1' => 'student_num',
            'special2' => 'avg_income',
            'special3' => 'government_subsidies',
            'special4' => 'asian_student_pct'
        ];
        foreach ($fieldRenameMaps as $field => $newField) {
            $item['special']->$newField = $item['special']->$field;
            unset($item['special']->$field);
        }

        $item['summary']['average_price'] = tt('$'.$item['summary']['average_price'], 
            number_format(str_replace(',', '', $item['summary']['average_price']) / 10000.0, 1).'万美元');
        $item['special']->avg_income = tt('$'.$item['special']->avg_income, 
            number_format(str_replace(',', '', $item['special']->avg_income) / 10000.0, 1).'万美元');

        return $item;
    }

    /**
     * 学区选项列表
     * @desc 学区选项列表
     * @return object - 学区选项列表
     */
    public function actionMaps()
    {
        $items = SchoolDistrict::xFind()->all();

        $items = array_map(function ($d) {
            $ns = $d->getItemData('name');
            return [
                'id' => strtolower($d->code),
                'name' => tt($ns[0], $ns[1])
            ];
        }, $items);

        return array_key_value($items, function ($d) {
            return [$d['id'], $d['name']];
        });
    }
}

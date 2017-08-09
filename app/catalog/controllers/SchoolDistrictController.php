<?php
namespace module\catalog\controllers;

use WS;
use common\catalog\SchoolDistrict;

class SchoolDistrictController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 学区列表
     * @desc 学区列表
     * @return [] list 学区列表 结构详见<a href="/help?schooldistrict-list-results">Results</a>
     */
    public function actionList()
    {
        $items = SchoolDistrict::xFind()->all();

        $houseSummeryTotal = 0;
        $items = array_map(function ($d) use (& $houseSummeryTotal) {
            $houseSummeryTotal += $d->getSummary('total');

            return [
                'id' => $d->id,
                'code' => strtolower($d->code),
                'summary' => [
                    'house_total' => $d->getSummary('total'),
                    'average_price' => $d->getSummary('average-price'),
                ],
                'name' => $d->getItemData('name'),
                'image' => media_url('school-area/images').'/'.str_replace('/', '&', strtoupper($d->code)).'.jpg',
                'ranking' => $d->sort_order,
                'rating' => $d->rating,
                'sat' => $d->sat->local,
                'k12' => $d->k12,
                'student_num' => $d->special->special1,
                'government_subsidies' => $d->special->special3,
                'avg_income' => $d->special->special2,
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
     * @return [] info 学区信息
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

        return $item;
    }

    /**
     * 学区选项列表
     * @desc 学区选项列表
     * @return [] list 学区选项列表
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

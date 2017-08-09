<?php
namespace module\estate\controllers;

use WS;
use common\estate\RetsIndex;
use module\estate\helpers\SearchGeneral;
use common\estate\helpers\Rets as RetsHelper;

class HouseController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 房源搜索
     * @desc 房源搜索
     * @param string $type 售房:purchase, 租房: lease, 默认为售房
     * @param string $q 搜索关键词(支持中/英文城市名, zipcode, 房源号, 以及全文搜索)
     * @param [] $filters:f 筛选器，查看<a href="/help?house-search-filters" target="_blank">Filters格式</a>
     * @param number $order 排序(1:价格升,2:价格降,3:房间数降,4:房间数升,默认:发布时间降)
     * @param number $page 指定的分页
     * @param number $page_size 指定分页大小
     * @return [] list 查询结果, 查看<a href="/help?house-search-results" target="_blank">Results格式</a>
     */
    public function actionSearch($type = 'purchase', $q = '', $order = 0, $page = 1, $page_size = 15)
    {
        // 请求参数
        $req = WS::$app->request;

        // search对象
        $search = RetsIndex::search();

        // 搜索参数应用
        SearchGeneral::apply($req, $search);

        // 分页处理
        $search->pagination->page = $page;
        $search->pagination->pageSize = $page_size;

        // 获取真实结果
        $results = RetsHelper::result($search);

        // 构造结果
        $items = [];
        foreach ($results as $rets) {
            $items[$rets->list_no] = [
                'location' => $rets->location,
                'image' => $rets->getPhoto(0, 800, 800),
                'images' => [
                    $rets->getPhoto(1, 600, 600),
                    $rets->getPhoto(2, 600, 600)
                ],
                'no_bedrooms' => intval($rets->no_bedrooms),
                'no_full_baths' => intval($rets->no_full_baths),
                'no_half_baths' => intval($rets->no_half_baths),
                'square_feet' => intval($rets->square_feet),
                'list_price' => floatval($rets->list_price),
                'prop_type_name' => $rets->propTypeName(),
                'status_name' => $rets->statusName(),
                'list_days_description' => $rets->getListDaysDescription(),
                'tags' => $rets->getTags()
            ];
        }

        // 返回
        $resuts = [
            'total' => $search->query->count(),
            'items' => $items,
            'options' => []
        ];

        if ($type === 'purchase') {
            $resuts['options']['prop_types'] = \common\estate\Rets::propertyTypeNames();
        }

        return $resuts;
    }

    /**
     * 房源详情
     * @desc 房源详情
     * @param number $id 房源ID
     * @return object info 房源信息, 查看<a href="/help?house-get-results" target="_blank">Results格式</a>
     */
    public function actionGet($id)
    {
        return [];
    }
}

<?php
namespace module\estate\controllers;

use WS;
use common\estate\RetsIndex;
use module\estate\helpers\SearchGeneral;
use common\estate\helpers\Rets as RetsHelper;
use module\estate\helpers\Detail as DetailHelper;

class HouseController extends \deepziyu\yii\rest\Controller
{   
    public $enableAuth = true;

    /** 
     * list 无需登陆验证
     */
    public function authOptional()
    {
        return ['search', 'get'];
    }

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
            $r = $rets->render();
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
                'list_price' => $r->get('list_price')['formatedValue'],
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
        $rets = \common\estate\Rets::findOne($id);
        
        if(is_null($rets )) {
            throw new \yii\web\HttpException(404, "Page not found");
        }

        $render = $rets->render();

        return [
            'id' => $rets->list_no,
            'location' => $rets->getLocation(),
            'list_price' => $render->get('list_price')['formatedValue'],
            'prop_type_name' => $render->get('prop_type_name')['value'],
            strtolower($rets->prop_type).'_type_name' => $render->get(strtolower($rets->prop_type).'_type_name')['value'],
            'no_bedrooms' => $rets->no_bedrooms,
            'no_full_baths' => $rets->no_full_baths,
            'no_half_baths' => $rets->no_half_baths,
            'square_feet' => $render->get('square_feet')['formatedValue'],
            'latitude' => $rets->latitude,
            'longitude' => $rets->longitude,
            'images' => $rets->getPhotos(),
            'roi' => DetailHelper::fetchRoi($rets),
            'details' => DetailHelper::fetchDetail($rets),
            'recommend_houses' => DetailHelper::fetchRecommends($rets)
        ];
    }

    /**
     * 房源收藏
     * @desc 添加或取消房源到收藏夹, 需要事先登陆
     * @param number $id 房源ID
     * @return object info
     */
    public function actionFavorite($id)
    {
        return \common\customer\RetsFavorite::addOrRemove($id, WS::$app->user->id);
    }

    /**
     * 看房预约
     * @method POST
     * @desc 新增看房预约, 需要事先登陆
     * @param number $id 房源ID
     * @data date $day 日期(天), 格式为yyyy-mm-dd，如"2018-12-24"
     * @data time $time_start 开始时间(精确到分), 格式为hh-MM，如"14:30"表过下午2点30分
     * @data time $time_end 结束时间(精确到分), 格式如上
     * @return bool/errors info 成功与否 或 错误信息
     */
    public function actionTour($id)
    {
        $req = WS::$app->request;

        $result = false;

        if($req->isPost) {
            $tour = new \common\estate\gotour\Tour();
            $tour->user_id = WS::$app->user->id;
            $tour->list_no = $id;
            $tour->date_start = $req->post('day').' '.$req->post('time_start') . ':00';
            $tour->date_end = $req->post('day').' '.$req->post('time_end') . ':00';
            if ($tour->validate()) {
                $result = $tour->save();
            } else {
                return $tour->getErrors();
            }
        }

        return $result;
    }
}

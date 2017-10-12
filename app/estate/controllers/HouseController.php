<?php
namespace module\estate\controllers;

use WS;
use common\estate\HouseIndex;
use module\estate\helpers\SearchGeneral;
use module\estate\helpers\SearchMap;
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
        return ['search', 'map-search', 'get', 'search-options', 'school-district-options', 'top', 'hot-areas'];
    }

    /**
     * 房源搜索
     * @desc 房源搜索
     * @param string $area_id 区域id
     * @param string $type 售房:purchase, 租房: lease, 默认为售房
     * @param string $q 搜索关键词(支持中/英文城市名, zipcode, 房源号, 以及全文搜索)
     * @param [] $filters:f 筛选器，查看<a href="/help?house-search-filters" target="_blank">Filters格式</a>
     * @param number $order 排序(1:价格升,2:价格降,3:房间数降,4:房间数升,默认:发布时间降)
     * @param number $page 指定的分页
     * @param number $page_size 指定分页大小
     * @return [] - 查询结果, 查看<a href="/help?house-search-results" target="_blank">Results格式</a>
     */
    public function actionSearch($area_id = 'ma', $type = 'purchase', $q = '', $order = 0, $page = 1, $page_size = 15)
    {
        // 请求参数
        $req = WS::$app->request;

        // search对象
        $search = HouseIndex::search();

        // 搜索参数应用
        SearchGeneral::apply($req, $search);

        // 分页处理
        $search->pagination->setPage(intval($page) - 1);
        $search->pagination->setPageSize($page_size);

        $items = [];
        if (intval($page) - 1 < 100) { // 限制最多100页
            // 获取真实结果
            $results = RetsHelper::result($search);

            foreach ($results as $rets) {
                $r = $rets->render();
                $items[] = [
                    'id' => $rets->list_no,
                    'name' => $r->get('name')['value'],
                    'location' => $rets->location,
                    'image' => $rets->getPhoto(0, 800, 800),
                    'images' => [
                        $rets->getPhoto(1, 600, 600),
                        $rets->getPhoto(2, 600, 600)
                    ],
                    'no_bedrooms' => intval($rets->no_bedrooms),
                    'no_full_baths' => intval($rets->no_full_baths),
                    'no_half_baths' => intval($rets->no_half_baths),
                    'square_feet' => $r->get('square_feet')['formatedValue'],
                    'list_price' => $r->get('list_price')['formatedValue'],
                    'prop_type_name' => $rets->propTypeName(),
                    'latitude' => $rets->latitude,
                    'longitude' => $rets->longitude,
                    'status_name' => $rets->statusName(),
                    'list_days_description' => $rets->getListDaysDescription(),
                    'tags' => $rets->getTags()
                ];
            }
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
     * 推荐房源
     * @desc 用于app首页的推荐房源列表
     * @param string $area_id 区域id
     * @param number $limit 只返回多少条
     * @return [] - 查询结果
     */
    public function actionTop($limit = 10)
    {
        $houses = [];

        $groups = \WS::getStaticData('home.rets.top');
        foreach ($groups as $items) {
            foreach ($items as $item) {
                if ($rets = \common\estate\Rets::findOne($item['list_no'])) {
                    $render = $rets->render();
                    $houses[] = [
                        'id' => $rets->list_no,
                        'name' => $render->get('name')['value'],
                        'location' => $rets->getLocation(),
                        'prop_type_name' => $rets->propTypeName(),
                        'list_price' => $render->get('list_price')['formatedValue'],
                        'image' => $rets->getPhoto(0, 800, 800),
                        'no_bedrooms' => intval($rets->no_bedrooms),
                        'no_full_baths' => intval($rets->no_full_baths),
                        'no_half_baths' => intval($rets->no_half_baths),
                        'square_feet' => $render->get('square_feet')['formatedValue'],
                        'status_name' => $rets->statusName(),
                        'list_days_description' => $rets->getListDaysDescription(),
                    ];
                }
            }
        }

        return array_slice($houses, 0, $limit);
    }

    /**
     * 地图房源搜索
     * @desc 地图房源搜索
     * @param string $area_id 区域id
     * @param string $type 售房:purchase, 租房: lease, 默认为售房
     * @param string $q 搜索关键词(支持中/英文城市名, zipcode, 房源号, 以及全文搜索)
     * @param [] $filters:f 筛选器，查看<a href="/help?house-search-filters" target="_blank">Filters格式</a>
     * @param number $limit 限制条数
     * @return [] items 查询结果
     * @return [] polygons 区域边界
     */
    public function actionMapSearch($area_id = 'ma', $type = 'purchase', $q = '', $limit=4000)
    {
        // 请求参数
        $req = WS::$app->request;

        // search对象
        $search = HouseIndex::search();

        // 搜索参数应用
        $townCodes = SearchMap::apply($req, $search);

        // 限制总返回数量
        $search->query->limit($limit);

        // 获取真实结果
        $search->query->select('id, list_price, prop_type, latitude, longitude');
        $results = $search->query->all();

        // 构造结果
        $items = [];
        foreach ($results as $d) {
            $items[] = implode('|', [
                $d->id,
                $d->list_price * 1.0 / 10000,
                $d->prop_type,
                $d->latitude,
                $d->longitude
            ]);
        }

        // 构造城市areas
        $polygons = [];
        if (! empty($townCodes)) {
            foreach ($townCodes as $code) {
                $cityId = strtolower(\models\Town::find()->where(['short_name'=>$code])->one()->name);
                $polygons = array_merge($polygons, \common\estate\helpers\Config::get('map.city.polygon/'.$cityId, []));
            }
        }

        // 返回
        return [
            'items' => $items,
            'polygons' => $polygons
        ];
    }

    /**
     * 房源详情
     * @desc 房源详情
     * @param number $id 房源ID
     * @param number $simple 是否仅返回简单信息, 1 简单 0 全全 
     * @return object - 房源信息, 查看<a href="/help?house-get-results" target="_blank">Results格式</a>
     */
    public function actionGet($id, $simple = '0')
    {
        $rets = \common\estate\Rets::findOne($id);
        
        if(is_null($rets )) {
            throw new \yii\web\HttpException(404, "Page not found");
        }

        $render = $rets->render();

        $options = \yii\helpers\ArrayHelper::merge([
            'image' => [
                'width' => '800',
                'height' => '800'
            ],
            'small_image' => [
                'width' => '400',
                'height' => '300'
            ]
        ], WS::$app->request->get('options', []));

        if ($simple === '0') {
            // 构造城市areas
            $cityId = strtolower(\models\Town::find()->where(['short_name'=>$rets->town])->one()->name);
            $cityId = str_replace(' ', '-', $cityId);
            $polygons = \common\estate\helpers\Config::get('map.city.polygon/'.$cityId, []);

            return [
                'id' => $rets->list_no,
                'name' => $render->get('name')['value'],
                'location' => $rets->getLocation(),
                'list_price' => $render->get('list_price')['formatedValue'],
                'prop_type_name' => $render->get('prop_type_name')['value'],
                strtolower($rets->prop_type).'_type_name' => $render->get(strtolower($rets->prop_type).'_type_name')['value'],
                'no_bedrooms' => is_null($rets->no_bedrooms) ? tt('Unknown', '未提供') : $rets->no_bedrooms,
                'no_full_baths' => is_null($rets->no_full_baths) ? tt('Unknown', '未提供') : $rets->no_full_baths,
                'no_half_baths' => $rets->no_half_baths ?? '0',
                'square_feet' => $render->get('square_feet')['formatedValue'],
                'area' => $render->get('area')['value'],
                'status_name' => $rets->statusName(),
                'list_days_description' => $rets->getListDaysDescription(),
                'latitude' => $rets->latitude,
                'longitude' => $rets->longitude,
                'images' => $rets->getPhotos($options['image']['width'], $options['image']['height']),
                'small_images' => $rets->getPhotos($options['small_image']['width'], $options['small_image']['height']),
                'roi' => DetailHelper::fetchRoi($rets),
                'details' => DetailHelper::fetchDetail($rets),
                'recommend_houses' => DetailHelper::fetchRecommends($rets),
                'polygons' => $polygons
            ];
        } else {
            return [
                'id' => $rets->list_no,
                'name' => $render->get('name')['value'],
                'location' => $rets->getLocation(),
                'list_price' => $render->get('list_price')['formatedValue'],
                'prop_type_name' => $render->get('prop_type_name')['value'],
                'no_bedrooms' => is_null($rets->no_bedrooms) ? tt('Unknown', '未提供') : $rets->no_bedrooms,
                'no_full_baths' => is_null($rets->no_full_baths) ? tt('Unknown', '未提供') : $rets->no_full_baths,
                'no_half_baths' => $rets->no_half_baths ?? '0',
                'square_feet' => $render->get('square_feet')['formatedValue'],
                'status_name' => $rets->statusName(),
                'list_days_description' => $rets->getListDaysDescription(),
                'image' => $rets->getPhoto()
            ];
        }
    }

    /**
     * 房源收藏
     * @desc 添加或取消房源到收藏夹, 需要事先登陆
     * @param number $id 房源ID
     * @return - info
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
     * @return bool/errors - 成功与否 或 错误信息
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

    /**
     * 搜索选项待选列表
     * @param string $area_id 区域id
     * @desc 搜索选项待选列表
     * @return [] - 待选列表
     */
    public function actionSearchOptions($area_id = 'ma')
    {
        $resultItems = [];

        $towns = \models\Town::find()->where([
            'state'=>'MA'
        ])->all();

        foreach ($towns as $town) {
            $resultItems[] = [
                'title' => $town->name,
                'desc' => $town->name_cn.',MA'
            ];
            
            if ($town->name_cn) {
                $resultItems[] = [
                    'title' => $town->name_cn,
                    'desc' => $town->name.',MA'
                ];
            }
        }

        $zipcodes = \models\ZipcodeTown::find()->where([
            'state'=>'MA'
        ])->all();

        foreach ($zipcodes as $zipcode) {
            $resultItems[] = [
                'title' => $zipcode->zip,
                'desc' => $zipcode->city_name.','.$zipcode->city_name_cn.',MA'
            ];
        }

        return $resultItems;
    }

    /**
     * 热门区域
     * @desc 房源热门区域，可以应用到房源搜索的filters条件中
     * @param string $area_id 区域id
     * @return [] - 区域列表
     */
    public function actionHotAreas()
    {
        $names = ['Malden', 'Cambridge', 'Lexington', 'Newton', 'Brookline', 'Waltham', 'Boston', 'Arlington', 'Quincy'];
        $items = \models\Town::find()
            ->where(['state' => 'MA'])
            ->andWhere(['in', 'name', $names])
            ->all();

        $itemsMap = \common\helper\ArrayHelper::index($items, 'name');

        $resultItems = [];
        foreach($names as $name) {
            if (isset($itemsMap[$name])) {
                $d = $itemsMap[$name];
                $resultItems[strtolower($d->short_name)] = tt($d->name, $d->name_cn);
            }
        }

        return $resultItems;
    }
}

<?php
namespace module\estate\controllers;

use WS;
use common\estate\HouseIndex;
use module\estate\helpers\SearchGeneral;
use module\estate\helpers\SearchMap;
use \yii\helpers\ArrayHelper;
use common\estate\helpers\Rets as RetsHelper;
use module\estate\helpers\Detail as DetailHelper;
use module\estate\helpers\FieldFilter;

class HouseController extends \deepziyu\yii\rest\Controller
{   
    public $enableAuth = true;

    /** 
     * list 无需登陆验证
     */
    public function authOptional()
    {
        return ['search', 'map-search', 'get', 'search-options', 'school-district-options', 'top', 'hot-areas', 'data'];
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
     * @return [] - 查询结果, 查看<a href="/help?house-search-results" target="_blank">Results格式</a>
     */
    public function actionSearch($type = 'purchase', $q = '', $order = '0', $page = 1, $page_size = 15)
    {
        $q = urldecode($q);

        // filters
        $targetFilters = [];
        $filtersMap = include(__DIR__.'/../etc/filters.php');
        foreach (app('request')->get('filters', []) as $sfield => $val) {
            if (isset($filtersMap[$sfield])) {
                list($targetField, $targetVal) = ($filtersMap[$sfield])($val);
                $targetFilters[$targetField] = $targetVal;
            }
        }

        // order
        $sort = $order + 1; // +1刚刚对上

        // 请求graphql服务
        $result = app('graphql')->request('search-houses', [
            'only_rental' => $type !== 'purchase',
            'q' => $q,
            'first' => $page_size,
            'skip' => ( $page - 1 ) * $page_size,
            'filters' => $targetFilters,
            'sort' => $sort
        ])->result;

        // 渲染结果
        $result->items = array_map(function ($d) {
            return FieldFilter::listItem($d);
        }, $result->items);

        // 附加options
        $result->options = [];

        if ($type === 'purchase') {
            $result->options['prop_types'] = \common\estate\Rets::propertyTypeNames();
        }

        // 返回
        return $result;
    }

    /**
     * 推荐房源
     * @desc 用于app首页的推荐房源列表
     * @param number $limit 只返回多少条
     * @return [] - 查询结果
     */
    public function actionTop($limit = 10)
    {
        // 请求graphql服务
        $result = app('graphql')->request('top-houses')->result;

        $items = array_map(function ($group) {
            return FieldFilter::listItem($group->house);
        }, (Array)$result);

        return array_slice($items, 0, $limit);
    }

    /**
     * 地图房源搜索
     * @desc 地图房源搜索
     * @param string $type 售房:purchase, 租房: lease, 默认为售房
     * @param string $q 搜索关键词(支持中/英文城市名, zipcode, 房源号, 以及全文搜索)
     * @param [] $filters:f 筛选器，查看<a href="/help?house-search-filters" target="_blank">Filters格式</a>
     * @param number $limit 限制条数
     * @return [] items 查询结果
     * @return [] polygons 区域边界
     */
    public function actionMapSearch($type = 'purchase', $q = '', $limit=1000)
    {
        // filters
        $q = urldecode($q);
        $targetFilters = [];
        $filtersMap = include(__DIR__.'/../etc/filters.php');
        foreach (app('request')->get('filters', []) as $sfield => $val) {
            if (isset($filtersMap[$sfield])) {
                list($targetField, $targetVal) = ($filtersMap[$sfield])($val);
                $targetFilters[$targetField] = $targetVal;
            }
        }

        $result = app('graphql')->request('map-houses', [
            'only_rental' => $type !== 'purchase',
            'q' => $q,
            'first' => $limit,
            'filters' => $targetFilters
        ])->result;

        $result->polygons = [];

        return $result;
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
        // 请求参数
        $req = \WS::$app->request;

        // 返回简单(用于地图弹框)
        if ($simple === '1') {
            return $this->actionGetSimple($id);
        }

        // 请求选项
        $options = ArrayHelper::merge([
            'image' => [
                'width' => '800',
                'height' => '800'
            ],
            'small_image' => [
                'width' => '400',
                'height' => '300'
            ]
        ], app('request')->get('options', []));

        // 请求graphql服务
        $house = app('graphql')->request('house-full-get', [
            'list_no' => $id,
            'image_w' => $options['image']['width'],
            'image_h' => $options['image']['height'],
            'small_image_w' => $options['small_image']['width'],
            'small_image_h' => $options['small_image']['height']
        ])->result;

        // 关联房源
        $associatedHouses = array_map(function ($d) {
            return FieldFilter::listItem($d);
        }, $house->associated_houses);

        // taxes
        $taxes = [
            'id' => 'taxes',
            'title'=>'',
            'rawValue' => $house->taxes,
            'value' => FieldFilter::money($house->taxes, false),
            'formatedValue' => FieldFilter::money($house->taxes)
        ];

        $taxes[app()->language === 'en-US' ? 'prefix' : 'suffix']
            = str_replace($taxes['value'], '', $taxes['formatedValue']);

        // detail
        $details = array_map(function ($group) {
            foreach ($group->items as $id => $item) {
                $item->id = $id;
                $item->rawValue = $item->raw_value;
                unset($item->raw_value);

                $item->formatedValue = $item->value;
                if (isset($item->prefix)) {
                    $item->formatedValue = $item->prefix . $item->formatedValue;
                }
                if (isset($item->suffix)) {
                    $item->formatedValue = $item->formatedValue . $item->suffix;
                }
            }

            return $group;
        }, $house->details);

        return [
            'id' => $house->id,
            'name' => $house->nm,
            'location' => $house->loc,
            'list_price' => FieldFilter::money($house->price),
            'prop_type_name' => FieldFilter::housePropName($house->prop),
            strtolower($house->prop.'_type_name') => FieldFilter::unknown(''), // 暂不提供
            'no_bedrooms' => FieldFilter::unknown($house->beds),
            'no_full_baths' => FieldFilter::unknown($house->baths[0]),
            'no_half_baths' => FieldFilter::unknown($house->baths[1]),
            'square_feet' => FieldFilter::square($house->square_feet),
            'est_sale' => $house->est_sale,
            'area' => FieldFilter::unknown($house->area),
            'status_name' => FieldFilter::statusName($house->status, $house->prop),
            'list_days_description' => FieldFilter::listDayDesc(round((time() - strtotime($house->date)) / 3600 / 24)),
            'latitude' => $house->latlng[0],
            'longitude' => $house->latlng[1],
            'images' => $house->images,
            'taxes' => $taxes,
            'small_images' => $house->small_images,
            'roi' => array_map(function ($val) {
                return FieldFilter::unknown($val);
            }, (Array)$house->roi),
            'details' => $details,
            'recommend_houses' => $associatedHouses,
            'polygons' => $house->polygons
        ];
    }

    /**
     * 房源详情(简单, 用于地图弹框)
     * @desc 房源详情
     * @param number $id 房源ID
     * @return object - 房源信息, 查看<a href="/help?house-get-results" target="_blank">Results格式</a>
     */
    public function actionGetSimple($id)
    {
        // 请求graphql服务
        $house = app('graphql')->request('house-simple-get', [
            'list_no' => $id
        ])->result;

        return [
            'id' => $house->id,
            'name' => $house->nm,
            'location' => $house->loc,
            'list_price' => FieldFilter::money($house->price),
            'prop_type_name' => FieldFilter::housePropName($house->prop),
            'no_bedrooms' => FieldFilter::unknown($house->beds),
            'no_full_baths' => FieldFilter::unknown($house->baths[0]),
            'no_half_baths' => FieldFilter::unknown($house->baths[1]),
            'square_feet' => FieldFilter::square($house->square_feet),
            'status_name' => FieldFilter::statusName($house->status, $house->prop),
            'list_days_description' => FieldFilter::listDayDesc(round((time() - strtotime($house->date)) / 3600 / 24)),
            'image' => $house->photo
        ];
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
            $tour->area_id = \WS::$app->area->id;
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
     * @desc 搜索选项待选列表
     * @return [] - 待选列表
     */
    public function actionSearchOptions()
    {
        return app('graphql')->request('autocomplete_cities')->result;
    }

    /**
     * 热门区域
     * @desc 房源热门区域，可以应用到房源搜索的filters条件中
     * @return [] - 区域列表
     */
    public function actionHotAreas()
    {
        $req = \WS::$app->request;

        if (\WS::$app->area->id !== 'ma') {
            return \module\listhub\estate\controllers\House::hotAreas($this, $req);
        }

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

<?php
namespace module\listhub\estate\controllers;

use \module\listhub\estate\helpers\SearchGeneral;
use \module\listhub\estate\helpers\SearchMap;

class House
{
    public static function search($context, $req)
    {
        $type = $req->get('type', 'purchase');
        $page = $req->get('page', 1);
        $page_size = $req->get('page_size', 15);

        $search = \common\listhub\estate\House::search(\WS::$app->area->stateId);

        // 搜索参数应用
        SearchGeneral::apply($req, $search->query);

        // 分页处理
        $search->pagination->setPage(intval($page) - 1);
        $search->pagination->setPageSize($page_size);

        $items = [];
        if (intval($page) - 1 < 100) { // 限制最多100页
            $items = \module\listhub\estate\helpers\ListResult::renderItems($search->getModels());
        }

        // 返回
        $resuts = [
            'total' => $search->query->count(),
            'items' => $items,
            'options' => []
        ];

        if ($type === 'purchase') {
            $resuts['options']['prop_types'] = \common\listhub\estate\References::getPropTypeNames();
        }

        return $resuts;
    }

    public static function get($context, $req)
    {
        $area = \WS::$app->area;
        $id = $req->get('id');
        $simple = $req->get('simple', '0');
        $rets = \common\listhub\estate\House::findOne($id);

        if(is_null($rets )) {
            throw new \yii\web\HttpException(404, "Page not found");
        }

        if ($simple === '0') {
            $recommendHouses = $rets->recommends($area->id);
            $recommendHouses = \module\listhub\estate\helpers\ListResult::renderItems($recommendHouses);
            return [
                'id' => $rets->id,
                'name' => $rets->title(),
                'location' => $rets->location,
                'list_price' => $rets->getFieldData('list_price')['formatedValue'],
                'prop_type_name' => $rets->getFieldData('prop_type_name')['value'],
                strtolower($rets->prop_type).'_type_name' => tt('Unknown', '未提供'),//$render->get(strtolower($rets->prop_type).'_type_name')['value'],
                'no_bedrooms' => is_null($rets->no_bedrooms) ? tt('Unknown', '未提供') : $rets->no_bedrooms,
                'no_full_baths' => is_null($rets->no_full_baths) ? tt('Unknown', '未提供') : $rets->no_full_baths,
                'no_half_baths' => $rets->no_half_baths ?? '0',
                'square_feet' => $rets->getFieldData('square_feet')['formatedValue'],
                'area' => tt('Unknown', '未提供'),//$rets->get('area')['value'],
                'status_name' => $rets->statusName(),
                'list_days_description' => $rets->getListDaysDescription(),
                'latitude' => $rets->latitude,
                'longitude' => $rets->longitude,
                'images' => array_map(function ($po) {return $po['url'];}, $rets->getPhotos()),
                'small_images' => array_map(function ($po) {return $po['url'];}, $rets->getPhotos()),
                'taxes' => $rets->getFieldData('taxes'),
                'est_sale' => null,
                'roi' => [
                    'est_roi_cash' => '0.00%',
                    'ave_roi_cash' => '0.00%',
                    'est_annual_income_cash' => '$0.00',
                    'ave_annual_income_cash' => '$0.00'
                ],//DetailHelper::fetchRoi($rets),
                'details' => $rets->getDetail(),
                'recommend_houses' => $recommendHouses,
                'polygons' => $rets->getPolygons()
            ];
        } else {
            return [
                'id' => $rets->id,
                'name' => $rets->title(),
                'location' => $rets->location,
                'list_price' => $rets->getFieldData('list_price')['formatedValue'],
                'prop_type_name' => $rets->getFieldData('prop_type_name')['value'],
                'no_bedrooms' => is_null($rets->no_bedrooms) ? tt('Unknown', '未提供') : $rets->no_bedrooms,
                'no_full_baths' => is_null($rets->no_full_baths) ? tt('Unknown', '未提供') : $rets->no_full_baths,
                'no_half_baths' => $rets->no_half_baths ?? '0',
                'square_feet' => $rets->getFieldData('square_feet')['formatedValue'],
                'status_name' => $rets->statusName(),
                'list_days_description' => $rets->getListDaysDescription(),
                'image' => $rets->getPhoto()['url']
            ];
        }
    }

    public static function mapSearch($context, $req)
    {
        $area = \WS::$app->area;
        $stateId = $area->getStateId();
        $limit = $req->get('limit', 4000);

        $query = (new \yii\db\Query())
            ->from('listhub_index')
            ->select('id, list_price, prop_type, latitude, longitude')
            ->where(['state' => $stateId])
            ->andWhere('latitude is not null and longitude is not null')
            ->andWhere('list_price>0')
            ->andWhere(['is_show' => true])
            ->limit($limit);

        // 搜索参数应用
        $city = SearchMap::apply($req, $query);

        // 获取真实结果
        $houses = $query->all();

        // 构造结果
        $items = [];
        foreach ($houses as $d) {
            $items[] = implode('|', [
                $d['id'],
                $d['list_price'] * 1.0 / 10000,
                $d['prop_type'],
                $d['latitude'],
                $d['longitude']
            ]);
        }

        $polygons = [];
        if ($city) {
            $cityName = $city->name;
            $cityName = strtolower($cityName);
            $cityName = str_replace(' ', '-', $cityName);
            $polygons = \WS::getStaticData('polygons/'.$stateId.'/'.$cityName, []);
        }

        return [
            'items' => $items,
            'polygons' => $polygons
        ];
    }

    public static function searchOptions($context, $req)
    {
        $state = \WS::$app->area->getStateId();

        return \models\City::getSearchList($state, function ($query) use($req) {
            $type = $req->get('type', 'purchase');
            if ($type === 'purchase') {
                $query->innerJoin('listhub_index i', "e.id=i.city_id and i.prop_type<>'RN'");
            } else {
                $query->innerJoin('listhub_index i', "e.id=i.city_id and i.prop_type='RN'");
            }
        });
    }

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

    public static function data($context, $req)
    {
        return [];
    }

    public static function top($context, $req)
    {
        $areaId = \WS::$app->area->id;
        $limit = $req->get('limit', 10);

        $items = \models\SiteSetting::get('home.luxury.houses', $areaId);

        foreach ($items as $item) {
            if ($rets = \common\listhub\estate\House::findOne($item['id'])) {
                $houses[] = \module\listhub\estate\helpers\ListResult::renderItem($rets);
            }
        }

        return array_slice($houses, 0, $limit);
    }
}
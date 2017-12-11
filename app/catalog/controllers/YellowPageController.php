<?php
namespace module\catalog\controllers;

use WS;
use models\YellowPage;

class YellowPageController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 黄页列表
     * @desc 黄页列表
     * @param number $city_id 城市id
     * @param number $type_id 类型id
     * @param number $page:f
     * @param number $page_size:f;
     * @return number total 黄页总数
     * @return [] items 黄页结果集合
     */
    public function actionList($page = 1, $page_size = 25, $city_id = '', $type_id='')
    {
        $areaId = \WS::$app->area->id;

        $search = YellowPage::search($areaId);

        // 应用城市过滤
        if ($city_id !== '') {
            $search->query
                ->innerJoinWith(['city' => function ($q) use ($city_id) {
                    $q->andWhere('city_id=:id', [':id' => $city_id]);
                }]);
        }

        // 应用类型过滤
        if ($type_id !== '') {
            $search->query
                ->innerJoinWith(['type' => function ($q) use ($type_id) {
                    $q->andWhere('type_id=:id', [':id' => $type_id]);
                }]);
        }

        $search->query->orderBy('rating DESC, hits DESC, id DESC');

        // 分页处理
        $search->pagination->setPage(intval($page) - 1);
        $search->pagination->pageSize = $page_size;

        $items = $search->getModels();
        $items = array_map(function ($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'business' => tt($d->business, $d->business_cn),
                'address' => $d->address,
                'contact' => $d->contact,
                'license' => $d->license,
                'photo_url' => $d->getPhotoImageInstance()->resize(300)->getUrl(),
                'phone' => $d->phone,
                'rating' => $d->rating,
                'comments' => $d->comments,
                'hits' => $d->hits
            ];
        }, $items);
    
        return [
            'total' => $search->query->count(),
            'items' => $items
        ];
    }

    /**
     * 商家黄页推荐
     * @desc 商家黄页推荐
     * @return [] - 黄页结果集合
     */
    public function actionRecommends()
    {
        $areaId = \WS::$app->area->id;
        $groups = \WS::getStaticData('home.yellowpage.top.'.$areaId);

        $ids = [];
        foreach($groups as $group) {
            $items = $group['ids'];
            $ids = array_merge($ids, $items);
        }
        $ids = array_unique($ids);

        $allYellowpages = YellowPage::find()->where(['in', 'id', $ids])->all();
        $allYellowpages = \common\helper\ArrayHelper::entityMap($allYellowpages, 'id');

        $results = [];

        foreach($groups as $gIdx=>$group) {
            $items = [];
            foreach($group['ids'] as $id) {
                if(isset($allYellowpages[$id])) {
                    $items[] = $allYellowpages[$id];
                }
            }

            $items = array_map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'rating' => $d->rating,
                    'photo' => $d->getPhotoImageInstance()->resize(90, 90)->getUrl()
                ];
            }, $items);

            $results[] = [
                'id' => $group['id'],
                'name' => $group['name'],
                'items' => $items
            ];
        }

        return $results;
    }

    /**
     * 黄页详情
     * @desc 黄页详情
     * @param number $id 黄页id
     * @return object - 黄页结果
     */
    public function actionGet($id)
    {
        $item = YellowPage::findOne($id);
        return [
            'id' => $item->id,
            'name' => $item->name,
            'business' => tt($item->business, $item->business_cn),
            'address' => $item->address,
            'contact' => $item->contact,
            'license' => $item->license,
            'photo_url' => $item->getPhotoImageInstance()->resize(300)->getUrl(),
            'website' => $item->website,
            'intro' => $item->intro,
            'phone' => $item->phone,
            'email' => $item->email,
            'rating' => $item->rating,
            'comments' => $item->comments,
            'hits' => $item->hits
        ];
    }

    /**
     * 黄页点击
     * @desc 黄页点击，用于记录点击数
     * @param number $id 黄页id
     * @return number - 执行结果
     */
    public function actionHit($id)
    {
        return YellowPage::hit($id);
    }

    /**
     * 黄页类型列表获取
     * @return [] - 类型集合
     */
    public function actionTypes()
    {
        $items = \models\TaxonomyTerm::typeOptions(2);

        $resultItems = [];
        foreach ($items as $id => $d) {
            $icon = '';
            if (preg_match('/\[(.*)\]/', $d, $match)) {
                $icon = $match[1];
            }

            $resultItems[] = [
                'id' => $id,
                'name' => t('yp/tag', preg_replace('/\[.*\]/', '', $d)),
                'icon' => media_url('yellowpage/types/'.$icon)
            ];
        }

        return $resultItems;
    }

    /**
     * 黄页城市列表获取
     * @return [] - 城市集合
     */
    public function actionCities()
    {
        $stateId = \WS::$app->area->getStateId();
        if ($stateId !== 'MA') {
            return \models\City::mapOptions($stateId);
        }
        return \models\Town::mapOptions();
    }
}

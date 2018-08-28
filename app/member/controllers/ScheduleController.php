<?php
namespace module\member\controllers;

use module\estate\helpers\FieldFilter;

class ScheduleController extends \deepziyu\yii\rest\Controller
{
    public $enableAuth = true;

    /**
     * 房源预约列表
     * @desc 获取房源预约列表, 需要登陆，只能当前已登陆的用户的房源预约列表
     * @param number $page 分页
     * @param number $page_size 分页大小
     * @return number total 预约总数
     * @return [] items 预约集合
     */
    public function actionList($page = 1, $page_size = 15)
    {
        if ($page == '0') $page = 1;
        
        $result = app('graphql')->request('schedule', [
            'first' => $page_size,
            'skip' => ($page - 1) * $page_size
        ], [
            'access-token' => app()->request->get('access-token')
        ])->result;

        $result->items = array_map(function ($item) {
            $item->house = FieldFilter::listItem($item->house);
            $item->status = $item->is_confirmed ? '1' : '0';
            unset($item->is_confirmed);
            $item->date_start = date('Y-m-d H:i:s', strtotime($item->date_start));
            $item->date_end = date('Y-m-d H:i:s', strtotime($item->date_end));
            return $item;
        }, $result->items);

        return $result;


        $query = \common\estate\gotour\Tour::findByUser(\WS::$app->user->id)
            ->offset(($page - 1) * $page_size)
            ->limit($page_size);

        $items = $query->all();

        $items = array_map(function ($d) {
            $e = $d->getRets();

            $result = [
                'id' => $d->id,
                'house' => [],
                'date_start' => $d->date_start,
                'date_end' => $d->date_end,
                'status' => $d->status
            ];

            if ($e instanceof \common\estate\Rets) {
                $r = $e->render();
                $result['house'] = [
                    'id' => $e->list_no,
                    'name' => $r->get('name')['value'],
                    'location' => $e->location,
                    'image' => $e->getPhoto(0, 800, 800),
                    'images' => [
                        $e->getPhoto(1, 600, 600),
                        $e->getPhoto(2, 600, 600)
                    ],
                    'no_bedrooms' => intval($e->no_bedrooms),
                    'no_full_baths' => intval($e->no_full_baths),
                    'no_half_baths' => intval($e->no_half_baths),
                    'square_feet' => $r->get('square_feet')['formatedValue'],
                    'list_price' => $r->get('list_price')['formatedValue'],
                    'prop_type_name' => $e->propTypeName(),
                    'latitude' => $e->latitude,
                    'longitude' => $e->longitude,
                    'status_name' => $e->statusName(),
                    'list_days_description' => $e->getListDaysDescription(),
                    'tags' => $e->getTags()
                ];
            } elseif ($e instanceof \common\listhub\estate\House) {
                $result['house'] = [
                    'id' => $d->id,
                    'name' => $e->title(),
                    'location' => $e->getLocation(),
                    'image' => $e->getPhoto(0)['url'],
                    'images' => [
                        $e->getPhoto(1)['url'],
                        $e->getPhoto(2)['url'],
                    ],
                    'no_bedrooms' => $e->no_bedrooms,
                    'no_full_baths' => $e->no_full_baths,
                    'no_half_baths' => $e->no_half_baths,
                    'square_feet' => $e->getFieldData('square_feet')['formatedValue'],
                    'list_price' => $e->getFieldData('list_price')['formatedValue'],
                    'prop_type_name' => $e->propTypeName(),
                    'latitude' => $e->latitude,
                    'longitude' => $e->longitude,
                    'status_name' => $e->statusName(),
                    'list_days_description' => $e->getListDaysDescription(),
                    'tags' => $e->getTags()
                ];
            }

            return $result;
        }, $items);

        return [
            'total' => $query->count(),
            'items' => $items
        ];
    }

    /**
     * 删除房源预约
     * @desc 删除房源预约, 需要登陆，只能删除已登陆的用户的预约
     * @param number $id 需删除的看房预约ID
     * @return bool - 删除结果信息
     */
    public function actionRemove($id)
    {
        $item = \common\estate\gotour\Tour::findOne($id);
        if ($item->user_id !== \WS::$app->user->id) {
            throw new \yii\web\HttpException(404, '不存在的预约!');
        }

        return $item->delete();
    }
}

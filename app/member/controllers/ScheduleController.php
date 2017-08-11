<?php
namespace module\member\controllers;

class ScheduleController extends \deepziyu\yii\rest\Controller
{   
    public $enableAuth = true;

    /**
     * 房源预约列表
     * @desc 获取房源预约列表, 需要登陆，只能当前已登陆的用户的房源预约列表
     * @param number $page 分页
     * @param number $page_size 分页大小
     * @return [] $list 预约集合
     */
    public function actionList($page = 1, $page_size = 15)
    {
        $query = \common\estate\gotour\Tour::findByUser(\WS::$app->user->id)
            ->offset(($page - 1) * $page_size)
            ->limit($page_size);

        $items = $query->all();

        $items = array_map(function ($d) {
            $e = $d->getRets();
            $r = $e->render();
            $price = $r->get('list_price');

            return [
                'id' => $d->id,
                'house' => [
                    'id' => $e->list_no,
                    'location' => $e->getLocation(),
                    'image' => $e->getPhoto(0),
                    'list_price' => $price['formatedValue'],
                    'status_name' => $e->statusName()
                ],
                'date_start' => $d->date_start,
                'date_end' => $d->date_start,
                'status' => $d->status
            ];
        }, $items);

        return [
            'total' => $query->count(),
            'items' => $items
        ];
    }

    /**
     * 删除房源预约
     * @desc 删除房源预约, 需要登陆，只能删除已登陆的用户的预约
     * @return bool $result 删除结果信息
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
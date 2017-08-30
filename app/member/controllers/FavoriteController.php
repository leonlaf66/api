<?php
namespace module\member\controllers;

class FavoriteController extends \deepziyu\yii\rest\Controller
{   
    public $enableAuth = true;

    /**
     * 房源收藏夹
     * @desc 获取房源收藏夹列表, 需要登陆，只能当前已登陆的用户的收藏夹列表
     * @param string $type 房源类型，purchase-售房 lease-租房
     * @param number $page 分页
     * @param number $page_size 分页大小
     * @return number total 总数
     * @return [] items 收藏集合
     */
    public function actionList($type = 'purchase', $page = 1, $page_size = 15)
    {
        if (! in_array($type, ['purchase', 'lease'])) {
            throw new \yii\web\HttpException(405, '未知的房源类型指定:'.$type);
            
        }

        $query = \common\customer\RetsFavorite::findByUserId(\WS::$app->user->id)
            ->where([
                'user_id' => \WS::$app->user->id
            ])
            ->offset(($page - 1) * $page_size)
            ->limit($page_size);

        if ($type === 'purchase') {
            $query->andWhere(['<>', 'property_type', 'RN']);
        } else {
            $query->andWhere(['=', 'property_type', 'RN']);
        }

        $items = $query->all();

        $items = array_map(function ($d) {
            $e = $d->getRets();
            $r = $e->render();
            // $price = $r->get('list_price');

            return [
                'id' => $d->id,
                'house' => [
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
                    /*
                    'id' => $e->list_no,
                    'location' => $e->getLocation(),
                    'image' => $e->getPhoto(0),
                    'list_price' => $price['formatedValue'],
                    'status_name' => $e->statusName()*/
                ],
                'created_at' => $d->created_at
            ];
        }, $items);

        return [
            'total' => $query->count(),
            'items' => $items
        ];
    }

    /**
     * 删除房源收藏
     * @desc 删除房源收藏, 需要登陆，只能删除已登陆的用户的收藏
     * @return bool - 删除结果信息
     */
    public function actionRemove($id)
    {
        $item = \common\customer\RetsFavorite::findOne($id);
        if ($item->user_id !== \WS::$app->user->id) {
            throw new \yii\web\HttpException(404, '不存在的收藏!');
        }
        
        return $item->delete();
    }
}

<?php
namespace module\member\controllers;

use module\estate\helpers\FieldFilter;

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
        $result = app('graphql')->request('favorite', [
            'only_rental' => $type !== 'purchase',
            'first' => $page_size,
            'skip' => ($page - 1) * $page_size
        ], [
            'access-token' => app()->request->get('access-token')
        ])->result;

        $result->items = array_map(function ($item) {
            $item->id = intval($item->id);
            $item->house = FieldFilter::listItem($item->house);
            return $item;
        }, $result->items);

        return $result;
    }

    /**
     * 删除房源收藏
     * @desc 删除房源收藏, 需要登陆，只能删除已登陆的用户的收藏
     * @param number $id 需删除的房源收藏ID
     * @return bool - 删除结果信息
     */
    public function actionRemove($id)
    {
        $item = \models\MemberHouseFavority::findOne($id);
        if ($item->user_id !== \WS::$app->user->id) {
            throw new \yii\web\HttpException(404, '不存在的收藏!');
        }
        
        return $item->delete();
    }
}

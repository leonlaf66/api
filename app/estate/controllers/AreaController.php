<?php
namespace module\estate\controllers;

class AreaController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 房产区域接口
     * @desc 用于获取房产大区的列表
     * @return [] list 区域列表
     */
    public function actionList()
    {
        return [
            'ma' => [
                'name' => tt('Boston', '波士顿'),
                'desc' => tt('Massachusetts', '马萨诸塞州'),
                'image_url' => media_url('area/ma.jpg')
            ],
            'ny' => [
                'name' => tt('New York', '纽约'),
                'desc' => tt('NYC, Long island, Brooklyn', '纽约市，长岛，布鲁克林'),
                'image_url' => media_url('area/b.jpg')
            ],
            'va' => [
                'name' => tt('Washington', '华盛顿'),
                'desc' => tt('DC, MD, VA', '华盛顿DC，弗吉尼亚，马里兰'),
                'image_url' => media_url('area/c.jpg')
            ]
        ];
    }
}

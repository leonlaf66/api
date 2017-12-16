<?php
namespace module\estate\controllers;

class AreaController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 房产区域接口
     * @desc 用于获取房产大区的列表
     * @return [] - 区域列表
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
                'image_url' => media_url('area/ny.jpg')
            ],
            'ga' => [
                'name' => tt('Atlanta', '亚特兰大'),
                'desc' => tt('Georgia', '佐治亚州'),
                'image_url' => media_url('area/ga.jpg')
            ],
            'ca' => [
                'name' => tt('Los Angel', '洛杉矶'),
                'desc' => tt('California', '加利福尼亚'),
                'image_url' => media_url('area/ca.jpg')
            ],
            'il' => [
                'name' => tt('Chicago', '芝加哥'),
                'desc' => tt('Illinois', '伊利诺斯'),
                'image_url' => media_url('area/il.jpg')
            ],
        ];
    }
}

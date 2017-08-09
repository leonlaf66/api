<?php
namespace module\estate\helpers;

use WS;

class Detail
{
    public static function fetchRoi($rets)
    {
        $listNo = $rets->list_no;
        $zipCode = $rets->zip_code;

        $result = WS::$app->db->createCommand('select * from rets_roi where "LIST_NO"=:id', [
                ':id' => $listNo
            ])->queryOne();
        if (! $result) {
            $result = [
                'EST_ROI_CASH' => 0,
                'EST_ANNUAL_INCOME_CASH' => 0
            ];
        }

        $aveResult = WS::$app->db->createCommand('select * from rets_ave_roi where "ZIP_CODE"=:zip', [
                ':zip' => $zipCode
            ])->queryOne();

        return array_merge($result, $aveResult);
    }

    public static function fetchDetail($rets)
    {
        return $rets->render()->detail();
    }

    public static function fetchRecommends($rets)
    {
        $items = \common\estate\RetsIndex::findOne($rets->list_no)->nearbyHouses();

        return array_map(function ($d) {
            $e = $d->entity();
            $r = $e->render();

            return [
                'location' => $e->getLocation(),
                'image' => $e->getPhoto(0, 500, 500),
                'list_price' => $e->list_price,
                'rooms_descriptions' => $r->get('rooms_descriptions')['value'],
                'prop_type_name' => $r->get('prop_type_name')['value']
            ];
        }, $items);
    }
}
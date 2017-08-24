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

        $result = array_merge($result, $aveResult);
        unset($result['ZIP_CODE']);
        unset($result['AVE_ROI_MORTGAGE']);
        unset($result['AVE_ANNUAL_INCOME_MORTGAGE']);

        $result['EST_ROI_CASH'] = number_format($result['EST_ROI_CASH'], 2).'%';
        $result['AVE_ROI_CASH'] = number_format($result['AVE_ROI_CASH'], 2).'%';
        if (WS::$app->language === 'en-US') {
            $result['EST_ANNUAL_INCOME_CASH'] = '$'.number_format($result['EST_ANNUAL_INCOME_CASH'], 2);
            $result['AVE_ANNUAL_INCOME_CASH'] = '$'.number_format($result['AVE_ANNUAL_INCOME_CASH'], 2);
        } else {
            $result['EST_ANNUAL_INCOME_CASH'] = number_format($result['EST_ANNUAL_INCOME_CASH'] * 1.0 / 10000, 2).'万美元';
            $result['AVE_ANNUAL_INCOME_CASH'] = number_format($result['AVE_ANNUAL_INCOME_CASH'] * 1.0 / 10000, 2).'万美元';
        }

        return [
            strtolower('EST_ROI_CASH') => $result['EST_ROI_CASH'],
            strtolower('AVE_ROI_CASH') => $result['AVE_ROI_CASH'],
            strtolower('EST_ANNUAL_INCOME_CASH') => $result['EST_ANNUAL_INCOME_CASH'],
            strtolower('AVE_ANNUAL_INCOME_CASH') => $result['AVE_ANNUAL_INCOME_CASH']
        ];
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
                'id' => $e->list_no,
                'name' => $r->get('name')['value'],
                'location' => $e->getLocation(),
                'image' => $e->getPhoto(0, 500, 500),
                'list_price' => $e->list_price,
                'rooms_descriptions' => $r->get('rooms_descriptions')['value'],
                'prop_type_name' => $r->get('prop_type_name')['value']
            ];
        }, $items);
    }
}
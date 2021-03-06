<?php
namespace module\estate\helpers;

use WS;

class Detail
{
    public static function fetchRoi($rets)
    {
        $listNo = $rets->list_no;
        $zipCode = $rets->zip_code;

        $result = [
            'EST_ROI_CASH' => null,
            'EST_ANNUAL_INCOME_CASH' => null
        ];

        $estimation = WS::$app->mlsdb->createCommand('select estimation from mls_rets where "list_no"=:id', [
            ':id' => $listNo
        ])->queryScalar();

        if ($estimation) {
            $estimation = json_decode($estimation);
            if ($estimation->est_roi) {
                $result['EST_ROI_CASH'] = ($estimation->est_roi * 100);
            }
            if ($estimation->est_rental) {
                $result['EST_ANNUAL_INCOME_CASH'] = $estimation->est_rental * 12;
            }
        }

        $aveResult = WS::$app->db->createCommand('select * from zipcode_roi_ave where "ZIP_CODE"=:zip', [
                ':zip' => $zipCode
            ])->queryOne();
        if (! $aveResult) {
            $aveResult = [
                'AVE_ROI_CASH' => null,
                'AVE_ANNUAL_INCOME_CASH' => null
            ];
        }

        $aveResult['AVE_ROI_CASH'] *= 100;

        $result = array_merge($result, $aveResult);
        unset($result['ZIP_CODE']);
        unset($result['AVE_ROI_MORTGAGE']);
        unset($result['AVE_ANNUAL_INCOME_MORTGAGE']);

        if ($result['EST_ROI_CASH']) {
            $result['EST_ROI_CASH'] = number_format($result['EST_ROI_CASH'], 2).'%';
        } else {
            $result['EST_ROI_CASH'] = tt('Unknown', '未提供');
        }

        if ($result['AVE_ROI_CASH']) {
            $result['AVE_ROI_CASH'] = number_format($result['AVE_ROI_CASH'], 2).'%';
        } else {
            $result['AVE_ROI_CASH'] = tt('Unknown', '未提供');
        }

        if (WS::$app->language === 'en-US') {
            if ($result['EST_ANNUAL_INCOME_CASH']) {
                $result['EST_ANNUAL_INCOME_CASH'] = '$'.number_format($result['EST_ANNUAL_INCOME_CASH'], 0);
            } else {
                $result['EST_ANNUAL_INCOME_CASH'] = tt('Unknown', '未提供');
            }

            if ($result['AVE_ANNUAL_INCOME_CASH']) {
                $result['AVE_ANNUAL_INCOME_CASH'] = '$'.number_format($result['AVE_ANNUAL_INCOME_CASH'], 0);
            } else {
                $result['AVE_ANNUAL_INCOME_CASH'] = tt('Unknown', '未提供');
            }
        } else {
            if ($result['EST_ANNUAL_INCOME_CASH']) {
                $result['EST_ANNUAL_INCOME_CASH'] = number_format($result['EST_ANNUAL_INCOME_CASH'] * 1.0 / 10000, 2).'万美元';
            } else {
                $result['EST_ANNUAL_INCOME_CASH'] = tt('Unknown', '未提供');
            }

            if ($result['AVE_ANNUAL_INCOME_CASH']) {
                $result['AVE_ANNUAL_INCOME_CASH'] = number_format($result['AVE_ANNUAL_INCOME_CASH'] * 1.0 / 10000, 2).'万美元';
            } else {
                $result['AVE_ANNUAL_INCOME_CASH'] = tt('Unknown', '未提供');
            }
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
        $items = \common\estate\HouseIndex::findOne($rets->list_no)->nearbyHouses();

        return array_map(function ($d) {
            $e = $d->entity();
            $r = $e->render();

            return [
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
        }, $items);
    }
}
<?php
namespace module\estate\helpers;

class FieldFilter
{
    public static function money($val, $full = true)
    {
        if ($unknownVal = self::unknown($val, false)) return $unknownVal;

        if (\WS::$app->language === 'zh-CN') {
            if ($val > 10000) {
                $val = number_format($val / 10000.0, 2);
                if (!$full) return $val;

                return $val.'万美元';
            } else {
                $val = number_format($val, 0);
                if (!$full) return $val;

                return $val.'美元';
            }
        }
        $val = number_format($val, 0);
        if (!$full) return $val;

        return '$'.$val;
    }

    public static function moneyRmb($val)
    {
        $rmbVal = $val * 6.9506;

        if ($rmbVal > 10000) {
            return number_format($rmbVal / 10000.0, 2).'万元';
        }
        return number_format($rmbVal, 0).'元';
    }

    public static function percent($val)
    {
        if ($unknownVal = self::unknown($val, false)) return $unknownVal;
        return number_format($val * 100, 2).'%';
    }

    public static function square($val)
    {
        if ($unknownVal = self::unknown($val, false)) return $unknownVal;

        if (\WS::$app->language === 'zh-CN') {
            return intval($val * 0.092903).'平方米';
        }
        return intval($val).'Sq.Ft';
    }

    public static function baths($vals)
    {
        if ($unknownVal = self::unknown($vals, false)) return $unknownVal;

        $parts = [];

        if ($vals[0]) {
            $parts[] = \WS::$app->language === 'zh-CN' ? $vals[0] . '全' : $vals[0] . 'F';
        }

        if ($vals[1]) {
            $parts[] = \WS::$app->language === 'zh-CN' ? $vals[1] . '半' : $vals[1] . 'H';
        }

        return implode('&nbsp;', $parts);
    }

    public static function listDayDesc($days)
    {
        if ($days === '0' || $days === 0) {
            return tt('New listing', '当日上市');
        }

        return tt("{$days} days on market", "已上市{$days}天");
    }

    public static function statusName($status, $prop)
    {
        if($status === 'NEW') {
            $name = $prop === 'LD' ? '新出售' : '新房源';
            return \WS::$app->language === 'zh-CN' ? $name : 'New';
        }

        $activeCnNm = $prop === 'RN' ? '出租' : '销售';
        if (in_array($status, ['ACT', 'BOM', 'PCG', 'RAC', 'EXT'])) {
            return \WS::$app->language === 'zh-CN' ? $activeCnNm.'中' : 'Active';
        }

        return \WS::$app->language === 'zh-CN' ? '已'.$activeCnNm : 'Sold';
    }

    public static function tags($d)
    {
        $maps = [
            tt('School district', '学区房') => function ($d) {
                return $d->is_in_sd;
            },
            tt('More bedrooms', '卧室充足') => function ($d) {
                return intval($d->beds) > 2;
            },
            tt('More parkings', '车位充足') => function ($d) {
                return intval($d->parking) > 1;
            },
            tt('Has garage', '带车库') => function ($d) {
                return intval($d->garage) > 0;
            },
            tt('Luxury house', '高级豪宅') => function ($d) {
                return in_array($d->prop, ['CC', 'SF']) && intval($d->price) > 1000000;
            }
        ];

        $tagNames = [];
        foreach ($maps as $tagName => $callback) {
            if ($callback($d)) $tagNames[] = $tagName;
        }

        return $tagNames;
    }

    public static function housePropName($prop)
    {
        $props = [
            'RN' => ['Rental', '租房'],
            'SF' => ['Single Family', '单家庭'],
            'MF' => ['Multi Family', '多家庭'],
            'CC' => ['Condominium', '公寓'],
            'CI' => ['Commercial', '商业用房'],
            'BU' => ['Business Opportunity', '营业用房'],
            'LD' => ['Land', '土地']
        ];

        return tt($props[$prop]);
    }

    public static function listItem($d)
    {
        $ldays = round((time() - strtotime($d->date)) / 3600 / 24);

        return [
            'id' => $d->id,
            'name' => $d->nm,
            'location' => $d->loc,
            'image' => $d->photo,
            'images' => [
                $d->photo_sub2,
                $d->photo_sub3
            ],
            'no_bedrooms' => static::unknown($d->beds),
            'no_full_baths' => static::unknown($d->baths[0]),
            'no_half_baths' => static::unknown($d->baths[1]),
            'square_feet' => static::square($d->square_feet),
            'list_price' => static::money($d->price),
            'prop_type_name' => static::housePropName($d->prop),
            'latitude' => $d->latlng[0],
            'longitude' => $d->latlng[1],
            'status_name' => static::statusName($d->status, $d->prop),
            'list_days_description' => static::listDayDesc($ldays),
            'tags' => static::tags($d)
        ];
    }

    public static function unknown($val, $returnRaw = true)
    {
        return $val != '0' && empty($val) ? tt('Unknown', '未提供') : ($returnRaw ? $val : false);
    }
}

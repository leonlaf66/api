<?php
namespace module\estate\helpers;

use WS;

class ListItem
{
    public static function map($items)
    {
        return array_map(function ($d) {
            $r = $d->render();
            $items[] = [
                'id' => $d->list_no,
                'name' => $r->get('name')['value'],
                'location' => $d->location,
                'image' => $d->getPhoto(0, 800, 800),
                'images' => [
                    $d->getPhoto(1, 600, 600),
                    $d->getPhoto(2, 600, 600)
                ],
                'no_bedrooms' => intval($d->no_bedrooms),
                'no_full_baths' => intval($d->no_full_baths),
                'no_half_baths' => intval($d->no_half_baths),
                'square_feet' => $r->get('square_feet')['formatedValue'],
                'list_price' => $r->get('list_price')['formatedValue'],
                'prop_type_name' => $rets->propTypeName(),
                'latitude' => $d->latitude,
                'longitude' => $d->longitude,
                'status_name' => $d->statusName(),
                'list_days_description' => $d->getListDaysDescription(),
                'tags' => $d->getTags()
            ];
        }, $items);
    }
}
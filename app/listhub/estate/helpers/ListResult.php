<?php
namespace module\listhub\estate\helpers;

class ListResult
{
    public static function renderItems($items)
    {
        return array_map(function ($item) {
            return ListResult::renderItem($item);
        }, $items);
    }

    public static function renderItem($item)
    {
        return [
            'id' => $item->id,
            'name' => $item->title(),
            'location' => $item->location,
            'image' => $item->getPhoto()['url'],
            'images' => [
                $item->getPhoto(1)['url'],
                $item->getPhoto(2)['url']
            ],
            'no_bedrooms' => intval($item->no_bedrooms),
            'no_full_baths' => intval($item->no_full_baths),
            'no_half_baths' => intval($item->no_half_baths),
            'square_feet' => $item->getFieldData('square_feet')['formatedValue'],
            'list_price' => $item->getFieldData('list_price')['formatedValue'],
            'prop_type_name' => $item->propTypeName(),
            'latitude' => $item->latitude,
            'longitude' => $item->longitude,
            'status_name' => $item->statusName(),
            'list_days_description' => $item->getListDaysDescription(),
            'tags' => $item->getTags()
        ];
    }
}
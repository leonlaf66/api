# 房源搜索filters请求参数格式 #

## 请求 (GET方式)
``` javascript
{
    "city_code": "", // 城市编码
    "list_price": {"from": 0, "to": 1000}, // 价格范围, from默认为0, to默认为无限大
    "prop-type": [], // 售房专用，指定需限制的房源类型, 如["MF", "SF", "CC"]
    "square": {"from": 0, "to": 200}, // 面积范围, from默认为0, to默认为无限大
    "beds": 1, // 卧室数, 1=1+, 2=2+, 3=3+... 
    "baths": 1, // 卫生间数, 1=1+, 2=2+, 3=3+... 
    "parking": null, // 车位数, 1=1+, 2=2+, 3=3+... 
    "agrage": null, // 是否带车库, 1为带，0为不带
    "market-days": null, // 上市天数, 1:最近 2:本周 3:本月
    "school_district": 1, // 学区id, 对应于API /catalog/school-district/maps中的key 或 /catalog/school-district/list中的code字段
    "subway_line": 1, // 地铁id
    "subway_stations": [1, 2, 3] // 地铁站点id，多选支持
}
```
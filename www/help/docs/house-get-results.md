# 房源详情格式 #

``` javascript
{
    "id": 123,
    "location": "", // 地址
    "images": "", // 第二第三张图
    "no_bedrooms": 0, // 卧室数
    "no_full_baths": 0, // 全卫生间数
    "no_half_baths": 0, // 半全卫生间数
    "square_feet": 0, // 面积 (需app自己本地化)
    "list_price": 0, // 价格 (需app自己本地化)
    "prop_type_name": "", // 类型名, 直接获取(自动识别中英文)
    "status_name": "", // 房源状态, 直接获取(自动识别中英文)
    "list_days_description": "", // 上市天数， 直接获取(自动识别中英文)
    "latitude": "41.986729",
    "longitude": "-71.320775",
    "images": [], // 所有图片
    "roi": [ // 投资回报率
        "EST_ROI_CASH": 0,
        "EST_ANNUAL_INCOME_CASH": 0,
        "ZIP_CODE": "02760",
        "AVE_ROI_MORTGAGE": "0.084552",
        "AVE_ROI_CASH": "0.062742",
        "AVE_ANNUAL_INCOME_MORTGAGE": "2780.25",
        "AVE_ANNUAL_INCOME_CASH": "18785.15"
    ],
    "details": [ // 详细信息(请自动循环渲染)
        { // 一个分组
            "title": "基本信息", // 分组标题
            "items": [ // 组内字段项目
                "no_rooms": { // 一个字段的信息
                    "id": "no_rooms", // 字段名
                    "title": "Rooms", // 字段标题
                    "value": 5, // 字段的最终渲染值
                    "rawValue": "5", // 字段的原始值
                    "prefix": "", // 字段值的前缀 如 $123.00 中的"$"，渲染时请附到value前面
                    "postfix": "" // 字段值的后缀 如 100万美元 中的中的"万美元" 渲染时请附到value后面
                },
                // ... 更多字段项目
            ]
        },
        // ... 更多分组
    ],
    "recommend_houses" => [
        {
            "location": "58 Reed Avenue, 5 North Attleboro MA 02760",
            "image": "http://media.mlspin.com/Photo.aspx?mls=71946191&n=0&w=500&h=500",
            "list_price": "329900.00",
            "rooms_descriptions": "卧室 3 卫生间 2.5",
            "prop_type_name": "公寓"
        },
        // ... 更分推荐房源
    ]
}
```
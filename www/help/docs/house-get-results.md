# 房源详情格式 #

``` javascript
{
        "location": "", // 地址
        "image": "", // 默认主图
        "images": "", // 第二第三张图
        "no_bedrooms": 0, // 卧室数
        "no_full_baths": 0, // 全卫生间数
        "no_half_baths": 0, // 半全卫生间数
        "square_feet": 0, // 面积 (需app自己本地化)
        "list_price": 0, // 价格 (需app自己本地化)
        "prop_type_name": "", // 类型名, 直接获取(自动识别中英文)
        "status_name": "", // 房源状态, 直接获取(自动识别中英文)
        "list_days_description": "", // 上市天数， 直接获取(自动识别中英文)
        "tags": [ // 标签，直接获取(自动识别中英文) - 循环取该项
            "卧室充足",
            "车位充足"
        ].
    ]
}
```
<?php
namespace module\catalog\controllers;

use WS;
use common\news\News;

class NewsController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 新闻首页
     * @desc 新闻首页
     * @param number $type_id 类型id
     * @param number $simple 是否返回简单结果, 0: 较完整, 1:简单
     * @param number $only_infomaion 是否仅仅资讯
     * @param number $only_hot 是否仅仅热门
     * @param number $content_len 概要内容长度
     * @param number $page 页码
     * @param number $page_size 分页大小
     * @return [] info 新闻首页数据
     */
    public function actionList($type_id = 0, $simple = '0', $only_infomaion = '0', $only_hot = '0', $content_len=200, $page = 1, $page_size = 15)
    {
        $search = News::search();
        $search->query->andWhere(['=', 'status', '1']);

        // 类型
        if ($type_id) {
            $search->query->andWhere(['=', 'type_id', $type_id]);
        }

        // 资讯
        if ($only_infomaion === '1') {
            $search->query->andWhere(['=', 'is_infomation', true]);
        }

        // 热门
        if ($only_hot === '1') {
            $search->query->andWhere(['=', 'is_hot', true]);
        }

        // 分页处理
        $search->pagination->page = $page;
        $search->pagination->pageSize = $page_size;

        $items = array_map(function ($d) use ($simple, $content_len){
            $item = [
                'id' => $d->id,
                'image' => $d->getImageUrl('news/tmp.jpg')
            ];

            if ($simple !== '1') {
                $item['short_content'] = \common\cms\helper\Content::subString(strip_tags($d->content), $content_len);
            }
            $item['hits'] = $d->hits;
            $item['created_at'] = $d->created_at;

            return $item;
        }, $search->query->all());

        return [
            'total' => $search->query->count(),
            'items' => $items
        ];
    }

    /**
     * 新闻详情
     * @desc 新闻详情
     * @param number $id 学区ID
     * @return [] info 新闻信息
     */
    public function actionGet($id)
    {
        $news = News::findOne($id);
        
        return [
            'id' => $news->id,
            'title' => $news->title,
            'content' => $news->content,
            'type_id' => $news->type_id,
            'created_at' => $news->created_at,
            'hits' => intval($news->hits)
        ];
    }

    /**
     * 新闻类型列表
     * @desc 新闻类型列表
     * @return [] list 列表
     */
    public function actionTypes()
    {
        return \common\core\TaxonomyTerm::typeOptions(3);
    }
}

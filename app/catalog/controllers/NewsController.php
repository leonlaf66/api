<?php
namespace module\catalog\controllers;

use WS;
use models\News;

class NewsController extends \deepziyu\yii\rest\Controller
{   
    /**
     * 新闻列表
     * @desc 新闻列表
     * @param string $area_id 区域ID
     * @param number $type_id 类型id
     * @param number $simple 是否返回简单结果, 0: 较完整, 1:简单
     * @param number $only_infomaion 是否仅仅资讯
     * @param number $only_hot 是否仅仅热门
     * @param number $content_len 概要内容长度
     * @param number $page 页码
     * @param number $page_size 分页大小
     * @return number total 新闻总数
     * @return [] items 新闻集合
     */
    public function actionList($area_id = 'ma', $type_id = 0, $simple = '0', $only_infomaion = '0', $only_hot = '0', $content_len=200, $page = 1, $page_size = 15)
    {
        $search = News::search();

        // 分页处理
        $search->pagination->setPage(intval($page) - 1);
        $search->pagination->setPageSize($page_size);

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

        $total = $search->query->count();

        $items = array_map(function ($d) use ($simple, $content_len){
            $item = [
                'id' => $d->id,
                'title' => $d->title,
                'image' => $d->getImageUrl('news/tmp.jpg')
            ];

            if ($simple !== '1') {
                $item['short_content'] = \common\cms\helper\Content::subString(strip_tags($d->content), $content_len);
            }
            $item['hits'] = intval($d->hits);
            $item['favorites'] = 0;
            $item['created_at'] = $d->created_at;

            return $item;
        }, $search->getModels());

        return [
            'total' => $total,
            'items' => $items
        ];
    }

    /**
     * 最新新闻资讯
     * @desc 用于首页的最新资讯列表
     * @param string $area_id 区域ID
     * @param number $limit 限制条数
     * @return [] list 资讯集合
    */
    public function actionLatest($area_id = 'ma', $limit = 6)
    {
        $search = News::search();
        $search->query
            ->andWhere(['=', 'status', '1'])
            ->andWhere(['=', 'is_infomation', true])
            ->orderBy(['id' => SORT_DESC])
            ->limit($limit);

        $items = $search->query->all();

        return array_map(function ($d) {
            return [
                'id' => $d->id,
                'title' => $d->title,
                'hits' => intval($d->hits),
                'favorites' => 0,
                'image' => $d->getImageUrl('news/tmp.jpg')
            ];
        }, $items);
    }

    /**
     * 新闻详情
     * @desc 新闻详情
     * @param number $id 学区ID
     * @return [] - 新闻信息
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
            'hits' => intval($news->hits),
            'favorites' => 0
        ];
    }

    /**
     * 新闻类型列表
     * @desc 新闻类型列表
     * @param string $area_id 区域ID
     * @return [] - 列表
     */
    public function actionTypes($area_id = 'ma')
    {
        return \common\core\TaxonomyTerm::typeOptions(3);
    }

    /**
     * 新闻顶部Banner
     * @desc 新闻顶部的图文Banner, 返回结果中的news_id用于点周并查看相应新闻内容
     * @param string $area_id 区域ID
     * @return [] - 列表
     */
    public function actionListTopBanner($area_id)
    {
        return \WS::getStaticData('app.news.banner.top');
    }
}   

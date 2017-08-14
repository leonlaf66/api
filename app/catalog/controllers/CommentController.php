<?php
namespace module\catalog\controllers;

use WS;
use \common\comment\CommentPage;
use \common\comment\Comment;

class CommentController extends \deepziyu\yii\rest\Controller
{   
    public $enableAuth = true;

    /** 
     * list 无需登陆验证
     */
    public function authOptional()
    {
        return ['list'];
    }

    /**
     * 获取评论列表
     * @desc 通用的评论列表获取, 支持任何类型的评论, 暂仅有yellowpage在使用，且暂不支持分页
     * @param string $type 评论类型, 如黄页的评论类型为: yellowpage
     * @param number $id 评论实体的id，如果type=yellowpage，那么该id就应该为黄页id
     * @return number total 总条数, 可用于分页
     * @return [] items 评论集合
     */
    public function actionList($type, $id)
    {
        $commentPage = CommentPage::find()->where(['url' => "{$type}/{$id}"])->one();
        if (! $commentPage) {
            return [];
        }

        $commentQuery = $commentPage->getComments();
        $items = $commentQuery->all();

        $items = array_map(function ($d) {
            $user = $d->getUser();

            return [
                'id' => $d->id,
                'rating' => $d->rating,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->username
                ],
                'comments' => $d->comments,
                'created_at' => $d->created_at
            ];
        }, $items);

        return [
            'total' => $commentQuery->count(),
            'items' => $items
        ];
    }

    /**
     * 提交评论
     * @desc 通用的评论提交, 支持任何类型的评论, 暂仅有yellowpage在使用
     * @method POST
     * @data number $rating(5) 评价星星数, 1~5
     * @data string $content 评论内容
     * @param string $type 评论类型, 如黄页的评论类型为: yellowpage
     * @param number $id 评论实体的id，如果type=yellowpage，那么该id就应该为黄页id
     * @return bool - 评论成功与否 || 返回错误提示
     */
    public function actionSubmit($type, $id)
    {
        if (\WS::$app->user->isGuest) {
            throw new \yii\web\HttpException(403, "用户授权失败!");
        }

        $path = "{$type}/{$id}";
        $commentPage = CommentPage::find()->where(['url'=>$path])->one();

        if(! $commentPage) {
            $commentPage = new CommentPage();
            $commentPage->url = $path;
            $commentPage->hash = md5($path);
            $commentPage->save();
        }

        $req = \WS::$app->request;

        $comment = new Comment();
        $comment->attributes = [
            'rating' =>$req->post('rating', '5'),
            'comments' => $req->post('content', '')
        ];
        $comment->user_id = \WS::$app->user->id;
        $comment->page_id = $commentPage->id;

        if ($comment->validate()) {
            return $comment->save();
        }

        return $comment->getErrors();
    }
}

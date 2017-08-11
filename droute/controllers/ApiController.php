<?php
namespace droute\controllers;

use droute\models\Route;
use Yii;
use yii\web\Controller;

/**
 * Site controller
 */
class ApiController extends Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->response->format = 'html';
    }

    /**
     * Displays homepage.
     * @param int $id
     * @return array
     */
    public function actionIndex()
    {
        $model = new Route();

        $routes =  $model->getRoutes();
        unset($routes['/*']);
        foreach ($routes as $key =>  &$route) {
            if(preg_match('#^/route|gii|debug/#',$key,$m)){
                unset($routes[$key]);
            }
        }
        $routes = array_reverse($routes);

        $sorts = include(dirname(__DIR__).'/etc/sorts.php');
        $diffArr = array_diff(array_keys($routes), $sorts);
        if (count($diffArr) > 0) {
            echo '<h2>未加入到队列中的路由:</h2><pre>';
            foreach ($diffArr as $item) {
                echo "\"{$item}\"<br/>";
            }
            echo '</pre>';
            exit;
        }

        $sortedRoutes = [];
        foreach($sorts as $idx=>$id) {
            if (isset($routes[$id])) {
                $sortedRoutes[$id] = $routes[$id];
            }
        }

        return $this->renderPartial('index',[
            'routes'=>$sortedRoutes
        ]);
    }

    public function actionRoutes()
    {

    }

}

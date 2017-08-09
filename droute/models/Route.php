<?php
namespace droute\models;

use Yii;

class Route extends \deepziyu\yii\rest\models\Route
{
    /**
     * Get route of action
     * @param \yii\base\Controller $controller
     * @param array $result all controller action.
     */
    protected function getActionRoutes($controller, &$result)
    {
        $description = '';
        $descComment = '//请使用@desc 注释';
        $typeMaps = array(
            'string' => '字符串',
            'int' => '整型',
            'float' => '浮点型',
            'boolean' => '布尔型',
            'date' => '日期',
            'array' => '数组',
            'fixed' => '固定值',
            'enum' => '枚举类型',
            'object' => '对象',
        );
        $token = "Get actions of controller '" . $controller->uniqueId . "'";
        Yii::beginProfile($token, __METHOD__);
        try {
            $prefix = '/' . $controller->uniqueId . '/';
            foreach ($controller->actions() as $id => $value) {
                //$result[$prefix . $id] = $prefix . $id;
            }
            $class = new \ReflectionClass($controller);
            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                    $name = strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', substr($name, 6)));
                    $id = $prefix . ltrim(str_replace(' ', '-', $name), '-');
                    //$result[$id] = $id;
                    $result[$id] = [
                        'id' => $id,
                        'description' => '',
                        'method' => 'GET',
                        'descComment' => '//请使用@desc 注释',
                        'request' => [],
                        'response' => [],
                    ];
                    $docComment = $method->getDocComment();
                    $docCommentArr = explode("\n", $docComment);
                    foreach ($docCommentArr as $comment) {
                        $comment = trim($comment);

                        //标题描述
                        if (empty($result[$id]['description']) && strpos($comment, '@') === false && strpos($comment, '/') === false) {
                            $result[$id]['description'] = (string)substr($comment, strpos($comment, '*') + 1);
                            continue;
                        }

                        //@method注释
                        $pos = stripos($comment, '@method');
                        if ($pos !== false) {
                            $result[$id]['method'] = substr($comment, $pos + 7);
                            continue;
                        }

                        //@desc注释
                        $pos = stripos($comment, '@desc');
                        if ($pos !== false) {
                            $result[$id]['descComment'] = substr($comment, $pos + 5);
                            continue;
                        }

                        //@param注释
                        $pos = stripos($comment, '@param');
                        if ($pos !== false) {
                            $params = [
                                'name' => '',
                                'type' => '',
                                'require' => true,
                                'default' => '',
                                'other' => '',
                                'desc' => ''
                            ];
                            $paramCommentArr = explode(' ', substr($comment, $pos + 7));
                            if (preg_match('/\$[A-Z0-9]*/', @$paramCommentArr[1])) {
                                $params['name'] = substr($paramCommentArr[1], 1);
                                $params['type'] = $paramCommentArr[0];
                                foreach ($paramCommentArr as $k => $v) {
                                    if ($k < 2) {
                                        continue;
                                    }
                                    $params['desc'] .= $v.' ';
                                }
                                foreach ($method->getParameters() as $item) {
                                    if ($item->getName() !== $params['name']) {
                                        continue;
                                    }
                                    $params['require'] = !$item->isDefaultValueAvailable();
                                    if (!$params['require']) {
                                        $params['default'] = $item->getDefaultValue();
                                    }
                                }
                            }
                            $result[$id]['request'][] = $params;
                            continue;
                        }

                        //@return注释
                        $pos = stripos($comment, '@return');
                        if ($pos === false) {
                            continue;
                        }

                        $returnCommentArr = explode(' ', substr($comment, $pos + 8));
                        //将数组中的空值过滤掉，同时将需要展示的值返回
                        $returnCommentArr = array_values(array_filter($returnCommentArr));
                        if (count($returnCommentArr) < 2) {
                            continue;
                        }
                        if (!isset($returnCommentArr[2])) {
                            $returnCommentArr[2] = '';    //可选的字段说明
                        } else {
                            //兼容处理有空格的注释
                            $returnCommentArr[2] = implode(' ', array_slice($returnCommentArr, 2));
                        }

                        $result[$id]['response'][] = $returnCommentArr;
                    }


                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }
}
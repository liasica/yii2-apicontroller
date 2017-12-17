<?php
/**
 * Author: liasica
 * Email: magicrolan@qq.com
 * CreateTime: 2017/12/17 上午11:44
 */

namespace liasica\ApiController;

use yii\web\Response;

class Controller extends \yii\rest\Controller
{
    use JsonData;
    public $route;
    public $level1;
    public $level2;

    public function beforeAction($action)
    {
        $beforeAction           = parent::beforeAction($action);
        $this->request          = \Yii::$app->request;
        $this->headers          = \Yii::$app->request->headers;
        $this->response         = \Yii::$app->response;
        $this->response->format = Response::FORMAT_JSON;
        // 格式化
        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
        ];
        // 模块ID
        $module = \Yii::$app->controller->module->id;
        if ($module == 'fondinn') {
            $module = '';
        }
        $this->route  = sprintf('%s/%s/%s', $module, $this->id, $action->id);
        $this->level1 = sprintf('%s/*', $module);
        $this->level2 = sprintf('%s/%s/*', $module, $this->id);
        return $beforeAction;
    }
}

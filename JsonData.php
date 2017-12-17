<?php
/**
 * Author: liasica
 * Email: magicrolan@qq.com
 * CreateTime: 2017/12/17 上午11:34
 */

namespace liasica\apicontroller;

use Yii;
use yii\helpers\Json;
use yii\web\HeaderCollection;
use yii\web\Request;
use yii\web\Response;

trait JsonData
{
    /**
     * @var string
     */
    public $output = [];
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var HeaderCollection
     */
    protected $headers;

    /**
     * @param $msg
     */
    public static function webError($msg)
    {
        header('text/html; charset=UTF-8');
        echo '<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">';
        echo $msg;
        exit();
    }

    /**
     * @param $code
     * @return string
     */
    public function getAppErrors($code)
    {
        $errors = $this->getErrorsText();
        $error  = $errors[$code] ?? '服务器错误';
        $c      = \Yii::$app->controller->id;
        $cs     = $this->getControllersText();
        $msg    = '';
        if (key_exists($c, $cs)) {
            $msg = $cs[$c];
        }
        return sprintf($error, $msg);
    }

    /**
     * @return array
     */
    public function getErrorsText()
    {
        return [
            16001 => '用户认证失败',
            16002 => '权限认证失败',
            16003 => '参数错误',
            16004 => '未找到',
            16006 => '请求方式错误',
            16007 => '数据获取失败或校验失败',
            16008 => '已存在，请勿重复添加',
            16009 => '请求非法',
            16010 => '用户名或密码错误',
            16011 => '数据保存失败',
            16012 => '操作过于频繁',
            16013 => '文件错误',
            16014 => '校验失败或已失效',
            16015 => '需要绑定手机号',
            16016 => '请求数据为空',
            16017 => '需要认证',
            16018 => '您已被禁言',
        ];
    }

    /**
     * @return array
     */
    public function getControllersText()
    {
        return [
            'user' => '用户',
        ];
    }

    /**
     * 输出成功
     * @param array $data
     * @return void
     * @throws \yii\base\ExitException
     */
    public function successJson($data = null)
    {
        $this->response->format = Response::FORMAT_JSON;
        $ret                    = [
            'code'    => 16000,
            'message' => 'SUCCESS',
        ];
        $this->convertString($data);
        if ($data != null && (is_object($data) || is_array($data))) {
            $ret['data'] = Json::decode(Json::encode($data));
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo Json::encode($ret);
        Yii::$app->end();
    }

    /**
     * 格式化为字符串
     * @param $data
     */
    public function convertString(&$data)
    {
        if (!empty($data) && (is_object($data) || is_array($data))) {
            foreach ($data as $key => $datum) {
                if (is_array($datum) || is_object($datum)) {
                    $this->convertString($datum);
                    $data[$key] = $datum;
                } else {
                    if (!is_bool($datum) && !is_int($datum)) {
                        $data[$key] = (string)$datum;
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @throws \yii\base\ExitException
     */
    public function d($data)
    {
        $this->p($data);
        Yii::$app->end();
    }

    /**
     * @param $data
     */
    public function p($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    /**
     * @param \yii\db\ActiveRecord $model
     * @param bool                 $validate
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function saveModel($model, $validate = false)
    {
        if (($validate ? $model->validate() : true) && $model->save()) {
            return true;
        }
        $errs    = $model->getErrors();
        $errors  = [];
        $message = '保存失败';
        foreach ($errs as $k => $err) {
            $errors[] = [
                'field' => $k,
                'error' => $err[0],
            ];
        }
        $this->errJson(16005, $message, $errors);
    }

    /**
     * 输出错误
     * @param int    $code
     * @param string $desc
     * @param null   $data
     * @return void
     * @throws \yii\base\ExitException
     */
    public function errJson(int $code, string $desc, $data = null)
    {
        $this->response->format = Response::FORMAT_JSON;
        $output                 = [
            'message' => $desc,
        ];
        $this->convertString($data);
        if ($data != null) {
            $output['data'] = ['error' => Json::decode(Json::encode($data))];
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo Json::encode($this->renderJson($code, $output));
        Yii::$app->end();
    }

    /**
     * 输出JSON数据
     * @param int   $code
     * @param array $data
     * @return void
     * @throws \yii\base\ExitException
     */
    public function renderJson($code = 0, array $data)
    {
        $this->response->format = Response::FORMAT_JSON;
        // 组装数据
        $this->convertString($data);
        $output = array_merge([
            'code' => $code,
        ], $data);
        header('Content-Type: application/json; charset=UTF-8');
        echo Json::encode($output);
        Yii::$app->end();
    }

    /**
     * 获取POST参数
     * @param $field
     * @return array|mixed
     */
    public function post($field = null)
    {
        return $this->request->post($field);
    }

    /**
     * 获取固定失败消息
     * @param int   $code
     * @param array $data
     * @throws \yii\base\ExitException
     */
    public function getError($code = 16005, $data = [])
    {
        $this->errJson($code, trim($this->getAppErrors($code)), $data);
    }

    /**
     * 输出错误
     * @param $data
     * @throws \yii\base\ExitException
     */
    public function error($data)
    {
        $this->errJson(16005, '请求失败', $data);
    }

    /**
     * 输出错误信息
     * @param $msg
     * @throws \yii\base\ExitException
     */
    public function errMsg($msg)
    {
        $this->errJson(16005, $msg);
    }

    /**
     * 输出错误网页
     * @param        $msg
     * @param string $title
     */
    public function webErr($msg, $title = '请求失败')
    {
        $file = Yii::$app->basePath . 'err.html';
        $html = file_get_contents($file);
        echo sprintf($html, $title, $msg);
    }

    /**
     * 校验手机号
     * @param $phone
     * @throws \yii\base\ExitException
     */
    public function checkPhone($phone)
    {
        // 判断手机号
        $partten    = '/^((1[3,5,8][0-9])|(14[5,7])|(17[0-9]))\d{8}$/';
        $validPhone = preg_match($partten, $phone, $result);
        if ($phone == null || $validPhone === 0) {
            $this->errMsg('手机号错误');
        }
    }
}
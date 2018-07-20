<?php

namespace app\common\utils;

/*
 * curl 封装工具类
 */
class UrlUtils
{


    /**
     * 拼接url
     * @param string $baseURL   基于的url
     * @param array  $params   参数列表数组
     * @return string           返回拼接的url
     */
    public static function combineParams($baseURL, $params){
        $combined = $baseURL . "?";
        $valueArr = array();
        foreach($params as $key => $val){
            $valueArr[] = "$key=$val";
        }
        $keyStr = implode("&", $valueArr);
        $combined .= ($keyStr);
        return $combined;
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url 请求URL
     * @param  array $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @param  array $header 请求头
     * @return array $data   响应数据
     * @throws \Exception
     */
    public static function http($url, $params = [], $method = 'GET', $header = [])
    {
        $opts = [
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header,
        ];

        $isJson = is_string($params);


        /* 根据请求类型设置特定参数 */
        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
                $opts[CURLOPT_URL] = self::combineParams($url, $params);
                break;
            case 'POST':
                if($isJson){ //发送JSON数据
                    $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER],
                        [
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length: ' . strlen($params)
                        ]
                    );
                }
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_POST]       = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'PUT':
            case 'PATCH':
                if($isJson){ //发送JSON数据
                    $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER],
                        [
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length: ' . strlen($params)
                        ]
                    );
                }
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_CUSTOMREQUEST]    = $method;
                $opts[CURLOPT_POSTFIELDS]       = $params;//设置请求体，提交数据包
                break;
            case 'DELETE':
                $opts[CURLOPT_URL] = self::combineParams($url, $params);
                $opts[CURLOPT_CUSTOMREQUEST]    = $method;
                break;
            default:
                throw new \Exception('不支持的请求方式！');
        }
        //var_dump($opts[CURLOPT_URL]);exit;
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            throw new \Exception('请求发生错误：' . $error);
        }
        return $data;
    }

}
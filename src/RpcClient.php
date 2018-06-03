<?php
/**
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace Zithan\Yar;

class RpcClient
{
    private $serverUrl = 'http://rpc.030.me/';
    private $loopCallback;
    private $loopErrorCallback;
    private $countCall = 0;
    private static $data = [];

    private static $signs = array(
        'sign1',
        'sign2'
        // ....
    );

    public function __construct()
    {

    }

    /**
     * @param $params
     * @return string
     */
    protected function getSignature($params)
    {
        ksort($params);
        $signStr = '';
        foreach ($params as $key => $val) {
            if (empty($val)) continue;
            $signStr .= $key . '=' . $val . '&';
        }
        $signStr = rtrim($signStr, '&');
        return md5($signStr . self::$signs[mt_rand(0, count(self::$signs) - 1)]);
    }

    /**
     * 同步串行
     * @param string $server
     * @param array $params
     * @param bool $sign
     * @return mixed
     * @throws \Exception
     */
    public function one(string $server, array $params, bool $sign = false)
    {
        try {
            $client = new \Yar_Client($this->serverUrl . $server);
            return $client->run($params);
        }  catch (\Yar_Server_Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * 异步并行
     * @param string $server
     * @param array $params
     * @param bool $signature
     * @param string $callback
     * @return mixed
     * @throws \Exception
     */
    public function call(string $server, array $params, $callback = null, bool $signature = false)
    {
        try {
            $this->countCall ++;

            if($signature){
                $params['signature'] = $this->getSignature($params);
            }

            return \Yar_Concurrent_Client::call($this->serverUrl . $server, 'run', [$params], $callback);
        }  catch (\Yar_Server_Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param string $loopCallback
     * @param string $loopErrorCallback
     * @return mixed
     */
    public function loop($loopCallback = null, $loopErrorCallback = null)
    {
        if ($loopCallback != null) {
            $this->loopCallback = $loopCallback;
        }

        if ($loopErrorCallback != null) {
            $this->loopErrorCallback = $loopErrorCallback;
        }

        return \Yar_Concurrent_Client::loop([$this, 'clientLoopCallback'], [$this, 'clientLoopErrorCallback']);
    }

    /**
     * Clean all registered calls
     * @return mixed
     */
    public function reset() {
        return \Yar_Concurrent_Client::reset();
    }

    /**
     * 并发调用回调
     * @param $retval
     * @param $callinfo
     * @return mixed
     */
    public function clientLoopCallback($retval, $callinfo)
    {
        if ($this->loopCallback) {
            if($callinfo === null) {
                call_user_func_array($this->loopCallback, [$retval, $callinfo]);
            } else {
                self::$data[][$callinfo['uri']] = $retval;
                if (count(self::$data) == $this->countCall) {
                    call_user_func_array($this->loopCallback, [self::$data, $callinfo]);
                } else {
                    call_user_func_array($this->loopCallback, [$retval, $callinfo]);
                }
            }
        }
    }

    /**
     * @param $type
     * @param $error
     * @param $callinfo
     * @return mixed
     */
    public function clientLoopErrorCallback($type, $error, $callinfo)
    {
        if ($this->loopErrorCallback) {
            return call_user_func_array($this->loopErrorCallback, [$type, $error, $callinfo]);
        }
        // 默认处理
        error_log('发送错误...error_callback...', 0);
    }
}
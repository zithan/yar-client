<?php

/*
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace Zithan\YarClient;

use Zithan\YarClient\Exceptions\BaseException;
use Zithan\YarClient\Exceptions\YarException;

class Client
{
    private $baseUri;

    private $loopCallback;

    private $loopErrorCallback;

    private $countCall = 0;

    private static $data = [];

    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * 同步串行.
     *
     * @param string $uri    [description]
     * @param array  $params [description]
     *
     * @return [type] [description]
     *
     * @throws Yar_Server_Exception | BaseException
     */
    public function one(string $uri, array $params)
    {
        try {
            $client = new \Yar_Client($this->baseUri . $uri);
            $response = $client->run($params);
        } catch (\Yar_Server_Exception | \Yar_Client_Exception $e) {
            throw new YarException($e->getMessage());
        }

        if (is_object($response)) {
            $response = $response->getData();
        }

        if (!is_array($response)) {
            throw new YarException('response is not array');
        }

        if (!isset($response['error_code']) || 0 !== $response['error_code'] || !isset($response['result'])) {
            throw new YarException(sprintf('error_code is error. [response]-->%s', json_encode($response, JSON_UNESCAPED_UNICODE)));
        }

        return $response['result'];
    }

    /**
     * 异步并行.
     *
     * @param string $uri      [description]
     * @param array  $params   [description]
     * @param [type] $callback [description]
     *
     * @return [type] [description]
     */
    public function call(string $uri, array $params, $callback = null)
    {
        try {
            return \Yar_Concurrent_Client::call($this->baseUri . $uri, 'run', [$params], $callback);
        } catch (\Yar_Server_Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param string $loopCallback
     * @param string $loopErrorCallback
     *
     * @return mixed
     */
    public function loop($loopCallback = null, $loopErrorCallback = null)
    {
        if (null != $loopCallback) {
            $this->loopCallback = $loopCallback;
        }

        if (null != $loopErrorCallback) {
            $this->loopErrorCallback = $loopErrorCallback;
        }

        return \Yar_Concurrent_Client::loop([$this, 'clientLoopCallback'], [$this, 'clientLoopErrorCallback']);
    }

    /**
     * Clean all registered calls.
     *
     * @return mixed
     */
    public function reset()
    {
        return \Yar_Concurrent_Client::reset();
    }

    /**
     * 异步并发调用回调.
     *
     * @param $retval
     * @param $callinfo
     *
     * @return mixed
     */
    public function clientLoopCallback($retval, $callinfo)
    {
        if ($this->loopCallback) {
            if (null === $callinfo) {
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
     *
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

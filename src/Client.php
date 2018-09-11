<?php
/**
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace Zithan\YarClient;

class Client
{
    protected $baseUri;

    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * 同步串行
     * @param  string    $uri    [description]
     * @param  array     $params [description]
     * @param  bool|null $sign   [description]
     * @return [type]            [description]
     */
    public function one(string $uri, array $params, bool $sign = null)
    {
        try {
            $client = new \Yar_Client($this->baseUri . $uri);
            return $client->run($params);
        } catch (\Yar_Server_Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

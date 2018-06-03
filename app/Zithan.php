<?php
/**
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace App;

use Zithan\Yar\RpcClient;

class Zithan
{
    public function single()
    {
        $rpc = new RpcClient();
        $rs = $rpc->call('Comment.add', ['huang', 'wen']);

        return $rs;
    }
}
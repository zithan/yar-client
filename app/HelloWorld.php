<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;
use Zithan\Yar\RpcClient;

class HelloWorld
{
    private $foo;

    private $response;

    public function __construct(string $foo, ResponseInterface $response)
    {
        $this->foo = $foo;
        $this->response = $response;
    }

    public function __invoke()
    {
        $rpc = new RpcClient();
//        $rs = $rpc->one('Comment.add', ['huang', 'wen']);
//        echo $rs;

        $rpc->call('Comment.add', ['huang', 'wen'], [$this, 'call3']);
        $rpc->call('Comment.sub', ['qiang', 'ling']);
        $rpc->loop([$this, 'call2']);
        $rpc->reset();

//        $response = $this->response->withHeader('Content-Type', 'text/html');
//        $response->getBody()
//            ->write("<html><head></head><body>$rs</body></html>");

//        return $response;
    }

    public function call($retval, $callinfo)
    {
        if ($callinfo == NULL) {
            echo 'callinfo1 is null....<br />';
        } else {
            echo '11111111111111------>' . '<br/>';
            var_dump($retval);
        }
    }

    public function call2($retval, $callinfo)
    {
        if ($callinfo == NULL) {
            echo 'callinfo2 is null....<br />';
        } else {
            echo '22222';
            var_dump($retval);
        }
    }

    public function call3($retval, $callinfo)
    {
        if ($callinfo == NULL) {
            echo 'callinfo3 is null....<br />';
        } else {
            echo '333333';
            var_dump($retval);
        }
    }
}
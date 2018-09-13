<?php

namespace Zithan\YarClient\Tests;

use Mockery\Matcher\AnyArgs;
use PHPUnit\Framework\TestCase;
use Zithan\YarClient\Client;
use Zithan\YarClient\Exceptions\YarException;

class ClientTest extends TestCase
{
    public function testOne($value = '')
    {
        $response = [

        ];
    }

    public function testOneWithYarRuntimeException()
    {
        $client = \Mockery::mock(\Yar_Client::class);
        $client->allows()->get(new AnyArgs())->andThrow(new \Exception('timeout'));

        $yarClient = \Mockery::mock(Client::class, ['mock-uri'])->makePartial();
        $yarClient->allows()->one()->andReturn($client);

        $this->expectException(YarException::class);
        $this->expectExceptionMessage('timeout');

        $yarClient->one('/Common/Goods/list', []);
    }
}

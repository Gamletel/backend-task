<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Infrastructure;

use Redis;
use RedisException;

class ConnectorFacade
{
    readonly protected  string $host;
    readonly protected int $port = 6379;
    readonly protected ?string $password = null;
    readonly protected ?int $dbindex = null;

    public Connector $connector;

    public function __construct($host, $port, $password, $dbindex)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->dbindex = $dbindex;
    }

    protected function build(): void
    {
        $redis = new Redis();
        try {
            $isConnected = $redis->connect($this->host, $this->port);
            if ($isConnected && $redis->ping('Pong')) {
                $redis->auth($this->password);
                $redis->select($this->dbindex);
                $this->connector = new Connector($redis);
            }
        } catch (RedisException $e) {
            error_log($e->getMessage());
        }
    }
}

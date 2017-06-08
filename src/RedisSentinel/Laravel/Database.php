<?php

namespace RedisSentinel\Laravel;

use Illuminate\Support\Arr;
use Predis\Client;
use Illuminate\Redis\Database as LaravelRedisDatabase;
use Predis\Connection\Aggregate\SentinelReplication;
use Predis\Connection\ConnectionInterface;

class Database extends LaravelRedisDatabase
{
    /**
     * Create an array of single connection or sentinel clients
     *
     * @param  array  $servers
     * @param  array  $options
     * @return array
     */
    protected function createSingleClients(array $servers, array $options = [])
    {
        $clients = [];

        foreach ($servers as $key => $server) {
            $options = array_merge($options, Arr::pull($server, 'options'));

            $clients[$key] = new Client($server, $options);

            if (isset($options['update_sentinels']) && boolval($options['update_sentinels']) === true) {
                /** @var ConnectionInterface $connection */
                $connection = ($clients[$key])->getConnection();

                if ($connection instanceof SentinelReplication) {
                    // is not defined on the interface so make sure we've got the right thing.
                    $connection->setUpdateSentinels(true);
                }
            }
        }

        return $clients;
    }
}

<?php
declare(strict_types=1);

namespace Xaddax\GraphQLExtension\Context;

use Behat\Behat\Context\Context;
use GuzzleHttp\ClientInterface;

interface ApiClientAwareContext extends Context
{
    /**
     * Sets Guzzle Client instance.
     *
     * @param \GuzzleHttp\Client $client Guzzle client.
     *
     * @return void
     */
    public function setClient(ClientInterface $client);

    public function setConfig(array $config);
}
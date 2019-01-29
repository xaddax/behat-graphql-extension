<?php
declare(strict_types=1);

namespace Xaddax\GraphQLExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use GuzzleHttp\ClientInterface;
use Xaddax\GraphQLExtension\Context\ApiClientAwareContext;

final class ApiClientAwareInitializer implements ContextInitializer
{
    /**
     * @var ClientInterface
     */
    private $client;

    /** @var array */
    private $config;

    /**
     * Initializes initializer.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof ApiClientAwareContext) {
            $context->setConfig($this->config); // must be set before client
            $context->setClient($this->client);
        }
    }
}
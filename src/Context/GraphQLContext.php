<?php
declare(strict_types=1);

namespace Xaddax\GraphQLExtension\Context;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\ClientInterface;

class GraphQLContext implements ApiClientAwareContext
{
    /** @var ClientInterface */
    private $client;
    /** @var array */
    private $config;

    static private $serverProcess;

    /**
     * @AfterFeature
     */
    public static function shutDownServer(AfterFeatureScope $scope)
    {
        proc_close(self::$serverProcess);
    }

    /**
     * @Given /^the php dsl code service is running$/
     */
    public function thePhpDslCodeServiceIsRunning()
    {
        $descriptorSpec = [
            0 => ["pipe", "r"],  // stdin is a pipe that the child will read from
            1 => ["pipe", "w"],  // stdout is a pipe that the child will write to
            2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
        ];
        $pipes = [];
        $php = $this->client->getConfig('php');
        $publicFolder = $this->client->getConfig('publicFolder');
        $serverUri = $this->client->getConfig('baseUrl');
        $cmd = "$php -S $serverUri -t $publicFolder";
        self::$serverProcess = proc_open($cmd, $descriptorSpec, $pipes);
    }

    /**
     * @When /^I call the GraphQL endpoint$/
     */
    public function iCallTheGraphQLEndpoint()
    {
        $this->setRequestPath($this->client->getConfig('path'));
        $this->setRequestMethod('POST');

        return $this->sendRequest();
    }

    /**
     * @Given the request body is:
     */
    public function theRequestBodyIs(PyStringNode $string)
    {
        throw new PendingException();
    }

    /**
     * @Then the response body is:
     */
    public function theResponseBodyIs(PyStringNode $string)
    {
        throw new PendingException();
    }

    /**
     * Sets Guzzle Client instance.
     *
     * @param \GuzzleHttp\Client $client Guzzle client.
     *
     * @return void
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}
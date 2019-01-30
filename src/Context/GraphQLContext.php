<?php
declare(strict_types=1);

namespace Xaddax\GraphQLExtension\Context;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Process\Process;
use Xaddax\GraphQLExtension\Exception\AssertionFailedException as GraphQLAssertionFailedException;
use function GuzzleHttp\Psr7\stream_for;
use function GuzzleHttp\Psr7\uri_for;

class GraphQLContext implements ApiClientAwareContext
{
    /** @var ClientInterface */
    private $client;
    /** @var array */
    private $config;
    /** @var RequestInterface */
    private $request;
    /** @var array */
    private $requestOptions = [];
    /** @var ResponseInterface */
    private $response;
    /** @var Uri */
    private $uri;
    /** @var Process */
    static private $serverProcess;

    /**
     * @AfterFeature
     */
    public static function shutDownServer(AfterFeatureScope $scope)
    {
        self::$serverProcess->stop(3, SIGKILL);
    }

    /**
     * @Given /^the php dsl code service is running$/
     */
    public function thePhpDslCodeServiceIsRunning()
    {
        $serverConfig = $this->config['server'];
        $php = $serverConfig['php'];
        $publicFolder = $serverConfig['publicFolder'];
        $serverUri = "{$this->uri->getHost()}:{$this->uri->getPort()}";
        $cmd = "$php -S $serverUri -t $publicFolder";

        self::$serverProcess = new Process($cmd);
        self::$serverProcess->start();
    }

    /**
     * @When /^I call the GraphQL endpoint$/
     */
    public function iCallTheGraphQLEndpoint()
    {
        return $this->sendRequest();
    }

    /**
     * @Given the request body is:
     */
    public function theRequestBodyIs(PyStringNode $string)
    {
        $this->request = $this->request->withBody(stream_for($string));

        return $this;
    }

    /**
     * @Then the response body is:
     */
    public function theResponseBodyIs(PyStringNode $content)
    {
        $this->requireResponse();
        $content = (string) $content;

        try {
            Assertion::same($body = (string) $this->response->getBody(), $content, sprintf(
                'Expected response body "%s", got "%s".',
                $content,
                $body
            ));
        } catch (AssertionFailedException $e) {
            throw new GraphQLAssertionFailedException($e->getMessage());
        }
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
        $uri = new Uri($this->config['baseUrl']);
        $path = $this->config['path'];
        $this->uri = Uri::resolve($uri, uri_for($path));
        $this->request = new Request($this->config['method'], $this->uri);
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Require a response object
     *
     * @throws RuntimeException
     */
    protected function requireResponse() {
        if (!$this->response) {
            throw new RuntimeException('The request has not been made yet, so no response object exists.');
        }
    }

    /**
     * Send the current request and set the response instance
     *
     * @throws RequestException
     * @return self
     */
    protected function sendRequest()
    {
        try {
            $this->response = $this->client->send(
                $this->request,
                $this->requestOptions
            );
        } catch (RequestException $e) {
            $this->response = $e->getResponse();

            if (!$this->response) {
                throw $e;
            }
        }

        return $this;
    }
}
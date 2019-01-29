<?php
declare(strict_types=1);

namespace Xaddax\GraphQLExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class GraphQLExtension implements ExtensionInterface
{
    const CLIENT_ID = 'graphql.client';

    public function getConfigKey()
    {
        return 'graphql';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
        // TODO: Implement initialize() method.
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('apiClientOptions')
                    ->addDefaultsIfNotSet()
                    ->ignoreExtraKeys(false)
                    ->end()
                ->scalarNode('baseUrl')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost:8080')
                    ->end()
                ->scalarNode('path')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('graphql')
                    ->end()
                ->scalarNode('method')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('POST')
                    ->end()
            ->end();
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadClient($container, $config);
        $this->loadContextInitializer($container, $config);
    }

    private function loadClient(ContainerBuilder $container, $config)
    {
        $guzzleClient = array_merge($config['apiClientOptions'], ['base_uri' => $config['baseUrl']]);
        $definition = new Definition('GuzzleHttp\Client', [$guzzleClient]);
        $container->setDefinition(self::CLIENT_ID, $definition);
    }

    private function loadContextInitializer(ContainerBuilder $container, $config)
    {
        $definition = new Definition(
            'Xaddax\GraphqlExtension\Context\Initializer\ApiClientAwareInitializer', [
            new Reference(self::CLIENT_ID),
            $config,
        ]
        );
        $definition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition('graphql.context_initializer', $definition);
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        // TODO: Implement process() method.
    }
}
<?php

namespace AMREU\NikBundle\DependencyInjection;

// use AMREU\GiltzaBundle\Controller\GiltzaController;
// use AMREU\GiltzaBundle\Service\GiltzaProvider;

use AMREU\NikBundle\Controller\NikController;
use AMREU\NikBundle\Service\NikService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

class NikExtension extends Extension
{
   public function load(array $configs, ContainerBuilder $container): void
   {
      $configuration = new Configuration();
      $config = $this->processConfiguration($configuration, $configs);

      $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
      $loader->load('services.yaml');

      $definition = $container->getDefinition(NikService::class);
      $definition->setArgument('$apiKey', $config['apiKey']);
      $definition->setArgument('$endPoint', $config['endPoint']);

      $definition2 = $container->getDefinition(NikController::class);
      $definition2->setArgument('$successUri', $config['successUri']);
      $definition2->setArgument('$timeout', $config['timeout']);

      // $definition2 = $container->getDefinition(GiltzaController::class);
      // $definition2->setArgument('$options', $config['controller']);
   }
}
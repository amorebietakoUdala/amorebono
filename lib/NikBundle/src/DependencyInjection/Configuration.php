<?php

namespace AMREU\NikBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Description of Configuration.
 *
 * @author ibilbao
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treebuilder = new TreeBuilder('nik');
        $rootNode = $treebuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('apiKey')->info('Nik Api Key')->isRequired()->end()
                ->scalarNode('successUri')->info('path to redirect after a successfull login')->isRequired()->end()
                ->scalarNode('endPoint')->defaultValue('http://svc.integracion.ejgv.jaso/ctxweb/ad56pbmiddleware')->end()
            ->end();
        //         ->arrayNode('controller')
        //             ->children()
        //                 ->scalarNode('successUri')->isRequired()->info('URL to go to after successful giltza login. For example: ("amreu_giltza_success")')->end()
        //                 ->scalarNode('response_type')->defaultValue('code')->end()
        //                 ->scalarNode('scope')->defaultValue('urn:izenpe:identity:global')->end()
        //                 ->scalarNode('prompt')->defaultValue('login')->end()
        //                 ->scalarNode('ui_locales')->defaultValue('eu')->end()
        //                 ->scalarNode('acr_values')->defaultValue('urn:safelayer:tws:policies:authentication:level:medium')->end()
        //             ->end()
        //     ->end();
        return $treebuilder;
    }
}
<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class FormCompilerPass
 * @package Admingenerator\GeneratorBundle\DependencyInjection\Compiler
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class FormPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->injectFormThemeConfiguration($container);
    }

    /**
     * Check if we need to automatically add form theme from admingen in the
     * application configuration.
     */
    private function injectFormThemeConfiguration(ContainerBuilder $container): void
    {
        if (($twigConfiguration = $container->getParameter('admingenerator.twig')) !== false) {
            $resources = $container->getParameter('twig.form.resources');
            $alreadyIn = in_array('bootstrap_3_layout.html.twig', $resources);

            if ($twigConfiguration['use_form_resources'] && !$alreadyIn) {
                $key = array_search('form_div_layout.html.twig', $resources) ?: 0;
                // Insert right after form_div_layout.html.twig if exists
                array_splice($resources, ++$key, 0, ['bootstrap_3_layout.html.twig']);

                $container->setParameter('twig.form.resources', $resources);
            }
        }
    }
}

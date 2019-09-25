<?php

/*
 * This file is part of itk-dev/user-management-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserManagementBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $twigLoaderId = 'twig.loader.native_filesystem';
        if ($container->hasDefinition($twigLoaderId)) {
            $loader = $container->getDefinition($twigLoaderId);

            // Prepend our path to FOSUser templates.
            $namespace = 'FOSUser';

            $paths = [];
            $paths[] = __DIR__.'/../../Resources/views/bundles/'.$namespace;
            // We have to prepend path to app templates to allow overriding our FOSUser template.
            if ($container->hasParameter('kernel.project_dir')) {
                $path = $container->getParameter('kernel.project_dir').'/templates/bundles/FOSUserBundle';
                if (is_dir($path)) {
                    $paths[] = $path;
                }
            }
            foreach ($paths as $path) {
                $loader->addMethodCall('prependPath', [$path, $namespace]);
            }
        }
    }
}

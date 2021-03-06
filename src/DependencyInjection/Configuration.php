<?php

/*
 * This file is part of itk-dev/user-management-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserManagementBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('itk_dev_user_management');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('site_url')->isRequired()->defaultValue('%env(SITE_URL)%')->end()
            ->scalarNode('site_name')->isRequired()->example('%env(SITE_NAME)%')->end()

            ->arrayNode('sender')->isRequired()->children()
            ->scalarNode('email')->isRequired()->defaultValue('%env(MAILER_EMAIL)%')->end()
            ->scalarNode('name')->isRequired()->defaultValue('%env(MAILER_NAME)%')->end()
            ->end()
            ->end()

            ->arrayNode('user_created')->info('Template for mails sent to new users')->children()
            ->scalarNode('subject')->isRequired()->example('{{ site_name }} – new user created')->end()
            ->scalarNode('header')->example('User created on {{ site_name }}')->end()
            ->scalarNode('body')->isRequired()->example(
                <<<'BODY'
<p style='margin: 0;'>You have been created as user on {{ site_name }} with email address {{ user.email }}.</p>
<p style='margin: 0;'>To get started, you have to choose a password.</p>
<p style='margin: 0;'>After choosing a password, you can sign in with your email address ({{ user.email }}) and the choosen password.</p>
BODY
            )->end()
            ->arrayNode('button')->children()
            ->scalarNode('text')->isRequired()->example('Choose password')->end()
            ->end()->end()
            ->scalarNode('footer')->example('<p style="margin: 0;">Best regards,<br/> {{ site_name }}</p>')->end()
            ->end()
            ->end()

            ->booleanNode('notify_user_on_create')->isRequired()->defaultFalse()->info('If set, new users will be notified via email about their account.')->end()

            ->end();

        return $treeBuilder;
    }
}

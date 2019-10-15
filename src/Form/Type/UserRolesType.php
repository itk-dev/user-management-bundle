<?php

/*
 * This file is part of itk-dev/user-management-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserManagementBundle\Form\Type;

use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserRolesType extends ChoiceType
{
    /** @var \Symfony\Component\Security\Core\Role\RoleHierarchyInterface */
    private $roleHierarchy;

    public function __construct(
        RoleHierarchyInterface $roleHierarchy
    ) {
        parent::__construct(null);
        $this->roleHierarchy = $roleHierarchy;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['choice_loader'] = new CallbackChoiceLoader(function () use ($options) {
            // Note: We assume that all roles are reachable from ROLE_ADMIN.
            $roles = $this->roleHierarchy->getReachableRoleNames((array) $options['base_roles']);

            return array_combine($roles, $roles);
        });

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => true,
            'base_roles' => ['ROLE_ADMIN'],
        ]);
    }
}

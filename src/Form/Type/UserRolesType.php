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
        $options['choice_loader'] = new CallbackChoiceLoader(function () {
            // Note: We assume that all roles are reachable from ROLE_ADMIN.
            $roles = $this->roleHierarchy->getReachableRoleNames(['ROLE_ADMIN']);

            return array_combine($roles, $roles);
        });

        parent::buildForm($builder, $options);
    }
}

# ITK Dev – User management

## Installation

```sh
composer config repositories.itk-dev/user-management-bundle vcs https://github.com/itk-dev/user-management-bundle
composer require itk-dev/user-management-bundle:dev-master
```

Register the bundle in `config/bundles.php`:

```php
<?php

return [
    …
    ItkDev\UserManagementBundle\ItkDevUserManagementBundle::class => ['all' => true],
];
```

## Configuration

Use `bin/console config:dump-reference ItkDevUserManagementBundle` to see the
configuration reference.

## Example

```yaml
itk_dev_user_management:
    site_name: '%env(SITE_NAME)%'
    site_url: '%env(SITE_URL)%'

    sender:
        email: '%env(MAILER_EMAIL)%'
        name: '%env(MAILER_NAME)%'

    # Template for mails sent to new users
    user_created:
        subject: '{{ site_name }} – new user created'
        header: 'User created on {{ site_name }}'
        body: |
            <p style='margin: 0;'>
              You have been registered as user on {{ site_name }} with email
              address {{ user.email }}.
            </p>
            <p style='margin: 0;'>
              To get started, you have to choose a password.
            </p>
            <p style='margin: 0;'>
              After choosing a password, you can sign in with your email address
              ({{ user.email }}) and the choosen password.
            </p>
        button:
            text: 'Choose password'
        footer: '<p style="margin: 0;">Best regards,<br/> {{ site_name }}</p>'

    # Set to true to automatically notify users on creation.
    # Otherwise, use the `itk-dev:user-management:notify-users-created` console
    # command or call `UserManager::notifyUserCreated` to notify new users.
    notify_user_on_create: false
```

[Configure
FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.rst#step-3-create-your-user-class)
and make sure to use the user manager from this bundle:

```yaml
fos_user:
    …
    service:
        user_manager: ItkDev\UserManagementBundle\Doctrine\UserManager
    …
```

## Commands

```sh
bin/console itk-dev:user-management:notify-users-created --help
```

## Controller

For convenience, you can use
[`ItkDev\UserManagementBundle\Controller\UserController`](src/Controller/UserController.php)
in your EasyAdminBundle configuration:

```yaml
easy_admin:
    entities:
        User:
            class: App\Entity\User
            controller: ItkDev\UserManagementBundle\Controller\UserController
```

### User roles form type

When editing user roles, you can use
[`ItkDev\UserManagementBundle\Form\Type\UserRolesType`](src/Form/Type/UserRolesType.php)
to get a list of roles in the role hierarchy (only roles reachable from
`options.base_roles`, which defaults to `[ROLE_ADMIN]`, are listed):

```yaml
easy_admin:
    entities:
        User:
            …
            form:
                fields:
                    …
                    - property: roles
                      type: ItkDev\UserManagementBundle\Form\Type\UserRolesType
                      # Optionally, specify base roles:
                      type_options:
                          base_roles: [ROLE_ADMIN] # default
```

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

```yaml
itk_dev_user_management:
    site_name: example.com

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

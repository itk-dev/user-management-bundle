# ITK Dev – User management

## Installation

```sh
composer config repositories.itk-dev/user-management-bundle vcs https://github.com/itk-dev/user-management-bundle
composer require itk-dev/user-management-bundle:dev-master
```

## Configuration

```yaml
itk_dev_user_management:
    site_name:            example.com

    # Template for mails sent to new users
    user_created:
        subject:              '{{ site_name }} – new user created'
        header:               'User created on {{ site_name }}'
        body:                 "<p style='margin: 0;'>You have been created as user on {{ site_name }} with email address {{ user.email }}.</p>\n<p style='margin: 0;'>To get started, you have to choose a password.</p>\n<p style='margin: 0;'>After choosing a password, you can sign in with your email address ({{ user.email }}) and the choosen password.</p>"
        button:
            text:                 'Choose password'
        footer:               '<p style="margin: 0;">Best regards,<br/> {{ site_name }}</p>'
```

```yaml
fos_user:
    …
    service:
        user_manager: ItkDev\UserManagementBundle\Doctrine\UserManager
        mailer: fos_user.mailer.twig_swift
    …
```

## Commands

```sh
bin/console itk-dev:user-management:notify-users-created --help
```

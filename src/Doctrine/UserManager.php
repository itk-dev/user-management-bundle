<?php

/*
 * This file is part of itk-dev/user-management-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserManagementBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\User;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class UserManager extends BaseUserManager
{
    /** @var \FOS\UserBundle\Util\TokenGeneratorInterface */
    private $tokenGenerator;

    /** @var \Twig_Environment */
    private $twig;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \FOS\UserBundle\Mailer\MailerInterface */
    private $userMailer;

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var ContainerInterface */
    private $configuration;

    public function __construct(
        PasswordUpdaterInterface $passwordUpdater,
        CanonicalFieldsUpdater $canonicalFieldsUpdater,
        ObjectManager $om,
        string $class,
        TokenGeneratorInterface $tokenGenerator,
        Environment $twig,
        RouterInterface $router,
        MailerInterface $userMailer,
        \Swift_Mailer $mailer,
        array $configuration
    ) {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);
        $this->tokenGenerator = $tokenGenerator;
        $this->twig = $twig;
        $this->router = $router;
        $this->userMailer = $userMailer;
        $this->mailer = $mailer;
        $this->configuration = $configuration;
    }

    public function createUser()
    {
        $user = parent::createUser();
        $user->setPlainPassword(uniqid('', true));
        $user->setEnabled(true);

        return $user;
    }

    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $isNew = null === $user->getId();
        $user->setUsername($user->getEmail());
        parent::updateUser($user, $andFlush);

        if ($isNew && $this->configuration['notify_user_on_create']) {
            // @TODO: Add flash bag message here?
            $this->notifyUserCreated($user);
        }
    }

    public function notifyUserCreated(User $user, $andFlush = true, array $options = [])
    {
        if (null === $user->getConfirmationToken()) {
            // @var $tokenGenerator TokenGeneratorInterface
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $user->setPasswordRequestedAt(new \DateTime());
        $this->updateUser($user, $andFlush);
        $message = $this->createUserCreatedMessage($user, $options);
        $this->mailer->send($message);
    }

    private function createUserCreatedMessage(UserInterface $user, array $options = [])
    {
        $url = $this->router->generate('fos_user_resetting_reset', [
            'token' => $user->getConfirmationToken(),
            'create' => true,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        $sender = $this->configuration['sender'];
        $config = $this->configuration['user_created'];
        $context = [
            'reset_password_url' => $url,
            'user' => $user,
            'sender' => $sender,
        ]
            + $this->configuration['user_created']
            + $this->configuration;

        $subject = $this->twig->createTemplate($config['subject'])->render($context);
        $header = $this->twig->createTemplate($config['header'])->render($context);
        $message = $options['message'] ?? $config['message'] ?? null;
        if (null !== $message) {
            $message = $this->twig->createTemplate($message)->render($context);
        }
        $body = $this->twig->createTemplate($config['body'])->render($context);
        $buttonText = $this->twig->createTemplate($config['button']['text'])->render($context);
        $footer = $this->twig->createTemplate($config['footer'])->render($context);

        $body = $this->twig->render('@ItkDevUserManagement/email/user/user_created_user.html.twig', [
            'reset_password_url' => $url,
            'header' => $header,
            'message' => $message,
            'body' => $body,
            'button' => [
                'url' => $url,
                'text' => $buttonText,
            ],
            'footer' => $footer,
        ]);

        return (new \Swift_Message($subject))
            ->setFrom($sender['email'], $sender['name'])
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');
    }

    public function resetPassword(UserInterface $user, $andFlush = true)
    {
        if (null === $user->getConfirmationToken()) {
            // @var $tokenGenerator TokenGeneratorInterface
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $user->setPasswordRequestedAt(new \DateTime());
        $this->updateUser($user, $andFlush);

        $this->userMailer->sendResettingEmailMessage($user);
    }

    public function findUsersBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }
}

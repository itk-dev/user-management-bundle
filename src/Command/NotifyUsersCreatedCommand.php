<?php

/*
 * This file is part of itk-dev/user-management-bundle.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\UserManagementBundle\Command;

use ItkDev\UserManagementBundle\Doctrine\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class NotifyUsersCreatedCommand extends Command
{
    protected static $defaultName = 'itk-dev:user-management:notify-users-created';

    /** @var UserManager */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }

    protected function configure()
    {
        $this
            ->addArgument('user-name', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'User names to notify')
            ->addOption('message', null, InputOption::VALUE_REQUIRED, 'Additional message to include in email sent to users')
            ->setDescription('Notify new users (who have not yet logged in) about their account.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $users = $this->userManager->findUsersBy(['lastLogin' => null]);
        if (empty($users)) {
            $output->writeln('No new users found.');

            return;
        }
        $userNames = $input->getArgument('user-name');
        $message = $this->getMessage($input->getOption('message'));
        $options = ['message' => $message];
        foreach ($users as $user) {
            if (!empty($userNames) && !\in_array($user->getUsername(), $userNames, true)) {
                continue;
            }
            $question = new ConfirmationQuestion(sprintf('Notify user %s? ', $user->getEmail()), false);
            if (!$input->isInteractive() || $helper->ask($input, $output, $question)) {
                $output->writeln(sprintf('User %s notified.', $user->getEmail()));
                $this->userManager->notifyUserCreated($user, true, $options);
            }
        }
    }

    protected function getMessage(string $message = null)
    {
        if (null === $message) {
            return null;
        }
        if (preg_match('/^@(?P<path>.+)/', $message, $matches)) {
            $path = $matches['path'];
            if (!is_readable($path)) {
                throw new InvalidArgumentException(sprintf('Invalid message file name: %s', $path));
            }
            $message = file_get_contents($path);
        }

        return $message;
    }
}

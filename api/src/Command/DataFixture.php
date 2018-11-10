<?php

namespace App\Command;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DataFixture
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class DataFixture extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('data:fixture:load')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates test data')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create test data.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new User();
        $user->setUserId('olxaovi1lIadJ0yERxOpYBA5E4U2');
        $user->setEmail('test@test.nl');
        $user->setCreateValues(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->flush();
    }
}
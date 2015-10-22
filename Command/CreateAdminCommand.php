<?php

namespace Tec\Ayt\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Tec\Ayt\CoreBundle\Entity\Admin;


class CreateAdminCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('ayt:admin:create')
            ->setDescription("Create a new Super Admin user for Ayt Control Panel")
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Admin username'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Admin password'
            )
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # Initialize Doctrine
        $doctrine = $this->getContainer()->get('doctrine');
        /* @var \Doctrine\ORM\EntityManager $em */
        $em = $doctrine->getManager();

        // Load command arguments
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        // Load security encoder
        $factory = $this->getContainer()->get('security.encoder_factory');

        // Create user
        $user = new Admin();

        $user->setUsername($username);
        $user->setFullName("Webmaster");
        $user->setRole('ROLE_SUPER_ADMIN');
        $user->setIsActive(1);

        /* @var \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $encoder */
        $encoder = $factory->getEncoder($user);
        $encodedPassword = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($encodedPassword);

        // Persist to database
        $em->persist($user);
        $em->flush();

        $output->writeln("Created SUPER_ADMIN user <info>" . $username . "</info> successfully!");
    }
}

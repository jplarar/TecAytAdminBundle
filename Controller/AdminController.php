<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Tec\Ayt\CoreBundle\Entity\Admin;

class AdminController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Admin';
    const NAMESPACED_FORM_TYPE = 'Tec\Ayt\AdminBundle\Form\Type\AdminType';

    public function listAction()
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        $query = $repository->createQueryBuilder('a');
        $admins = $query->getQuery()->getResult();
        return $this->render('TecAytAdminBundle:Admin:list.html.twig', array(
            'admins' => $admins
        ));
    }

    public function processAction(Request $request, $id)
    {
        // Form options
        $options = array();

        /** @var Admin $admin */
        $admin = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->findOneBy(array('adminId' => $id));

        if (!$admin) {
            $class = self::NAMESPACED_CLASS; // PHP quirk.
            $admin = new $class();
            $options['mode'] = 'new';
        } else {
            $options['mode'] = 'edit';
        }

        $formType = self::NAMESPACED_FORM_TYPE; // PHP quirk.
        $form = $this->createForm(new $formType(), $admin, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            ## Extend the default form validation
            if($admin->getAdminId() <= 0){
                // Load security encoder
                $factory = $this->get('security.encoder_factory');

                /* @var \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $encoder */
                $encoder = $factory->getEncoder($admin);
                $encodedPassword = $encoder->encodePassword($admin->getPassword(), $admin->getSalt());
                $admin->setPassword($encodedPassword);

                $em->persist($admin);
            }

            // Persist to database using Entity Manager
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_admin_list')
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Admin:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function passwordAction(Request $request, $id)
    {
        // Form options
        $options = array();

        /** @var Admin $admin */
        $admin = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->findOneBy(array('adminId' => $id));

        if (!$admin) {
            throw $this->createNotFoundException(
                'No admin found for id '.$id
            );
        }

        $options['password'] = true;

        $formType = self::NAMESPACED_FORM_TYPE; // PHP quirk.
        $form = $this->createForm(new $formType(), $admin, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            $password = $form->get('password')->getData();
            ## Extend the default form validation
            $factory = $this->get('security.encoder_factory');

            /* @var \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $encoder */
            $encoder = $factory->getEncoder($admin);
            $encodedPassword = $encoder->encodePassword($password, $admin->getSalt());
            $admin->setPassword($encodedPassword);

            // Persist to database using Entity Manager
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_admin_list')
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Admin:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deactivateAction($id)
    {
        /** @var Admin $admin */
        $admin = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$admin) {
            throw $this->createNotFoundException(
                'No admin found for id '.$id
            );
        }

        if ($admin->getIsActive()) {
            $admin->setIsActive(0);
        } else {
            $admin->setIsActive(1);
        }
        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_admin_list')
        );
    }

    public function viewAction($id)
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        /** @var Admin $admin */
        $admin = $repository->find($id);

        if (!$admin) {
            throw $this->createNotFoundException(
                'No admin found for id '.$id
            );
        }

        return $this->render('TecAytAdminBundle:Admin:view.html.twig', array(
            'admin' => $admin,
        ));
    }

}

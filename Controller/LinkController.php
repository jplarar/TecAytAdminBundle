<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Tec\Ayt\CoreBundle\Entity\Link;

class LinkController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Link';
    const NAMESPACED_FORM_TYPE = 'Tec\Ayt\AdminBundle\Form\Type\LinkType';

    public function listAction()
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        $query = $repository->createQueryBuilder('l');
        $links = $query->getQuery()->getResult();

        return $this->render('TecAytAdminBundle:Link:list.html.twig', array(
            'links' => $links
        ));
    }

    public function processAction(Request $request, $id)
    {
        // Form options
        $options = array();

        /** @var Link $link */
        $link = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)->find($id);

        if (!$link) {
            $class = self::NAMESPACED_CLASS; // PHP bug.
            $link = new $class();
            $options['mode'] = 'new';
        } else {
            $options['mode'] = 'edit';
        }

        $formType = self::NAMESPACED_FORM_TYPE; // PHP bug.
        $form = $this->createForm(new $formType(), $link, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            // Persist to database using Entity Manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($link);
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_link_list')
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Link:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        /** @var Link $link */
        $link = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$link) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($link);
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_link_list')
        );
    }
}

<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Tec\Ayt\CoreBundle\Entity\Topic;

class TopicController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Topic';

    public function listAction()
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        $query = $repository->createQueryBuilder('d');
        $topics = $query->getQuery()->getResult();

        return $this->render('TecAytAdminBundle:Topic:list.html.twig', array(
            'topics' => $topics
        ));
    }

    public function deleteAction($id)
    {
        /** @var Topic $topic */
        $topic = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$topic) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($topic);
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_topic_list')
        );
    }



}

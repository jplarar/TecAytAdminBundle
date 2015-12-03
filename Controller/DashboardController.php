<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Niva\Beaver\ControlBundle\Utility\ImageResize;

class DashboardController extends Controller
{
    public function mainAction()
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        $free_to_mbs = $free / (1024*1024);
        $total_to_mbs = $total / (1024*1024);
        $units = 'MB';
        if ($total_to_mbs > 1000) {
            $free_to_mbs = $free_to_mbs / 1024;
            $total_to_mbs = $total_to_mbs / 1024;
            $units = 'GB';
        }

        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\Admin');
        $admins = $repository->createQueryBuilder('a')
            ->select('count(a.adminId)')
            ->getQuery()
            ->getSingleScalarResult();

        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\User');
        $friends = $repository->createQueryBuilder('u')
            ->select('count(u.userId)')
            ->where("u.role = 'ROLE_FRIEND'")
            ->getQuery()
            ->getSingleScalarResult();

        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\User');
        $active = $repository->createQueryBuilder('u')
            ->select('count(u.userId)')
            ->where("u.role = 'ROLE_ACTIVE'")
            ->getQuery()
            ->getSingleScalarResult();

        $today = new \DateTime('now');

        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\Event');
        $events = $repository->createQueryBuilder('e')
            ->select('count(e.eventId)')
            ->where("e.date > :today")
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\Content');
        $contents = $repository->createQueryBuilder('c')
            ->select('count(c.contentId)')
            ->getQuery()
            ->getSingleScalarResult();

        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\Link');
        $links = $repository->createQueryBuilder('l')
            ->select('count(l.linkId)')
            ->getQuery()
            ->getSingleScalarResult();


        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\Topic');
        $topics = $repository->createQueryBuilder('t')
            ->select('count(t.topicId)')
            ->getQuery()
            ->getSingleScalarResult();


        return $this->render('TecAytAdminBundle:Dashboard:main.html.twig', array(
            'units' => $units,
            'free' => $free_to_mbs,
            'total' => $total_to_mbs,
            'admins' => $admins,
            'friends' => $friends,
            'active' => $active,
            'events' => $events,
            'contents' => $contents,
            'links' => $links,
            'topics' => $topics
        ));
    }

}

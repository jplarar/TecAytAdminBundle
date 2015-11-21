<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Tec\Ayt\CoreBundle\Entity\Sponsor;

class SponsorController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Sponsor';
    const NAMESPACED_FORM_TYPE = 'Tec\Ayt\AdminBundle\Form\Type\SponsorType';

    public function listAction()
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        $query = $repository->createQueryBuilder('b');
        $sponsors = $query->getQuery()->getResult();

        return $this->render('TecAytAdminBundle:Sponsor:list.html.twig', array(
            'sponsors' => $sponsors
        ));
    }

    public function processAction(Request $request, $id)
    {
        // Form options
        $options = array();

        /** @var Sponsor $sponsor */
        $sponsor = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)->find($id);

        if (!$sponsor) {
            $class = self::NAMESPACED_CLASS; // PHP bug.
            $sponsor = new $class();
            $options['mode'] = 'new';
        } else {
            $options['mode'] = 'edit';
        }

        $formType = self::NAMESPACED_FORM_TYPE; // PHP bug.
        $form = $this->createForm(new $formType(), $sponsor, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            // Upload file
            if ($sponsor->getFile() != null) {
                $path = $this->get('kernel')->getRootDir() . '/../web/uploads';
                $sponsor->uploadFile($path);
            }

            // Persist to database using Entity Manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($sponsor);
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_sponsor_list')
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Sponsor:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        /** @var Sponsor $sponsor */
        $sponsor = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($sponsor);
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_sponsor_list')
        );
    }

    public function downloadAction($id)
    {
        /** @var Sponsor $sponsor */
        $sponsor = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        $response = $this->generateDownloadResponse($sponsor);
        return $response;
    }

    #####################################
    #          PRIVATE FUNCTIONS        #
    #####################################
    /**
     * Generates Download Response
     *
     * Verifies if file exists and file's type before generating the response.
     *
     * @param  Sponsor $sponsor
     * @return BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function generateDownloadResponse($sponsor)
    {
        // Check if file exists
        $file = $sponsor->getFileName();
        $path = $this->get('kernel')->getRootDir() . '/../web/uploads/'. $file;
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        // Check the File Type: http://en.wikipedia.org/wiki/Internet_media_type
        $extArr = explode('.', $path);
        $ext = end($extArr);

        // Clean the filename
        $filename = preg_replace("/[^a-z0-9_\s]+/i", "", $sponsor->getName()) . '.' . $ext;
        $mimeType = mime_content_type($path);

        // Generate Response
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $mimeType);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}

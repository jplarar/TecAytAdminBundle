<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Tec\Ayt\CoreBundle\Entity\Album;

class AlbumController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Album';
    const NAMESPACED_FORM_TYPE = 'Tec\Ayt\AdminBundle\Form\Type\AlbumType';

    public function listAction()
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        $query = $repository->createQueryBuilder('d');
        $albums = $query->getQuery()->getResult();

        return $this->render('TecAytAdminBundle:Album:list.html.twig', array(
            'albums' => $albums
        ));
    }

    public function processAction(Request $request, $id)
    {
        // Form options
        $options = array();

        /** @var Album $album */
        $album = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)->find($id);

        if (!$album) {
            $class = self::NAMESPACED_CLASS; // PHP bug.
            $album = new $class();
            $options['mode'] = 'new';
        } else {
            $options['mode'] = 'edit';
        }

        $formType = self::NAMESPACED_FORM_TYPE; // PHP bug.
        $form = $this->createForm(new $formType(), $album, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            // Upload file
            if ($album->getFile() != null) {
                $path = $this->get('kernel')->getRootDir() . '/../web/uploads';
                $album->uploadFile($path);
            }

            // Persist to database using Entity Manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($album);
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_album_list')
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Album:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        /** @var Album $album */
        $album = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$album) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($album);
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_album_list')
        );
    }

    public function viewAction($id)
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        /** @var Album $album */
        $album = $repository->find($album);

        if (!$album) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        return $this->render('TecAytAdminBundle:Album:view.html.twig');
    }

    public function downloadAction($id)
    {
        /** @var Album $album */
        $album = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$album) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        $response = $this->generateDownloadResponse($album);
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
     * @param  Album $album
     * @return BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function generateDownloadResponse($album)
    {
        // Check if file exists
        $file = $album->getFileName();
        $path = $this->get('kernel')->getRootDir() . '/../web/uploads/'. $file;
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        // Check the File Type: http://en.wikipedia.org/wiki/Internet_media_type
        $extArr = explode('.', $path);
        $ext = end($extArr);

        // Clean the filename
        $filename = preg_replace("/[^a-z0-9_\s]+/i", "", $album->getName()) . '.' . $ext;
        $mimeType = mime_content_type($path);

        // Generate Response
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $mimeType);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}

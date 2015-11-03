<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Tec\Ayt\CoreBundle\Entity\Content;

class ContentController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Content';
    const NAMESPACED_FORM_TYPE = 'Tec\Ayt\AdminBundle\Form\Type\ContentType';

    public function listAction($id)
    {
        $contents = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)->findBy(
            array(
                'albumId' => $id
            )
        );

        $album = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\Album')->find($id);

        return $this->render('TecAytAdminBundle:Content:list.html.twig', array(
            'contents' => $contents,
            'album' => $album
        ));
    }

    public function processAction(Request $request, $id, $aid)
    {
        // Form options
        $options = array();

        /** @var Content $content */
        $content = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)->find($id);

        if (!$content) {
            $class = self::NAMESPACED_CLASS; // PHP bug.
            $content = new $class();
            $options['mode'] = 'new';
        } else {
            $options['mode'] = 'edit';
        }

        $formType = self::NAMESPACED_FORM_TYPE; // PHP bug.
        $form = $this->createForm(new $formType(), $content, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            // Upload file
            if ($content->getFile() != null) {
                $path = $this->get('kernel')->getRootDir() . '/../web/uploads';
                $content->uploadFile($path);
            }

            if ($options['mode'] = 'new') {
                /** @var \Tec\Ayt\CoreBundle\Entity\Album $album */
                $album = $this->getDoctrine()->getRepository('Tec\Ayt\CoreBundle\Entity\Album')->find($aid);
                $content->setAlbumId($album);
            }

            // Persist to database using Entity Manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($content);
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_content_list', array('id' => $aid))
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Content:form.html.twig', array(
            'form' => $form->createView(),
            'id' => $aid
        ));
    }

    public function deleteAction($id, $aid)
    {
        /** @var Content $content */
        $content = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$content) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($content);
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_content_list', array(
                'id' => $aid
            ))
        );
    }

    public function viewAction($id)
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        /** @var Content $content */
        $content = $repository->find($content);

        if (!$content) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        return $this->render('TecAytAdminBundle:Content:view.html.twig');
    }

    public function downloadAction($id)
    {
        /** @var Content $content */
        $content = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$content) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        $response = $this->generateDownloadResponse($content);
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
     * @param  Content $content
     * @return BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function generateDownloadResponse($content)
    {
        // Check if file exists
        $file = $content->getFileName();
        $path = $this->get('kernel')->getRootDir() . '/../web/uploads/'. $file;
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        // Check the File Type: http://en.wikipedia.org/wiki/Internet_media_type
        $extArr = explode('.', $path);
        $ext = end($extArr);

        // Clean the filename
        $filename = preg_replace("/[^a-z0-9_\s]+/i", "", $content->getName()) . '.' . $ext;
        $mimeType = mime_content_type($path);

        // Generate Response
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $mimeType);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}

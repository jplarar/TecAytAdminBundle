<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Tec\Ayt\CoreBundle\Entity\Banner;

class BannerController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Banner';
    const NAMESPACED_FORM_TYPE = 'Tec\Ayt\AdminBundle\Form\Type\BannerType';

    public function listAction()
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        $query = $repository->createQueryBuilder('b');
        $banners = $query->getQuery()->getResult();

        return $this->render('TecAytAdminBundle:Banner:list.html.twig', array(
            'banners' => $banners
        ));
    }

    public function processAction(Request $request, $id)
    {
        // Form options
        $options = array();

        /** @var Banner $banner */
        $banner = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)->find($id);

        if (!$banner) {
            $class = self::NAMESPACED_CLASS; // PHP bug.
            $banner = new $class();
            $options['mode'] = 'new';
        } else {
            $options['mode'] = 'edit';
        }

        $formType = self::NAMESPACED_FORM_TYPE; // PHP bug.
        $form = $this->createForm(new $formType(), $banner, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            // Upload file
            if ($banner->getFile() != null) {
                $path = $this->get('kernel')->getRootDir() . '/../web/uploads';
                $banner->uploadFile($path);
            }

            // Persist to database using Entity Manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($banner);
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_banner_list')
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Banner:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        /** @var Banner $banner */
        $banner = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$banner) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($banner);
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_banner_list')
        );
    }

    public function downloadAction($id)
    {
        /** @var Banner $banner */
        $banner = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$banner) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        $response = $this->generateDownloadResponse($banner);
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
     * @param  Banner $banner
     * @return BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function generateDownloadResponse($banner)
    {
        // Check if file exists
        $file = $banner->getFileName();
        $path = $this->get('kernel')->getRootDir() . '/../web/uploads/'. $file;
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        // Check the File Type: http://en.wikipedia.org/wiki/Internet_media_type
        $extArr = explode('.', $path);
        $ext = end($extArr);

        // Clean the filename
        $filename = preg_replace("/[^a-z0-9_\s]+/i", "", $banner->getName()) . '.' . $ext;
        $mimeType = mime_content_type($path);

        // Generate Response
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $mimeType);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}

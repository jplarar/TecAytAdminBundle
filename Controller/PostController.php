<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Tec\Ayt\CoreBundle\Entity\Post;

class PostController extends Controller
{
    const NAMESPACED_CLASS = 'Tec\Ayt\CoreBundle\Entity\Post';
    const NAMESPACED_FORM_TYPE = 'Tec\Ayt\AdminBundle\Form\Type\PostType';

    public function listAction()
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        $query = $repository->createQueryBuilder('d');
        $posts = $query->getQuery()->getResult();

        return $this->render('TecAytAdminBundle:Post:list.html.twig', array(
            'posts' => $posts
        ));
    }

    public function processAction(Request $request, $id)
    {
        // Form options
        $options = array();

        /** @var Post $post */
        $post = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)->find($id);

        if (!$post) {
            $class = self::NAMESPACED_CLASS; // PHP bug.
            $post = new $class();
            $options['mode'] = 'new';
        } else {
            $options['mode'] = 'edit';
        }

        $formType = self::NAMESPACED_FORM_TYPE; // PHP bug.
        $form = $this->createForm(new $formType(), $post, $options);

        // Check if form was submitted
        $form->handleRequest($request);

        // Check form validation
        if ($form->isValid()) {

            // Upload file
            if ($post->getFile() != null) {
                $path = $this->get('kernel')->getRootDir() . '/../web/uploads';
                $post->uploadFile($path);
            }

            // Persist to database using Entity Manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect(
                $this->generateUrl('tec_ayt_admin_post_list')
            );
        }

        // Render as new Entity
        return $this->render('TecAytAdminBundle:Post:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        /** @var Post $post */
        $post = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        // Persist to database using Entity Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirect(
            $this->generateUrl('tec_ayt_admin_post_list')
        );
    }

    public function viewAction($id)
    {
        /* @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS);
        /** @var Post $post */
        $post = $repository->find($post);

        if (!$post) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        return $this->render('TecAytAdminBundle:Post:view.html.twig');
    }

    public function downloadAction($id)
    {
        /** @var Post $post */
        $post = $this->getDoctrine()->getRepository(self::NAMESPACED_CLASS)
            ->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                'No web object found for id '.$id
            );
        }

        $response = $this->generateDownloadResponse($post);
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
     * @param  Post $post
     * @return BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function generateDownloadResponse($post)
    {
        // Check if file exists
        $file = $post->getFileName();
        $path = $this->get('kernel')->getRootDir() . '/../web/uploads/'. $file;
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        // Check the File Type: http://en.wikipedia.org/wiki/Internet_media_type
        $extArr = explode('.', $path);
        $ext = end($extArr);

        // Clean the filename
        $filename = preg_replace("/[^a-z0-9_\s]+/i", "", $post->getTitle()) . '.' . $ext;
        $mimeType = mime_content_type($path);

        // Generate Response
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $mimeType);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}

<?php

namespace Tec\Ayt\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Niva\Beaver\ControlBundle\Utility\ImageResize;

class DashboardController extends Controller
{
    public function mainAction()
    {

        return $this->render('TecAytAdminBundle:Dashboard:main.html.twig', array(

        ));
    }

}

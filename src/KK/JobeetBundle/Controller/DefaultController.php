<?php
// src/KK/JobeetBundle/Controller/DefaultController.php;

namespace KK\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class DefaultController extends Controller
{
    public function loginAction()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();

        // Get the login error if there is one present
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes
                ->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('KKJobeetBundle:Default:login.html.twig', array(
            // Last user name entered by user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error
        ));
    }
}

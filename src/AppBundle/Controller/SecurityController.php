<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;


class SecurityController extends Controller{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request){

        try{
            $authenticationUtils = $this->get('security.authentication_utils');

            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();

            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render(
                'default/login.html.twig',
                array(
                    // last username entered by the user
                    'last_username' => $lastUsername,
                    'error'         => $error,
                )
            );

        }
        catch(\Exception $e){
            $this->addFlash(
                'notice',
                'Error: Login Failed!'
            );
            return $this->redirectToRoute('login');

        }

        
    }


    /**
     * @Route("/login_check", name="login_check")
     */
    public function securityCheckAction(){
    }


    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request){
        try{
            $user = new User();
            $form = $this->createForm(UserType::class, $user);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                //Encode the password
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $user->setUserRole('ROLE_USER');

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash(
                'notice',
                'Registration successfull!'
                );
                return $this->redirectToRoute('login');
            }

            return $this->render(
                'default/register.html.twig',
                array('form' => $form->createView())
            );    
        }

        catch(UniqueConstraintViolationException $e){
            $this->addFlash(
                'notice',
                'Error: Cannot register!'
            );
            return $this->redirectToRoute('login');
        }
        catch(\Exception $e){
            #$logger = $this->get('logger');
            #$logger->error($e->getMessage());
            $this->addFlash(
                'notice',
                'Error: Cannot register!'
            );
            return $this->redirectToRoute('login');
        }
        
    }    

    

}

<?php

namespace Karismatik\ChatBotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('KarismatikChatBotBundle:Default:index.html.twig');
    }

    /**
     * @Route("/test")
     */
    public function testAction(Request $request)
    {
        // tableau de mots à vérifier
        $words  = array('hello','bonjour','salut','coucou',
            'cc','hi','bonsoir','good morning');

        $text = $request->request->get('text');

        $lev = levenshtein($text, 'bonsoir');

        if($lev<3)
            return new Response('Bonjour est ce que vouvlez .....');
        else
            return new Response('verfier votre saisie');

    }
}

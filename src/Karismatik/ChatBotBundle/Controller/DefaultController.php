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
     * @Route("/conversation")
     */
    public function conversationAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $text = $request->request->get('text');
        $conversations = $entityManager->getRepository('KarismatikChatBotBundle:Conversation')->getResponseByInput($text);

        $result = $this->getBestResponse($text, $conversations);

        if ($result) {
            if ($result['lev'] < 3 && strlen($text) > 2) {
                return new Response($result['response']);
            } else {
                return new Response('Tu veux dire "' . $result['input'] . '"?');
            }
        } else {

            return new Response("J'ai pas compris!");
        }
    }

    /**
     * @param $pattern
     * @param $results
     * @return array|mixed
     */
    public function getBestResponse($pattern, $results)
    {
        $r = array();
        $lev = 10;
        foreach ($results as $result) {
            $inputs = explode(';', $result->getInput());
            foreach ($inputs as $input) {
                $l = levenshtein($pattern, $input);
                if ($l < $lev) $lev = $l;
            }
            $r[$lev] = array('lev' => $lev, 'pattern' => $pattern, 'input' => $input, 'response' => $result->getResponse());
        }

        if ($lev > 4) {
            return array();
        }

        asort($r);
        return array_shift($r);
    }
}

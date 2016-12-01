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
        if(!$text) $text = $request->get('text'); /************* test ************/
        $conversations = $entityManager->getRepository('KarismatikChatBotBundle:Conversation')->getResponseByInput($text);
        $session = $request->getSession();

        if ($fastResult = $session->get('fast_result')) {
            $session->remove('fast_result');
            if (in_array($text, array('Oui', 'oui', 'Yes', 'yes'))) {

                return new Response($fastResult['response']);
            }

            if (in_array($text, array('Non', 'non', 'No', 'no'))) {

                return new Response('Ok');
            }
        }

        $session->remove('fast_result');
        $result = $this->getBestResponse($text, $conversations);

        if ($result) {
            if ($result['lev'] < 1 && strlen($text) > 2) {
                return new Response($result['response']);
            } else {
                $session->set('fast_result', $result);
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
        $lev = $minLev = 5;
        foreach ($results as $result) {
            $inputs = explode(';', $result->getInput());
            foreach ($inputs as $input) {
                $lev = levenshtein(strtolower($pattern), strtolower($input));
                $r[$lev] = array('lev' => $lev, 'pattern' => $pattern, 'input' => $input, 'response' => $result->getResponse());
                if ($lev < $minLev) $minLev = $lev;
            }
        }

        if ($minLev > 2) {
            return array();
        }

        asort($r);

        return array_shift($r);
    }
}

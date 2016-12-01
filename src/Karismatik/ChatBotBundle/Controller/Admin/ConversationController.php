<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Karismatik\ChatBotBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Karismatik\ChatBotBundle\Entity\Conversation;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin/conversation")
 * @Security("has_role('ROLE_ADMIN')")
 *
 */
class ConversationController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/", name="admin_index")
     * @Route("/", name="admin_conversation_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $conversations = $entityManager->getRepository('KarismatikChatBotBundle:Conversation')->findAll();

        return $this->render('admin/conversation/index.html.twig', array('conversations' => $conversations));
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="admin_conversation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $conversation = new Conversation();

        $form = $this->createForm('Karismatik\ChatBotBundle\Form\ConversationType', $conversation)
            ->add('saveAndCreateNew', 'Symfony\Component\Form\Extension\Core\Type\SubmitType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conversation);
            $entityManager->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See http://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'conversation.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_conversation_new');
            }

            return $this->redirectToRoute('admin_conversation_index');
        }

        return $this->render('admin/conversation/new.html.twig', array(
            'conversation' => $conversation,
            'form' => $form->createView(),
        ));
    }

    /**
     *
     * @Route("/{id}", requirements={"id": "\d+"}, name="admin_conversation_show")
     * @Method("GET")
     */
    public function showAction(Conversation $conversation)
    {
        $deleteForm = $this->createDeleteForm($conversation);

        return $this->render('admin/conversation/show.html.twig', array(
            'conversation'        => $conversation,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"}, name="admin_conversation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Conversation $conversation, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('Karismatik\ChatBotBundle\Form\ConversationType', $conversation);
        $deleteForm = $this->createDeleteForm($conversation);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'conversation.updated_successfully');

            return $this->redirectToRoute('admin_conversation_edit', array('id' => $conversation->getId()));
        }

        return $this->render('admin/conversation/edit.html.twig', array(
            'conversation'=> $conversation,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/{id}", name="admin_conversation_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Conversation $conversation)
    {
        $form = $this->createDeleteForm($conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($conversation);
            $entityManager->flush();

            $this->addFlash('success', 'conversation.deleted_successfully');
        }

        return $this->redirectToRoute('admin_conversation_index');
    }

    /**
     * @param Conversation $conversation
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm(Conversation $conversation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_conversation_delete', array('id' => $conversation->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}

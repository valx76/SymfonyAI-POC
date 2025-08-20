<?php

namespace App\Controller;

use App\AI\Chat;
use App\Entity\User;
use App\Form\AppFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    public function __construct(
        private readonly Chat $chat,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/', name: 'app_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $currentUserId = $request->getSession()->get('userId');
        $currentUser = null !== $currentUserId
            ? $this->userRepository->find($currentUserId)
            : null;

        $form = $this->createForm(AppFormType::class, [
            'user' => $currentUser,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->get('user')->getData();

            /** @var string $message */
            $message = $form->get('message')->getData();

            if ($currentUserId !== $user->getId()) {
                $this->chat->reset();
            }

            $request->getSession()->set('userId', $user->getId());

            $this->chat->submitMessage($message);

            return $this->redirectToRoute('app_index');
        }

        $messages = $this->chat->loadMessages()->withoutSystemMessage()->getMessages();

        return $this->render('app/index.html.twig', [
            'messages' => $messages,
            'form' => $form,
        ]);
    }

    #[Route('/clear', name: 'app_clear', methods: ['GET'])]
    public function clear(): Response
    {
        $this->chat->reset();

        return $this->redirectToRoute('app_index');
    }
}

<?php

namespace AMREU\NikBundle\Controller;

use AMREU\NikBundle\Service\NikService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/nik')]
class NikController extends AbstractController
{
   public function __construct(
      private NikService $nikService,
      private string $successUri,
      private int $timeout = 5000,
   )
   {
   }

   #[Route(path: '/invitation', name: 'amreu_nik_post_invitation')]
   public function postinvitation(Request $request): Response
   {
      $invitationRequest = $this->nikService->invitation();
      $session = $request->getSession();
      $session->set('NikInvitation', $invitationRequest);
      return $this->render('@Nik/invitation.html.twig', [
         'invitationRequest' => $invitationRequest,
         'invitation' => null,
         'timeout' => $this->timeout,
      ]);
   }

   #[Route(path: '/invitation/{inviId}', name: 'amreu_nik_get_invitation')]
   public function getInvitation(Request $request, string|null $inviId = null): Response
   {
      $invitationRequest = $request->getSession()->get('NikInvitation');

      if ( null === $inviId && null === $invitationRequest ) {
         return $this->redirectToRoute('amreu_nik_post_invitation');
      } else {
         $invitation = $this->nikService->getInvitation($inviId);
         if ($invitation !== null && $invitation['status'] === 1 ) {
            $payload = $invitation['payload'];
            $connUUID = $payload['connUUID'];
            $inviId = $payload['invID'];
            $petId = $payload['petID'];
            $loginStatus = $this->nikService->login($connUUID, $petId, 'Amorebonos', 'Amorebonoak', $request->getLocale());
            $session = $request->getSession();
            $session->set('NikLoginRequest', $loginStatus);
            return $this->redirectToRoute('amreu_nik_get_thread', [
               'threadId' => $loginStatus['threadID']
            ]);
         }
      }

      return $this->render('@Nik/invitation.html.twig', [
         'invitationRequest' => $invitationRequest,
         'invitation' => $invitation,
         'timeout' => $this->timeout,
      ]);
   }

   #[Route(path: '/login', name: 'amreu_nik_internal_login', methods:['POST', 'GET'])]
   public function login(Request $request): Response
   {
      $payload = $request->query->get('payload');
      $petId = $request->query->get('petID');
      $locale = $request->query->get('locale');
      $request->getSession()->set("_locale", $locale);
      $connection = $this->nikService->checkConnReq($payload, $request->getLocale());
      if ( null !== $connection ) {
         $connUUID = $connection['connUUID'];
         $loginStatus = $this->nikService->login($connUUID, $petId, 'Amorebonos', 'Amorebonoak', $locale);
         $session = $request->getSession();
         $session->set('NikLoginRequest', $loginStatus);
         return $this->redirectToRoute('amreu_nik_get_thread', [
            'threadId' => $loginStatus['threadID'],
            '_locale' => $locale,
         ]);
      }
      throw new Exception('Could no login');
   }

   #[Route(path: '/thread/{threadId}', name: 'amreu_nik_get_thread')]
   public function getThread(Request $request, string|null $threadId = null): Response
   {
      $taxId = null;
      $threadStatus = $this->nikService->getThread($threadId);
      if (null === $threadStatus) {
         $this->addFlash('error', 'message.problemCallingNikApp');
         $request->getSession()->invalidate();
      }
      if ($threadStatus !== null && $threadStatus['status'] !== 0 ) {
         if ($threadStatus['status'] === 1 && isset($threadStatus['payload']) && isset($threadStatus['payload']['answer']) ) {
            $taxId = $threadStatus['payload']['answer']['taxID'];
            $request->getSession()->set('nikUser',$taxId);
            return $this->redirectToRoute($this->successUri);
         }
      }

      return $this->render('@Nik/login.html.twig', [
         'threadId' => $threadId,
         'threadStatus' => $threadStatus,
         'taxId' => $taxId,
         'timeout' => $this->timeout,
      ]);
   }


    #[Route(path: '/nik/success', name: 'amreu_nik_success')]
    public function success(Request $request): Response
    {
      $nikUser = $request->getSession()->get("nikUser");
      return $this->json($nikUser);
    }

    #[Route(path: '/logout', name: 'amreu_nik_logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->invalidate();
        return $this->json('logout');
    }
}

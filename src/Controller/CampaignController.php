<?php

namespace App\Controller;

use App\DTO\AmorebonoUser;
use App\DTO\BonosDisponiblesResponse;
use App\DTO\BuyBonosResponse;
use App\DTO\ReimprimirBonosResponse;
use App\Entity\Sale;
use App\Form\BuyBonoType;
use App\Service\AmorebonoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Mailer\MailerInterface;

final class CampaignController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private readonly AmorebonoService $amorebonoService,
        private readonly MailerInterface $mailer,
        private readonly string $mailerFrom,
        )
    {
    }

    /* ============================================================
     * MÉTODOS AUXILIARES REUTILIZABLES
     * ============================================================ */

    private function ensureUserOrRedirect(Request $request): ?Response
    {
        if (null === $this->getAmorebonoUser($request)) {
            return $this->redirectToRoute('app_auth_selector');
        }
        return null;
    }

    private function getAmorebonoUser(Request $request): ?AmorebonoUser
    {
        $session = $request->getSession();
        $giltza = $session->get("giltzaUser");
        $nik = $session->get("nikUser");

        return match (true) {
            $giltza !== null => new AmorebonoUser($giltza['dni'],$giltza['given_name'],$giltza['surname1'],$giltza['surname2']),
            $nik !== null => new AmorebonoUser($nik),
            default => null
        };
    }

    private function loadCommonData(Request $request): array
    {
        $user = $this->getAmorebonoUser($request);
        $NikInternalLogin = $request->getSession()->get("NikInternalLogin", false);
        $campaign = $this->amorebonoService->info();

        $disponibles = BonosDisponiblesResponse::createFromArray(
            $this->amorebonoService->getBonosUsuarioDisponibles($user->getDni())
        );

        return [
            'user' => $user,
            'campaign' => $campaign,
            'disponibles' => $disponibles,
            'hasBought' => $campaign->hasBought($disponibles),
            'NikInternalLogin' => $NikInternalLogin,
        ];
    }

    private function renderBuy(array $data, $form)
    {
        return $this->render('campaign/buy.html.twig', [
            'nombresBonos' => $data['campaign']->getNombresBonos($form->getConfig()->getOption('translation_domain')),
            'disponibles' => $data['disponibles'],
            'canBuy' => $data['disponibles']->canBuy(),
            'amorebonoUser' => $data['user'],
            'campaign' => $data['campaign'],
            'hasBought' => $data['hasBought'],
            'NikInternalLogin' => $data['NikInternalLogin'],
            'form' => $form->createView(),
        ]);
    }

    private function pdfResponse(string $pdf, bool $inline = false): Response
    {
        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf(
                '%s; filename="amorebonoak.pdf"',
                $inline ? 'inline' : 'attachment'
            ),
            'Content-Length' => strlen($pdf),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function sendTemplatedEmail(string $subject, array $recipients, string $template, array $context, array $attachments = []): void
    {
        $email = (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to(...$recipients)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);
        foreach ($attachments as $attachmentName => $attachmentBody) {
            $email->attach($attachmentBody, $attachmentName, 'application/pdf');
        }
        $this->mailer->send($email);
    }

    private function addErrorMessage(BuyBonosResponse|ReimprimirBonosResponse $boughtBonos, array $nombresBonos) {
        $error = $boughtBonos->getError();
        $msg = $boughtBonos->getMsg();

        switch($error) {
            case AmorebonoService::ERROR_CAMPAIGN_MAX_REACHED:
                if ( $boughtBonos->getQuedanTipoBono1() == 0 ) {
                    $tipoBono = $nombresBonos[1];
                } else {
                    $tipoBono = $nombresBonos[2];;
                }
                $this->addFlash('error', new TranslatableMessage($msg,[
                    '%tipoBono%' => $tipoBono,
                ]));                
                break;

        }
        return;
    }

     /* ============================================================
     * ACCIONES
     * ============================================================ */

    #[Route('/{_locale}/', name: 'app_campaign_index', requirements: ['_locale' => 'es|eu'], defaults: ['_locale' => 'eu'], priority: 0)]
    public function index(Request $request): Response
    {
        // If no user redirect login page
        if ($redirect = $this->ensureUserOrRedirect($request)) {
            return $redirect;
        }

        $request->getSession()->set("_locale", $request->getLocale());
        $data = $this->loadCommonData($request);
        if (!$data['NikInternalLogin']) {
            return $this->redirectToRoute('app_campaign_buy');
        }

        return $this->render('campaign/index.html.twig', [
            'canBuy' => $data['disponibles']->canBuy(),
            'hasBought' => $data['hasBought'],
            'NikInternalLogin' => $data['NikInternalLogin'],
        ]);
    }

    #[Route('/{_locale}/buy', name: 'app_campaign_buy', requirements: ['_locale' => 'es|eu'], defaults: ['_locale' => 'eu'], priority: 0)]
    public function buy(Request $request): Response
    {
        // If no user redirect login page
        if ($redirect = $this->ensureUserOrRedirect($request)) {
            return $redirect;
        }
        $request->getSession()->set("_locale", $request->getLocale());
        $data = $this->loadCommonData($request);
        $user = $data['user'];
        $nombresBonos = $data['campaign']->getNombresBonos($request->getLocale());
        $form = $this->createForm(BuyBonoType::class, null, [
            'restantes_tipo1' => $data['disponibles']->getRestantesTipo1(),
            'restantes_tipo2' => $data['disponibles']->getRestantesTipo2(),
        ]);
        // If is a GET request form is not submitted and it's not valid. So render buy page
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->renderBuy($data, $form);
        }

        // On POST request a submitted and valid forms. 
        $formData = $form->getData();
        // Validaciones
        if ($formData['cantidad_bonos1'] === 0 && $formData['cantidad_bonos2'] === 0) {
            $this->addFlash('error', 'message.noBonosSelected');
            return $this->renderBuy($data, $form);
        }

        if ($formData['cantidad_bonos1'] > $data['disponibles']->getRestantesTipo1() ||
            $formData['cantidad_bonos2'] > $data['disponibles']->getRestantesTipo2()
        ) {
            $this->addFlash('error', new TranslatableMessage('message.tooMuchBonoSelected', [
                '%nombreTipo1%' => $nombresBonos[0],
                '%nombreTipo2%' => $nombresBonos[1],
                '%restantesTipo1%' => $data['disponibles']->getRestantesTipo1(),
                '%restantesTipo2%' => $data['disponibles']->getRestantesTipo2(),
                '%solicitadosTipo1%' => $formData['cantidad_bonos1'],
                '%solicitadosTipo2%' => $formData['cantidad_bonos2'],
            ]));
            return $this->renderBuy($data, $form);
        }

        // Ejecutar compra
        $response = $this->amorebonoService->buy($user->getDni(), $formData);
        $bought = BuyBonosResponse::createFromArray($response);

        if (!$bought->isOk()) {
            $this->addErrorMessage($bought, $nombresBonos);
            return $this->redirectToRoute('app_campaign_buy');
        }

        foreach ($bought->getBonos() as $bono) {
            $sale = (new Sale())
                ->setDni($user->getDni())
                ->setFecha(new \DateTime());

            $sale->fillBono($bono);

            $this->em->persist($sale);
        }

        $this->em->flush();
        $this->addFlash('success', 'message.bonosAdquiredSuccessfully');

        $pdf = $bought->getBinaryPdf();

        if ($formData['email']) {
            $this->sendTemplatedEmail(
                '[Amorebono] Zure Amorebonuak | Sus Amorebonos',
                [$formData['email']],
                'campaign/buyEmail.html.twig',
                ['bonos' => $bought],
                ['amorebonoak.pdf' => $pdf]
            );
            return $this->redirectToRoute('app_campaign_buy');
        }

        return $this->pdfResponse($pdf, false);
    }

    #[Route('/{_locale}/reprint-bonos', name: 'app_campaign_reprint_bonos', requirements: ['_locale' => 'es|eu'], defaults: ['_locale' => 'eu'], priority: 0)]
    public function reprintBonos(Request $request): Response {
        // If no user redirect login page
        if ($redirect = $this->ensureUserOrRedirect($request)) {
            return $redirect;
        }
        $request->getSession()->set("_locale", $request->getLocale());
        $data = $this->loadCommonData($request);
        $byMail = (bool) $request->query->get('byMail', false);
        $form = $this->createForm(BuyBonoType::class);
        $form->handleRequest($request);
        // Show on page on GET requests.
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->renderBuy($data, $form);
        }

        // On POST requests
        $formData = $form->getData();
        if ($byMail && empty($formData['email'])) {
            $this->addFlash('error', 'message.emailNeeded');
            return $this->redirectToRoute('app_campaign_buy');
        }
        $response = $this->amorebonoService->reprintBonos($data['user']->getDni());
        $bonos = ReimprimirBonosResponse::createFromArray($response);
        if (!$bonos->isOk()) {
            $this->addFlash('error', 'message.errorGettingBonos');
            return $this->redirectToRoute('app_campaign_buy');
        }
        $pdf = $bonos->getBinaryPdf();
        if (!$byMail) {
            return $this->pdfResponse($pdf, true);
        }
        $this->sendTemplatedEmail(
            '[Amorebono] Zure Amorebonuak | Sus Amorebonos',
            [$formData['email']],
            'campaign/buyEmail.html.twig',
            ['bonos' => $bonos],
            ['amorebonoak.pdf' => $pdf]
        );
        $this->addFlash('success', 'message.successfullySent');
        return $this->redirectToRoute('app_campaign_buy');
    }

    #[Route('/{_locale}/my-amorebonos', name: 'app_campaign_my_bonos', requirements: ['_locale' => 'es|eu'], defaults: ['_locale' => 'eu'], priority: 0)]
    public function myBonos(Request $request) {
        // If no user redirect login page
        if ($redirect = $this->ensureUserOrRedirect($request)) {
            return $redirect;
        }
        $request->getSession()->set("_locale", $request->getLocale());
        $data = $this->loadCommonData($request);
        $response = $this->amorebonoService->reprintBonos($data['user']->getDni());
        $bonos = ReimprimirBonosResponse::createFromArray($response);
        if (!$bonos->isOk()) {
            $this->addFlash('error', 'message.errorGettingBonos');
        }

        return $this->render('campaign/show.html.twig', [
            'myBonos' => $bonos,
            'campaign' => $data['campaign'],
            'amorebonoUser' => $data['user'],
            'NikInternalLogin' => $data['NikInternalLogin'],
        ]);
    }
}

<?php

namespace App\Controller;

use App\DTO\Bono;
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

    #[Route('/{_locale}/', name: 'app_campaign_index', requirements: ['_locale' => 'es|eu'], defaults: ['_locale' => 'eu'], priority: 0)]
    public function index(Request $request): Response
    {
        $request->getSession()->set("_locale", $request->getLocale());
        $giltzaUser = $request->getSession()->get("giltzaUser");
        if (!$giltzaUser) {
            return $this->redirectToRoute('amreu_giltza_login');
        }
        $campaign = $this->amorebonoService->info();
        $nombresBonos = $campaign->getNombresBonos($request->getLocale());

        if ( $campaign->getError() === AmorebonoService::ERROR_FETCHING_CAMPAIGN_1 || $campaign->getError() === AmorebonoService::ERROR_FETCHING_CAMPAIGN_2 ) {
            $this->addFlash('warning','message.noActiveCampaigns');
        }
        $disponiblesArray = $this->amorebonoService->getBonosUsuarioDisponibles($giltzaUser['dni']);
        $disponibles = BonosDisponiblesResponse::createFromArray($disponiblesArray);
        $hasBought = $campaign->hasBought($disponibles);
        $form = $this->createForm(BuyBonoType::class, null, [
            'restantes_tipo1' => $disponibles->getRestantesTipo1(),
            'restantes_tipo2' => $disponibles->getRestantesTipo2(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ( $data['cantidad_bonos1'] === 0 && $data['cantidad_bonos2'] === 0 ) {
                $this->addFlash('error','message.noBonosSelected');
                return $this->render('campaign/index.html.twig', [
                    // 'disponibles' => $disponibles,
                    'nombresBonos' => $nombresBonos,
                    'disponibles' => $disponibles,
                    'giltzaUser' => $giltzaUser,
                    'campaign' => $campaign,
                    'hasBought' => $hasBought,
                    'form' => $form->createView(),  
                ]);
            }
            if ( $data['cantidad_bonos1'] > $disponibles->getRestantesTipo1() or $data['cantidad_bonos2'] > $disponibles->getRestantesTipo2() ) {

                $this->addFlash('error', new TranslatableMessage('message.tooMuchBonoSelected',[
                    '%nombreTipo1%' => $nombresBonos[0],
                    '%nombreTipo2%' => $nombresBonos[1],
                    '%restantesTipo1%' => $disponibles->getRestantesTipo1(),
                    '%restantesTipo2%' => $disponibles->getRestantesTipo2(),
                    '%solicitadosTipo1%' => $data['cantidad_bonos1'],
                    '%solicitadosTipo2%' => $data['cantidad_bonos2'],
                ]));
                return $this->render('campaign/index.html.twig', [
                    'nombresBonos' => $nombresBonos,
                    'disponibles' => $disponibles,
                    'giltzaUser' => $giltzaUser,
                    'campaign' => $campaign,
                    'hasBought' => $hasBought,
                    'form' => $form->createView(),  
                ]);
            }

            $response = $this->amorebonoService->buy($giltzaUser['dni'], $data);
            $boughtBonos = BuyBonosResponse::createFromArray($response);
            if ($boughtBonos->isOk()) {
                /**
                 * @var Bono[] $bonos
                 */
                $bonos = $boughtBonos->getBonos();
                foreach ($bonos as $bono) {
                    $sale = new Sale();
                    $sale->setDni($giltzaUser['dni']);
                    $sale->fillBono($bono);
                    $sale->setFecha(new \DateTime());
                    $this->em->persist($sale);
                }
                $this->em->flush();
                $this->addFlash('success', 'message.bonosAdquiredSuccessfully');
                if ($data['email'] !== null) {
                    $pdf = $boughtBonos->getBinaryPdf();
                    $this->sendTemplatedEmail('[Amorebono] Zure Bonuak | Sus Bonos', [$data['email']], 'campaign/buyEmail.html.twig', [
                        'bonos' => $boughtBonos,
                    ],[
                        'bonos.pdf' => $pdf
                    ]);
                } else {
                    $pdf = $boughtBonos->getBinaryPdf();
                    return new Response($pdf, Response::HTTP_OK, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="amorebonoak.pdf"',
                        'Content-Length'      => strlen($pdf),
                        'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                        'Pragma'              => 'no-cache',
                    ]);
                }
            } else {
                $this->addErrorMessage($boughtBonos, $campaign->getNombresBonos());
            }
            return $this->redirectToRoute('app_campaign_index');
        }
        return $this->render('campaign/index.html.twig', [
            'nombresBonos' => $nombresBonos,
            'disponibles' => $disponibles,
            'giltzaUser' => $giltzaUser,
            'campaign' => $campaign,
            'hasBought' => $hasBought,
            'form' => $form->createView(),  
        ]);
    }

    #[Route('/{_locale}/reprint-bonos', name: 'app_campaign_reprint_bonos', requirements: ['_locale' => 'es|eu'], defaults: ['_locale' => 'eu'], priority: 0)]
    public function reprintBonos(Request $request): Response {
        $request->getSession()->set("_locale", $request->getLocale());
        $giltzaUser = $request->getSession()->get("giltzaUser");
        if (!$giltzaUser) {
            return $this->redirectToRoute('amreu_giltza_login');
        }
        $byMail = boolval($request->query->get('byMail',false));
        $disponiblesArray = $this->amorebonoService->getBonosUsuarioDisponibles($giltzaUser['dni']);
        $disponibles = BonosDisponiblesResponse::createFromArray($disponiblesArray);
        $form = $this->createForm(BuyBonoType::class, null, [
            'restantes_tipo1' => $disponibles->getRestantesTipo1(),
            'restantes_tipo2' => $disponibles->getRestantesTipo2(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $myBonosResponse = $this->amorebonoService->reprintBonos($giltzaUser['dni']);
            $myBonos = ReimprimirBonosResponse::createFromArray($myBonosResponse);
            if ( $byMail && ( $data['email'] === null || empty($data['email']) ) ) {
                $this->addFlash('error','message.emailNeeded');
                return $this->redirectToRoute('app_campaign_index');
            }
            if ( $myBonos->isOk() ) {
                $pdf = $myBonos->getBinaryPdf();
                if ( !$byMail ) {
                    return new Response($pdf, Response::HTTP_OK, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="amorebonoak.pdf"',
                        'Content-Length'      => strlen($pdf),
                        'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                        'Pragma'              => 'no-cache',
                    ]);
                } else {
                    $this->sendTemplatedEmail('[Amorebono] Sus Bonos | Zure Bonuak', [$data['email']], 'campaign/buyEmail.html.twig', [
                        'bonos' => $myBonos,
                    ],[
                        'amorebonoak.pdf' => $pdf
                    ]);
                    return $this->redirectToRoute('app_campaign_index');
                }
            } else {
                $campaign = $this->amorebonoService->info();
                $this->addErrorMessage($myBonos, $campaign->getNombresBonos());
                return $this->redirectToRoute('app_campaign_index');
            }
        }
        $campaign = $this->amorebonoService->info();
        $nombresBonos = $campaign->getNombresBonos($request->getLocale());
        $hasBought = $campaign->hasBought($disponibles);        
        return $this->render('campaign/index.html.twig', [
            'nombresBonos' => $nombresBonos,
            'disponibles' => $disponibles,
            'giltzaUser' => $giltzaUser,
            'campaign' => $campaign,
            'hasBought' => $hasBought,
            'form' => $form->createView(),  
        ]);
    }

    /**
     * Método genérico para enviar emails con plantilla
     */
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
}

<?php

namespace App\Controller;

use App\Entity\Bonus;
use App\Entity\Person;
use App\Entity\Selling;
use App\Form\SellingSearchFormType;
use App\Form\SellingType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SellingController extends AbstractController
{
    /**
     * @Route("/{_locale}/selling/new", name="selling_new")
     */
    public function new(Request $request, TranslatorInterface $translator, UserInterface $user = null)
    {
        $form = $this->createForm(SellingType::class, new Selling(), [
            'readonly' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /* @var $data Selling */
            $data = $form->getData();
            /* @var $person Person */
            $person = $em->getRepository(Person::class)->findOneBy(['NAN' => $data->getPerson()->getNAN()]);
            if (null !== $person) {
                $data->setPerson($person);
            } else {
                $person = $data->getPerson();
            }
            /* @var $bonus Bonus */
            $bonus = $em->getRepository(Bonus::class)->findOneBy(['id' => $data->getBonus()->getId()]);
            if (!$person->canBuy($bonus, $data->getQuantity())) {
                $erosiak = $person->getErositakoBonuKopurua($bonus);
                $gehienezkoa = $bonus->getPertsonakoGehienezkoKopurua();
                $this->addFlash('error',
                    $translator->trans('selling.tooMuchBonusPerPerson', [
                        '{erosiak}' => $erosiak,
                        '{gehienezkoa}' => $gehienezkoa,
                    ])
                );

                return $this->render('selling/new.html.twig', [
                    'form' => $form->createView(),
                    'readonly' => false,
                    'new' => true,
                ]);
            }

            if ($bonus->getRemaining() < $data->getQuantity()) {
                $this->addFlash('error', 'selling.notEnoughBonusesRemaining');

                return $this->render('selling/new.html.twig', [
                    'form' => $form->createView(),
                    'readonly' => false,
                    'new' => true,
                ]);
            }
            $data->setSellingDate(new DateTime());
            $data->setTotalPrice($data->getBonus()->getPrice() * $data->getQuantity());
            $data->setUser($user);
            $bonus->setEmandakoak($bonus->getEmandakoak() + $data->getQuantity());
            $em->persist($data);
            $em->persist($bonus);
            $em->flush();

            $this->addFlash('success', 'selling.saved');

            return $this->redirectToRoute('selling_new');
        }

        return $this->render('selling/new.html.twig', [
            'form' => $form->createView(),
            'readonly' => false,
            'new' => true,
        ]);
    }

    /**
     * @Route("/{_locale}/selling/{selling}/delete", options={"expose"=true}, name="selling_delete")
     */
    public function delete(Selling $selling)
    {
        $em = $this->getDoctrine()->getManager();
        $bonus = $selling->getBonus();
        $bonus->setEmandakoak($bonus->getEmandakoak() - $selling->getQuantity());
        $em->remove($selling);
        $em->persist($bonus);
        $em->flush();

        $this->addFlash('success', 'selling.succesfullyRemoved');

        return $this->redirectToRoute('selling_list');
    }

    /**
     * @Route("/{_locale}/selling/{selling}", name="selling_show")
     */
    public function show(Selling $selling)
    {
        $form = $this->createForm(SellingType::class, $selling, [
            'readonly' => true,
        ]);

        return $this->render('selling/edit.html.twig', [
            'form' => $form->createView(),
            'readonly' => true,
            'new' => false,
        ]);
    }

    /**
     * @Route("/{_locale}/selling", name="selling_list")
     */
    public function list(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $criteria = [
            'fromDate' => (new DateTime())->modify('-15 day')->format('Y-m-d'),
            'toDate' => (new DateTime())->format('Y-m-d'),
        ];
        $form = $this->createForm(SellingSearchFormType::class,
            $criteria);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $sellings = $em->getRepository(Selling::class)->findBy($data);

            return $this->render('selling/list.html.twig', [
            'form' => $form->createView(),
            'sellings' => $sellings,
        ]);
        }

        $sellings = $em->getRepository(Selling::class)->findBy($criteria);

        return $this->render('selling/list.html.twig', [
            'form' => $form->createView(),
            'sellings' => $sellings,
        ]);
    }
}

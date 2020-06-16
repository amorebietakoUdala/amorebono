<?php

namespace App\Controller;

use App\Entity\Bonus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/bonus", name="api_bonus")
     */
    public function getBonus(Request $request, SerializerInterface $serializer)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $bonus = $em->getRepository(Bonus::class)->find($id);

        return new JsonResponse($serializer->serialize($bonus, 'json'));
    }

    /**
     * @Route("/person", name="api_person")
     */
    public function getPerson(Request $request, SerializerInterface $serializer)
    {
        $em = $this->getDoctrine()->getManager();
        $nan = $request->get('nan');
        if (null === $nan || empty($nan)) {
            return new JsonResponse($serializer->serialize([], 'json'));
        }

        $persons = $em->getRepository(\App\Entity\Person::class)->findByNAN($nan);

        return new JsonResponse($serializer->serialize($persons, 'json'));
    }
}

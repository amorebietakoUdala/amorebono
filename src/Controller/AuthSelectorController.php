<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthSelectorController extends AbstractController
{
    #[Route('/auth-selector', name: 'app_auth_selector')]
    public function index(): Response
    {
        return $this->render('auth_selector/index.html.twig');
    }
}

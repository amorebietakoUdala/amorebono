<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthSelectorController extends AbstractController
{
    #[Route('/auth-selector', name: 'app_auth_selector')]
    public function index(Request $request): Response
    {
        $request->getSession()->invalidate();
        return $this->render('auth_selector/index.html.twig');
    }
}

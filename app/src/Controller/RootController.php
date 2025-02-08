<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class RootController extends AbstractController
{
    #[Route('/api', name: 'api_status', methods: ['GET'])]
    public function apiStatus(Request $request): JsonResponse
    {
        return new JsonResponse([
            'status' => 'server is running',
            'host' => $request->getHost(),
            'protocol' => $request->getScheme(),
        ]);
    }

    #[Route('/api/ping', name: 'api_ping', methods: ['GET'])]
    public function apiPing(): JsonResponse
    {
        return new JsonResponse(['status' => 'pong']);
    }
}

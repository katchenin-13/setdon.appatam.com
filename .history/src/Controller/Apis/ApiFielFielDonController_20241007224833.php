<?php

namespace App\Controller\Apis;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\FieldonRepository;
use Symfony\Component\DependencyInjection\Attribute\AsController;
use Symfony\Component\DependencyInjection\Attribute\AsController;
#[AsController]
#[Route('/api/fieldon')]
class ApiFielDonController extends AbstractController
{
    private FieldonRepository $fieldonRepository;

    public function __construct(FieldonRepository $fieldonRepository)
    {
        $this->fieldonRepository = $fieldonRepository;
    }

    #[Route('/', name: 'api_fieldon', methods: ['GET'])]
    public function getAll(Request $request): Response
    {
        try {
            $fieldons = $this->fieldonRepository->findAll();
            $tabaFielDon = [];

            foreach ($fieldons as $i => $value) {
                $tabaFielDon[$i] = [
                    'id' => $value->getId(),
                    'dateremise' => $value->getDon()->getDateremise(),
                    'communaute' => $value->getDon()->getCommunaute()->getLibelle(),
                    'recipiandaire' => $value->getDon()->getNom(),
                    'remise' => $value->getDon()->getRemispar(),
                    'nature' => $value->getNaturedon(),
                    'quantite' => $value->getQte(),
                    'motif' => $value->getMotifdon(),
                    'valeur' => $value->getMontantdon(),
                    'typedonsfiel' => $value->getTypedonsfiel(),
                ];
            }

            return $this->json($tabaFielDon);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

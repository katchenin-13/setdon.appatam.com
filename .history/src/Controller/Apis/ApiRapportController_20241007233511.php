<?php

namespace App\Controller\Apis;

use OpenApi\Attributes as OA;
use App\Entity\Missionrapport;
use App\Controller\ApiInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Repository\MissionrapportRepository;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/rapportmission')]
class ApiRapportController extends ApiInterface
{
    #[Route('/', name: 'api_rapportmission', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of Rapport entities',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Missionrapport::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'RapportMission')]
    #[Security(name: 'Bearer')]
    public function getAll(MissionrapportRepository $missionrapportRepository, Request $request): Response
    {
        try {
            $contacts = $missionrapportRepository->findAll();
            $tabaRapport = [];

            foreach ($contacts as $i => $value) {
                $tabaRapport[$i] = [
                    'id' => $value->getId(),
                    'titreMission' => $value->getTitreMission(),
                    'nombrepersonne' => $value->getNombrepersonne(),
                    'objectifs' => $value->getObjectifs(),
                    'action' => $value->getAction(),
                    'opportunite' => $value->getOpportunite(),
                    'prochaineetat' => $value->getProchaineetat(),
                    'communaute' => $value->getCommunaute()->getLibelle(),
                    'difficulte' => $value->getDifficulte(),
                    'etat' => $value->getEtat(),
                    'employe' => $value->getEmploye()->getNom(),
                    'justification'
                ];
            }

            return $this->response($tabaRapport);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response(null);
        }
    }
}

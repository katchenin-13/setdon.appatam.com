<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Rapport;
use App\Repository\MissionrapportRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route('/api/contacts')]
class ApiRapportController extends ApiInterface
{
    #[Route('/', name: 'api_contact', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of Rapport entities',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Rapport::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'Rapport')]
    #[Security(name: 'Bearer')]
    public function getAll(MissionrapportRepository $,issionrapportRepository, Request $request): Response
    {
        try {
            $contacts = $,issionrapportRepository->findAll();
            $tabaRapport = [];

            foreach ($contacts as $i => $value) {
                $tabaRapport[$i] = [
                    'id' => $value->getId(),
                    'nom' => $value->getNom(),
                    'prenom' => $value->getFonction(),
                    'email' => $value->getEmail(),
                    'telephone' => $value->getNumero(),
                    'adresse' => $value->getObservation(),
                    'communaute' => $value->getCommunaute()->getLibelle(),

                ];
            }

            return $this->response($tabaRapport);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response(null);
        }
    }
}

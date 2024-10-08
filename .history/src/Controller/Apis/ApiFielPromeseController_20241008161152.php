<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Fielpromesse;
use App\Repository\FielpromesseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route('/api/fielpromesses')]
class ApiFielPromeseController extends ApiInterface
{
    #[Route('/', name: 'api_promesse', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of FielPromesse entities',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fielpromesse::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'FielPromesse')]
    #[Security(name: 'Bearer')]
    public function getAll(FielpromesseRepository $fielpromesseRepository, Request $request): Response
    {
        try {
            $fielpromesses = $fielpromesseRepository->findAll();
            $tabaFielPromesse = [];

            foreach ($fielpromesses as $i => $value) {
                $tabaFielPromesse[$i] = [
                    'id' => $value->getId(),
                    'dateremise' => $value->getPromesse()->getDate(),
                    'communaute' => $value->getPromesse()->getCommunaute()->getLibelle(),
                    'recipiandaire' => $value->getPromesse()->getNom(),
                    'nature' => $value->getNature(),
                    'quantite' => $value->getQte(),
                    'motif' => $value->getMotif(),
                    'valeur' => $value->getMontant(),
                    'typedonsfiel' => $value->getTypepromesse(),
                    
                ];
            }

            return $this->response($tabaFielPromesse);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response(null);
        }
    }
}

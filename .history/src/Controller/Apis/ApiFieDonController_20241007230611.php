<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Fieldon;
use App\Repository\FieldonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route('/api/fieldon')]
class ApiFielDonController extends ApiInterface
{
    #[Route('/', name: 'api_fieldon', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all Fieldon entities',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fieldon::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'Fieldon')]
    #[Security(name: 'Bearer')]
    public function getAll(FieldonRepository $fieldonRepository, Request $request): Response
    {
        try {
            $fieldons = $fieldonRepository->findAll();
            dd($fieldons);
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

            return $this->response($tabaFielDon);
       
    }
}

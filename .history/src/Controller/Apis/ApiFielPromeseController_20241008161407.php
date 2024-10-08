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
                    'etat' => $value->getEtat(),
                ];
            }

            return $this->response($tabaFielPromesse);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response(null);
        }
    }

    #[Route('/justification_fil/{id}', name: 'api_audience_justification', methods: ['POST'])]
    #[OA\Tag(name: 'fil')]
    #[Security(name: 'Bearer')]

    public function justification(Request $request,FielpromesseRepository $fielpromesseRepository, $id)
    {
        try {
            $data = json_decode($request->getContent());

            $demande = $fielpromesseRepository->find($id);
            if ($demande != null) {

                $demande->setEtat($data->etat);

                // On sauvegarde en base
                $fielpromesseRepository->save($demande, true);
                // On retourne la confirmation

                $response = $this->json([
                    'statut' => 200,
                    'message' => 'Demande mise à jour avec succès',

                ], Response::HTTP_OK);
                dd($this->json([
                    'statut' => 200,
                    'message' => 'Demande mise à jour avec succès',

                ], Response::HTTP_OK));
                return $response;
            } else {
                $response = $this->json([
                    'statut' => 404,
                    'message' => 'Demande non trouvée',

                ]);
                return $response;
            }
        } catch (\Exception $exception) {
            $response = $this->json([
                'statut' => 500,
                'message' => 'Erreur : ' . $exception->getMessage()
            ]);

            return $response;
        }
        return $response;
    }
}

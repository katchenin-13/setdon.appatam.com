<?php

namespace App\Controller\Apis;

use App\Entity\Audience;
use OpenApi\Attributes as OA;
use App\Controller\ApiInterface;
use App\Repository\AudienceRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;


// **************************************************************************
// ApiAudianceController
// Contrôleur pour gérer les opérations sur les audiences via l'API.
// **************************************************************************

#[Route('/api/audience')]
class ApiAudianceController extends ApiInterface
{
    // **************************************************************************
    // getAll
    // Méthode pour récupérer la liste complète des audiences.
    // **************************************************************************

    #[Route('/', name: 'api_audience', methods: ['GET'])]
    #[Route('/audiences', name: 'get_audiences', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Retourne la liste des audiences",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Audience::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'Audience')]
    #[Security(name: 'Bearer')]
    public function getAll(AudienceRepository $audienceRepository, TagAwareCacheInterface $cachePool, Request $request): Response
    {
        try {
            $audiences = $audienceRepository->findAll();
            $tabaAudience = [];
            $i = 0;
            foreach ($audiences as $key => $value) {
                $tabaAudience[$i]['id'] = $value->getId();
                $tabaAudience[$i]['motif'] = $value->getMotif();
                $tabaAudience[$i]['communaute'] = $value->getCommunaute()->getLibelle();
               // $tabaAudience[$i]['nom'] = $value->getNom();
               // $tabaAudience[$i]['lieu_habitation'] =   $value->getLieuHabitation();
                $tabaAudience[$i]['daterencontre'] = $value->getDate();
                $tabaAudience[$i]['numero'] = $value->getNumero();
                $tabaAudience[$i]['etat'] = $value->getEtat();
                $i++;
            }
            $response = $this->response($tabaAudience);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        return $response;
    }

    // **************************************************************************
    // getAllBooks
    // Méthode pour récupérer une liste paginée de "livres" (audiences).
    // **************************************************************************

    #[Route('/book', name: 'api_audience1', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Retourne une liste paginée de livres",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Audience::class, groups: ['getBooks']))
        )
    )]
    #[OA\Tag(name: 'Audience')]
    #[Security(name: 'Bearer')]
    public function getAllBooks(AudienceRepository $bookRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): Response
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllBooks-" . $page . "-" . $limit;
        $bookList = $cachePool->get($idCache, function (ItemInterface $item) use ($bookRepository, $page, $limit) {
            $item->tag("booksCache");
            return $bookRepository->findAllWithPagination($page, $limit);
        });

        $jsonBookList = $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    // **************************************************************************
    // validation
    // Méthode pour valider et mettre à jour une audience spécifique.
    // **************************************************************************

    #[Route('/validation/{id}', name: 'api_audience_validation', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: "Valide et met à jour une audience",
        content: new OA\JsonContent(ref: new Model(type: Audience::class, groups: ['full']))
    )]
    #[OA\Tag(name: 'Audience')]
    #[Security(name: 'Bearer')]
    public function validation(Request $request, Audience $audience, AudienceRepository $audienceRepository, $id): Response
    {
        try {
            $data = json_decode($request->getContent());

            $audience = $audienceRepository->find($id);
            if ($audience != null) {
                $audience->setMotif($data->motif);
                $audience->setCommunaute($data->communaute);
                $audience->setDaterencontre($data->daterencontre);
                $audience->setNomchef($data->nomchef);
                $audience->setNumero($data->numero);
                $audience->setEmail($data->email);

                $audienceRepository->add($audience, true);

                $response = json_encode($this->response($audience));
            } else {
                $this->setMessage("Cette ressource est inexistante");
                $this->setStatusCode(300);
                $response = $this->response(null);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }
        return $response;
    }
}
    #[Route('/justification_demande/{id}', name: 'api_demande_justification', methods: ['POST'])]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]

    public function justification(Request $request, DemandeRepository $demandeRepository ,$id)
    {
        try {
            $data = json_decode($request->getContent());
            
            $demande = $demandeRepository->find($id);
            if ($demande != null) {
            
                $demande->setEtat($data->etat);
                $demande->setJustification($data->justification);
            
                // On sauvegarde en base
                $demandeRepository->save($demande, true);
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
             
            }else{
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

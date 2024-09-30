<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Demande;
use OpenApi\Attributes as OA;
use App\Repository\DemandeRepository;
use App\Repository\ModuleGroupePermitionRepository;
use App\Repository\PromesseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Symfony\Component\String\toString;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;


#[Route('/api/demande')]
class ApiDemandeController extends ApiInterface
{
    #[Route('/', name: 'api_demande', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Returns the rewards of an user",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Demande::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]
    public function getAll(DemandeRepository $demandeRepository, PromesseRepository $promesseRepository, Request $request): Response
    {
        try {
            // Récupération des demandes
            $demandes = $demandeRepository->findAll();

            // Utilisation de array_map pour créer une réponse formatée
            $tabaDemande = array_map(function ($demande) {
                return [
                    'id' => $demande->getId(),
                    'motif' => $demande->getMotif(),
                    'communaute' => $demande->getCommunaute()->getLibelle(),
                    'nom' => $demande->getNom(),
                    'lieu_habitation' => $demande->getLieuHabitation(),
                    'daterencontre' => $demande->getDate(),
                    'numero' => $demande->getNumero(),
                    'etat' => $demande->getEtat()
                ];
            }, $demandes);

            // Réponse formatée
            return $this->response($tabaDemande);
        } catch (\Exception $exception) {
            // En cas d'exception, retour de l'erreur avec un code HTTP approprié
            return $this->response(null, Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }



    #[Route('/validation/{id}', name: 'api_audience_validation', methods: ['POST'])]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]
    public function validation(Request $request, Demande $demande, DemandeRepository $audienceRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

           // $demande = $audienceRepository->find($id);
            if ($demande != null) {

                //$demande->setId($data->id);
                $demande->setMotif($data->motif);
                $demande->setCommunaute($data->communaute);
                $demande->setDaterencontre($data->daterencontre);
                //$demande->setNomchef($data->nomchef);
                $demande->setNumero($data->numero);
               // $demande->setEmail($data->email);

                // On sauvegarde en base
                $audienceRepository->add($demande, true);

                // On retourne la confirmation
                $response = $this->response($demande);
            } else {
                $this->setMessage("cette ressource est inexsitante");
                $this->setStatusCode(300);
                $response = $this->response(null);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }
        return $response;
    }





    #[Route('/create', name: 'api_demande_create', methods: ['POST'])]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]
    public function create(Request $request, DemandeRepository $demandeRepository)
    {
        try {
            $data = json_decode($request->getContent());

            $demande = $demandeRepository->findOneBy(array('code' => $data->code));
            if ($demande == null) {
                $demande = new Demande();
                $demande->setMotif($data->code);
                $demande->setCommunaute($data->libelle);

                // On sauvegarde en base
                $demandeRepository->add($demande, true);

                // On retourne la confirmation
                $response = $this->response($demande);
            } else {
                $this->setMessage("cette ressource existe deja en base");
                $this->setStatusCode(300);
                $response = $this->response(null);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }


        return $response;
    }

    #[Route('/update/{id}', name: 'api_demande_update', methods: ['POST'])]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]
    
    public function update(Request $request, DemandeRepository $demandeRepository, $id)
    {
        try {
            $data = json_decode($request->getContent());
            $demande = $demandeRepository->find($id);
            if ($demande != null) {

                //$demande->setCode($data->code);
                //$demande->setLibelle($data->libelle);

                // On sauvegarde en base
                $demandeRepository->add($demande, true);

                // On retourne la confirmation
                $response = $this->response($demande);
            } else {
                $this->setMessage("cette ressource est inexsitante");
                $this->setStatusCode(300);
                $response = $this->response(null);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }
        return $response;
    }


    #[Route('/delete/{id}', name: 'api_demande_delete', methods: ['POST'])]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]
    public function delete(Request $request, DemandeRepository $demandeRepository, $id)
    {
        try {
            $data = json_decode($request->getContent());

            $demande = $demandeRepository->find($id);
            if ($demande != null) {

                $demandeRepository->remove($demande, true);

                // On retourne la confirmation
                $response = $this->response($demande);
            } else {
                $this->setMessage("cette ressource est inexistante");
                $this->setStatusCode(300);
                $response = $this->response(null);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }
        return $response;
    }

    #[Route('/active/{id}', name: 'api_demande_active', methods: ['GET'])]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]
    public function active(?Demande $demande, DemandeRepository $demandeRepository)
    {
        /*  $demande = $demandeRepository->find($id);*/
        try {
            if ($demande) {

                //$demande->setCode("555"); //TO DO nous ajouter un champs active
                $demandeRepository->add($demande, true);
                $response = $this->response($demande);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response(null);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }


        return $response;
    }


    #[Route('/active/multiple', name: 'api_audience_active_multiple', methods: ['POST'])]
    #[OA\Tag(name: 'Demande')]
    #[Security(name: 'Bearer')]
    public function multipleActive(Request $request, DemandeRepository $demandeRepository)
    {
        try {
            $data = json_decode($request->getContent());

            $listedemandes = $demandeRepository->findAllByListId($data->ids);
            foreach ($listedemandes as $listedemande) {
                //$listeDemande->setCode("555");  //TO DO nous ajouter un champs active
                $demandeRepository->add($listedemande, true);
            }

            $response = $this->response(null);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }
        return $response;
    }
}

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
            $demandes = $demandeRepository->findAll();
            $tabaDemande = [];
            $i = 0;
            foreach ($demandes as $key => $value) {
                $tabaDemande[$i]['id'] = $value->getId();
                $tabaDemande[$i]['motif'] = $value->getMotif();
                $tabaDemande[$i]['communaute'] = $value->getCommunaute()->getLibelle();
                $tabaDemande[$i]['nom'] = $value->getNom();
                $tabaDemande[$i]['lieu_habitation'] =   $value->getLieuHabitation();
                $tabaDemande[$i]['daterencontre'] = $value->getDate();
                $tabaDemande[$i]['numero'] = $value->getNumero();
                $tabaDemande[$i]['etat'] = $value->getEtat();
                $i++;
            }

            //dd($tabaAudience);


            $response = $this->response($tabaDemande);
            //dd($demandes);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }


        return $response;
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
               
$                response =$this->json([
                    'statut' => 200,
                    'message' => 'Demande mise à jour avec succès',
                   
                ], Response::HTTP_OK);
                return $response;
             
            }else{
                
                return 
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }
       return $response;
    }
     
}

<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Demande;

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
use OpenApi\Annotations as OA;

#[Route('/api/demande')]
class ApiDemandeController extends ApiInterface
{
    #[Route('/', name: 'api_demande', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Demande::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Demande")
     * @Security(name="Bearer")
     */
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

        // On envoie la réponse

        return $response;
    }


    #[Route('/validation/{id}', name: 'api_audience_validation', methods: ['POST'])]

    /**
     * Permet de mettre à jour une demande.
     *
     * @OA\Tag(name="Demande")
     * @Security(name="Bearer")
     */
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



    // #[Route('/getOne/{id}', name: 'api_demande_get_one', methods: ['GET'])]
    // /**
    //  * Affiche une civilte en offrant un identifiant.
    //  * @OA\Tag(name="Demande")
    //  * @Security(name="Bearer")
    //  */
    // public function getOne(?Demande $demande)
    // {
    //     /*  $demande = $demandeRepository->find($id);*/
    //     try {
    //         if ($demande) {
    //             $response = $this->response($demande);
    //         } else {
    //             $this->setMessage('Cette ressource est inexistante');
    //             $this->setStatusCode(300);
    //             $response = $this->response($demande);
    //         }
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }


    //     return $response;
    // }


    #[Route('/create', name: 'api_demande_create', methods: ['POST'])]
    /**
     * Permet de créer une demande.
     *
     * @OA\Tag(name="Demande")
     * @Security(name="Bearer")
     */
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
    /**
     * Permet de mettre à jour une demande.
     *
     * @OA\Tag(name="Demande")
     * @Security(name="Bearer")
     */
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
    /**
     * permet de supprimer une demande en offrant un identifiant.
     *
     * @OA\Tag(name="Demande")
     * @Security(name="Bearer")
     */
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
    /**
     * Permet d'activer une demande en offrant un identifiant.
     * @OA\Tag(name="Demande")
     * @Security(name="Bearer")
     */
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


    #[Route('/active/multiple', name: 'api_audeince_active_multiple', methods: ['POST'])]
    /**
     * Permet de faire une desactivation multiple.
     *
     * @OA\Tag(name="Demande")
     * @Security(name="Bearer")
     */
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

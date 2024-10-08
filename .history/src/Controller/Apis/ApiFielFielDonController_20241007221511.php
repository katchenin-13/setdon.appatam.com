<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Fieldon;
use App\Entity\Promesse;
use App\Repository\FieldonRepository;
use App\Repository\ModuleGroupePermitionRepository;
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

#[Route('/api/fielpromesse')]
class ApiFielPromeseController extends ApiInterface
{
    #[Route('/', name: 'api_promesse', methods: ['GET'])]
    /**
     * Affiche toutes les FielPromesses.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=FielPromesse::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Fieldon")
     * @Security(name="Bearer")
     */
    public function getAll(FieldonRepository $fielpromesseRepository, Request $request): Response
    {
        //dd($this->response($promesseRepository->findAll()));
        try {
            //dd($groupePermitionRepository->getNombreDemandeParMois());
            $total_items = $fielpromesseRepository->countItems();
            $fielpromesses = $fielpromesseRepository->findAll();

            $tabaFielPromesse = [];
            $item = [];
            $i = 0;
            foreach ($fielpromesses as $key => $value) {

                $item[$i]['id'] = $value->getId();
                $item[$i]['dateremise'] = $value->getPromesse()->getDate();
                $item[$i]['communaute'] = $value->getPromesse()->getCommunaute()->getLibelle();
                $item[$i]['recipiandaire'] = $value->getPromesse()->getNom();
                $item[$i]['nature'] = $value->getNature();
                $item[$i]['quantite'] = $value->getQte();
                $item[$i]['motif'] = $value->getMotif();
                $item[$i]['valeur'] = $value->getMontant();

                $item[$i]['typedonsfiel'] = $value->getTypepromesse();

                $i++;
            }
            $tabaFielPromesse = [
                'total_count' => $total_items,
                'items' => $item,

            ];
            $response = $this->response($tabaFielPromesse);
            //dd($promesses);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse

        return $response;
    }

    #[Route('/validation/{id}', name: 'api_fielpromese_validation', methods: ['GET'])]

    /**
     * Permet de mettre à jour une fielpromesse.
     *
     * @OA\Tag(name="Fieldon")
     * @Security(name="Bearer")
     */
    public function validation(Request $request, Fieldon $fielpromesse, FieldonRepository $fielpromesseRepository, $id): Response
    {
        try {
            $data = json_decode($request->getContent());

            $fielpromesse = $fielpromesseRepository->find($id);

            if ($fielpromesse != null) {

                // $fielpromesse->setId($data->id);

                //  $fielpromesse->setPromesse($data->dateremise);
                //  $fielpromesse->setNature();
                //  $fielpromesse->setQte();
                //  $fielpromesse->setMotif();
                //  $fielpromesse->setMontant();
                //  $fielpromesse->setMontant();
                //  $fielpromesse->setTypepromesse();
                // On sauvegarde en base
                $fielpromesseRepository->add($fielpromesse, true);

                // On retourne la confirmation
                $response = $this->response($fielpromesse);
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


    // #[Route('/getOne/{id}', name: 'api_promesse_get_one', methods: ['GET'])]
    // /**
    //  * Affiche une civilte en offrant un identifiant.
    //  * @OA\Tag(name="Promesse")
    //  * @Security(name="Bearer")
    //  */
    // public function getOne(?Promesse $Promesse)
    // {
    //     /*  $Promesse = $promesseRepository->find($id);*/
    //     try {
    //         if ($Promesse) {
    //             $response = $this->response($Promesse);
    //         } else {
    //             $this->setMessage('Cette ressource est inexistante');
    //             $this->setStatusCode(300);
    //             $response = $this->response($Promesse);
    //         }
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }


    //     return $response;
    // }


    // #[Route('/create', name: 'api_promesse_create', methods: ['POST'])]
    // /**
    //  * Permet de créer une Promesse.
    //  *
    //  * @OA\Tag(name="Promesse")
    //  * @Security(name="Bearer")
    //  */
    // public function create(Request $request, FieldonRepository $FieldonRepository)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $fielpromesse = $FieldonRepository->findOneBy(array('code' => $data->code));
    //         if ($fielpromesse== null) {
    //             $fielpromesse = new Promesse();
    //             //$fielpromesse->setMotif($data->code);
    //             //$fielpromesse->setCommunaute($data->libelle);

    //             // On sauvegarde en base
    //             $FieldonRepository->add($fielpromesse, true);

    //             // On retourne la confirmation
    //             $response = $this->response($fielpromesse);
    //         } else {
    //             $this->setMessage("cette ressource existe deja en base");
    //             $this->setStatusCode(300);
    //             $response = $this->response(null);
    //         }
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }


    //     return $response;
    // }


    // #[Route('/update/{id}', name: 'api_promesse_update', methods: ['POST'])]
    // /**
    //  * Permet de mettre à jour une Promesse.
    //  *
    //  * @OA\Tag(name="Promesse")
    //  * @Security(name="Bearer")
    //  */
    // public function update(Request $request, FieldonRepository $fielpromesseRepository, $id)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $fielpromesse = $fielpromesseRepository->find($id);
    //         if ($fielpromesse != null) {

    //             //$fielpromesse->setCode($data->code);
    //            // $fielpromesse->setLibelle($data->libelle);

    //             // On sauvegarde en base
    //             $fielpromesseRepository->add($fielpromesse, true);

    //             // On retourne la confirmation
    //             $response = $this->response($fielpromesse);
    //         } else {
    //             $this->setMessage("cette ressource est inexsitante");
    //             $this->setStatusCode(300);
    //             $response = $this->response(null);
    //         }
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }
    //     return $response;
    // }


    // #[Route('/delete/{id}', name: 'api_promesse_delete', methods: ['POST'])]
    // /**
    //  * permet de supprimer une Promesse en offrant un identifiant.
    //  *
    //  * @OA\Tag(name="Promesse")
    //  * @Security(name="Bearer")
    //  */
    // public function delete(Request $request, FieldonRepository  $fielpromesseRepository, $id)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $Promesse = $fielpromesseRepository->find($id);
    //         if ($Promesse != null) {

    //             $fielpromesseRepository->remove($Promesse, true);

    //             // On retourne la confirmation
    //             $response = $this->response($Promesse);
    //         } else {
    //             $this->setMessage("cette ressource est inexistante");
    //             $this->setStatusCode(300);
    //             $response = $this->response(null);
    //         }
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }
    //     return $response;
    // }


    // #[Route('/active/{id}', name: 'api_promesse_active', methods: ['GET'])]
    // /**
    //  * Permet d'activer une Promesse en offrant un identifiant.
    //  * @OA\Tag(name="Promesse")
    //  * @Security(name="Bearer")
    //  */
    // public function active(?Promesse $Promesse, FieldonRepository $fielpromesseRepository)
    // {
    //     /*  $Promesse = $fielpromesseRepository->find($id);*/
    //     try {
    //         if ($Promesse) {

    //             //$Promesse->setCode("555"); //TO DO nous ajouter un champs active
    //             $fielpromesseRepository->add($Promesse, true);
    //             $response = $this->response($Promesse);
    //         } else {
    //             $this->setMessage('Cette ressource est inexistante');
    //             $this->setStatusCode(300);
    //             $response = $this->response(null);
    //         }
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }


    //     return $response;
    // }


    // #[Route('/active/multiple', name: 'api_audeince_active_multiple', methods: ['POST'])]
    // /**
    //  * Permet de faire une desactivation multiple.
    //  *
    //  * @OA\Tag(name="Promesse")
    //  * @Security(name="Bearer")
    //  */
    // public function multipleActive(Request $request, FieldonRepository $fielpromesseRepository)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $listepromesses = $fielpromesseRepository->findAllByListId($data->ids);
    //         foreach ($listepromesses as $listepromesse) {
    //             //$listePromesse->setCode("555");  //TO DO nous ajouter un champs active
    //             $fielpromesseRepository->add($listepromesse, true);
    //         }

    //         $response = $this->response(null);
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }
    //     return $response;
    // }
}

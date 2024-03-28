<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Localite;
use App\Repository\LocaliteRepository;
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

#[Route('/api/localite')]
class ApiLocaliteController extends ApiInterface
{
    #[Route('/', name: 'api_localite', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Localite::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Localite")
     * @Security(name="Bearer")
     */
    public function getAll(LocaliteRepository $localiteRepository): Response
    {
        try {
            //dd($groupePermitionRepository->getNombreDemandeParMois());
            $localites = $localiteRepository->findAll();
            // dd($localites);
            $response = $this->response($localites);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/getOne/{id}', name: 'api_localite_get_one', methods: ['GET'])]
    /**
     * Affiche une civilte en offrant un identifiant.
     * @OA\Tag(name="Localite")
     * @Security(name="Bearer")
     */
    public function getOne(?Localite $localite)
    {
        /*  $localite = $localiteRepository->find($id);*/
        try {
            if ($localite) {
                $response = $this->response($localite);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($localite);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }


        return $response;
    }


    #[Route('/create', name: 'api_localite_create', methods: ['POST'])]
    /**
     * Permet de créer une localite.
     *
     * @OA\Tag(name="Localite")
     * @Security(name="Bearer")
     */
    public function create(Request $request, LocaliteRepository $localiteRepository)
    {
        try {
            $data = json_decode($request->getContent());

            $localite = $localiteRepository->findOneBy(array('code' => $data->code));
            if ($localite == null) {
                $localite = new Localite();
                $localite->setCode($data->code);
                $localite->setLibelle($data->libelle);

                // On sauvegarde en base
                $localiteRepository->add($localite, true);

                // On retourne la confirmation
                $response = $this->response($localite);
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


    #[Route('/update/{id}', name: 'api_localite_update', methods: ['POST'])]
    /**
     * Permet de mettre à jour une localite.
     *
     * @OA\Tag(name="Localite")
     * @Security(name="Bearer")
     */
    public function update(Request $request, LocaliteRepository $localiteRepository, $id)
    {
        try {
            $data = json_decode($request->getContent());

            $localite = $localiteRepository->find($id);
            if ($localite != null) {

                $localite->setCode($data->code);
                $localite->setLibelle($data->libelle);

                // On sauvegarde en base
                $localiteRepository->add($localite, true);

                // On retourne la confirmation
                $response = $this->response($localite);
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


    #[Route('/delete/{id}', name: 'api_localite_delete', methods: ['POST'])]
    /**
     * permet de supprimer une localite en offrant un identifiant.
     *
     * @OA\Tag(name="Localite")
     * @Security(name="Bearer")
     */
    public function delete(Request $request, LocaliteRepository $localiteRepository, $id)
    {
        try {
            $data = json_decode($request->getContent());

            $localite = $localiteRepository->find($id);
            if ($localite != null) {

                $localiteRepository->remove($localite, true);

                // On retourne la confirmation
                $response = $this->response($localite);
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


    #[Route('/active/{id}', name: 'api_localite_active', methods: ['GET'])]
    /**
     * Permet d'activer une localite en offrant un identifiant.
     * @OA\Tag(name="Localite")
     * @Security(name="Bearer")
     */
    public function active(?Localite $localite, LocaliteRepository $localiteRepository)
    {
        /*  $localite = $localiteRepository->find($id);*/
        try {
            if ($localite) {

                //$localite->setCode("555"); //TO DO nous ajouter un champs active
                $localiteRepository->add($localite, true);
                $response = $this->response($localite);
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


    #[Route('/active/multiple', name: 'api_localite_active_multiple', methods: ['POST'])]
    /**
     * Permet de faire une desactivation multiple.
     *
     * @OA\Tag(name="Localite")
     * @Security(name="Bearer")
     */
    public function multipleActive(Request $request, LocaliteRepository $localiteRepository)
    {
        try {
            $data = json_decode($request->getContent());

            $listeLocalites = $localiteRepository->findAllByListId($data->ids);
            foreach ($listeLocalites as $listeLocalite) {
                //$listeLocalite->setCode("555");  //TO DO nous ajouter un champs active
                $localiteRepository->add($listeLocalite, true);
            }

            $response = $this->response(null);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }
        return $response;
    }
}

<?php

namespace App\Controller\Apis;

use App\Entity\Audience;
use OpenApi\Annotations as OA;

use App\Controller\ApiInterface;
use Psr\Cache\CacheItemInterface;
use Psr\SimpleCache\CacheInterface;
use App\Repository\AudienceRepository;
use App\Repository\PromesseRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\toString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\ModuleGroupePermitionRepository;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/api/audience')]
class ApiAudianceController extends ApiInterface
{
    #[Route('/', name: 'api_audience', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Audience::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Audience")
     * @Security(name="Bearer")
     */
    public function getAll(AudienceRepository $audienceRepository, TagAwareCacheInterface $cachePool,  Request $request): Response
    {
        //dd($this->response($promesseRepository->findAll()));
        try {
            $total_items = $audienceRepository->countItems();
            // $all = $request->query->all();
            // $page = $all['page'] ?? [];
            // $limit = $all['limit'] ?? [];
           
//$idCache = "getAllBooks-" . $page . "-" . $limit;
           
            // $audiences = $cachePool->get((), function (ItemInterface $item) use ($audienceRepository, $page, $limit) {
            //     $item->tag("booksCache");
            //     return $audienceRepository->findAllWithPagination($page, $limit);
            // });

            // $pagination = $paginator->paginate(
            //     $repository->findAll(), // Replace with your query or repository method
            //     $request->query->getInt('page', 1), // Page number
            //     10 // Items per page
            // );
            // $page = $request->query->getInt('page', 1); // Get page number from the request
            // $limit = 10; // Items per page

            // $idCache = 'audiences_page_' . $page;

            // $audiences = $cache->get($idCache, function (CacheItemInterface $item) use ($page, $limit) {
            //     $item->expiresAfter(3600); // Cache for 1 hour
            //     $item->tag("audiencesCache"); // Tag for cache invalidation

            //     return $audienceRepository->findAllWithPagination($page, $limit);
            // });
           // dd("soro");
            $audiences = $audienceRepository->findAll();
            // $tabaAudience = [];
            // $item = [];
            // $i = 0;
            // foreach ($audiences as $key => $value) {
            //     $item[$i]['id'] = $value->getId();
            //     $item[$i]['motif'] = $value->getMotif();
            //     $item[$i]['communaute'] = $value->getCommunaute()->getLibelle();
            //     $item[$i]['daterencontre'] = $value->getDate();
            //     $item[$i]['nomchef'] = $value->getNomchef();
            //     $item[$i]['numero'] = $value->getNumero();
            //     $item[$i]['email'] = $value->getEmail();
            //     $item[$i]['nombreparticipant'] = $value->getNombreparticipant();
            //     $item[$i]['etat'] = $value->getEtat();

            //     //$item[$i]['date'] = someAction($item[$i]['daterencontre'], $format = 'Y-m-d H:i:s');
            //     $i++;
            // }

            // $tabaAudience = [
            //     'total_count' => $total_items,
            //     'items' => $item,

            // ];
            //dd($tabaAudience);

            $response = $this->responseNew($audiences, "group1");
            //dd($audiences);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse

        return $response;
    }
    #[Route('/book', name: 'api_audience1', methods: ['GET'])]


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

    #[Route('/validation/{id}', name: 'api_audience_validation', methods: ['POST'])]

    /**
     * Permet de mettre à jour une audience.
     *
     * @OA\Tag(name="Audience")
     * @Security(name="Bearer")
     */
    public function validation(Request $request, Audience $audience, AudienceRepository $audienceRepository, $id): Response
    {
        try {
            $data = json_decode($request->getContent());

            $audience = $audienceRepository->find($id);
            if ($audience != null) {

                //$audience->setId($data->id);
                $audience->setMotif($data->motif);
                $audience->setCommunaute($data->communaute);
                $audience->setDaterencontre($data->daterencontre);
                $audience->setNomchef($data->nomchef);
                $audience->setNumero($data->numero);
                $audience->setEmail($data->email);

                // On sauvegarde en base
                $audienceRepository->add($audience, true);

                // On retourne la confirmation
                $response = json_encode($this->response($audience));
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



    // #[Route('/getOne/{id}', name: 'api_audience_get_one', methods: ['GET'])]
    // /**
    //  * Affiche une civilte en offrant un identifiant.
    //  * @OA\Tag(name="Audience")
    //  * @Security(name="Bearer")
    //  */
    // public function getOne(?Audience $audience)
    // {
    //     /*  $audience = $audienceRepository->find($id);*/
    //     try {
    //         if ($audience) {
    //             $response = $this->response($audience);
    //         } else {
    //             $this->setMessage('Cette ressource est inexistante');
    //             $this->setStatusCode(300);
    //             $response = $this->response($audience);
    //         }
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }


    //     return $response;
    // }


    // #[Route('/create', name: 'api_audience_create', methods: ['POST'])]
    // /**
    //  * Permet de créer une audience.
    //  *
    //  * @OA\Tag(name="Audience")
    //  * @Security(name="Bearer")
    //  */
    // public function create(Request $request, AudienceRepository $audienceRepository)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $audience = $audienceRepository->findOneBy(array('code' => $data->code));
    //         if ($audience == null) {
    //             $audience = new Audience();
    //             //$audience->setId($data->id);
    //             $audience->setMotif($data->motif);
    //             $audience->setCommunaute($data->communaute);
    //             $audience->setDaterencontre($data->daterencontre);
    //             $audience->setNomchef($data->nomchef);
    //             $audience->setNumero($data->numero);
    //             $audience->setEmail($data->email);


    //             // On sauvegarde en base
    //             $audienceRepository->add($audience, true);

    //             // On retourne la confirmation
    //             $response = $this->response($audience);
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


    // #[Route('/update/{id}', name: 'api_audience_update', methods: ['POST'])]
    // /**
    //  * Permet de mettre à jour une audience.
    //  *
    //  * @OA\Tag(name="Audience")
    //  * @Security(name="Bearer")
    //  */
    // public function update(Request $request, AudienceRepository $audienceRepository, $id)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $audience = $audienceRepository->find($id);
    //         if ($audience != null) {

    //             $audience->set($data->motif);
    //             $audience->setLibelle($data->libelle);

    //             // On sauvegarde en base
    //             $audienceRepository->add($audience, true);

    //             // On retourne la confirmation
    //             $response = $this->response($audience);
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


    // #[Route('/delete/{id}', name: 'api_audience_delete', methods: ['POST'])]
    // /**
    //  * permet de supprimer une audience en offrant un identifiant.
    //  *
    //  * @OA\Tag(name="Audience")
    //  * @Security(name="Bearer")
    //  */
    // public function delete(Request $request, AudienceRepository $audienceRepository, $id)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $audience = $audienceRepository->find($id);
    //         if ($audience != null) {

    //             $audienceRepository->remove($audience, true);

    //             // On retourne la confirmation
    //             $response = $this->response($audience);
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


    // #[Route('/active/{id}', name: 'api_audience_active', methods: ['GET', 'POST'])]
    // /**
    //  * Permet d'activer une audience en offrant un identifiant.
    //  * @OA\Tag(name="Audience")
    //  * @Security(name="Bearer")
    //  */
    // public function active(?Audience $audience, AudienceRepository $audienceRepository)
    // {
    //     /*  $audience = $audienceRepository->find($id);*/
    //     $etat = $audience->getEtat();
    //     //  dd($etat);
    //     try {
    //         if ($audience) {

    //             //$audience->setCode("555"); //TO DO nous ajouter un champs active

    //             $audienceRepository->add($audience, true);
    //             $response = $this->response($audience);
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
    //  * @OA\Tag(name="Audience")
    //  * @Security(name="Bearer")
    //  */
    // public function multipleActive(Request $request, AudienceRepository $audienceRepository)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $listeaudiences = $audienceRepository->findAllByListId($data->ids);
    //         foreach ($listeaudiences as $listeaudience) {
    //             //$listeAudience->setCode("555");  //TO DO nous ajouter un champs active
    //             $audienceRepository->add($listeaudience, true);
    //         }

    //         $response = $this->response(null);
    //     } catch (\Exception $exception) {
    //         $this->setMessage($exception->getMessage());
    //         $response = $this->response(null);
    //     }
    //     return $response;
    // }
}

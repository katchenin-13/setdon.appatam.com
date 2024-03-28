<?php

namespace App\Controller\Apis;

use App\Entity\Audience;
use OpenApi\Annotations as OA;

use App\Controller\ApiInterface;
use App\Repository\AudienceRepository;
use App\Repository\LocaliteRepository;
use App\Repository\PromesseRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommunauteRepository;
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

#[Route('/api/audience/statis')]
class ApiStatisAudienceController extends ApiInterface
{
    #[Route('/annee', name: 'api_audience_annee', methods: ['GET'])]
    /**
     * Affiche les statistique des audiences par annee et par communaute
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Audience::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Audience Statistique")
     * @Security(name="Bearer")
     */
    public function datadashbordAudience(AudienceRepository $audienceRepository, Request $request): Response
    {
        //dd($this->response($promesseRepository->findAll()));
        try {
            $all = $request->query->all();
            $communaute =$all['communaute'] ?? [];
            $date = $all['date'] ?? [];
          
           
            $data = $audienceRepository->getAudienceEtCommunauteTableauAudience($date, $communaute);
           // dd($data);
            $dataTotalValider = [];
            $dataTotalRejeter = [];
            $dataTotalInitie = [];
            $dataCompte = [];

            foreach ($data as  $cam) {
                if ($cam['etat'] == "audience_rejeter")
                $dataTotalRejeter[] = $cam['_total'];
                if ($cam['etat'] == "audience_valider")
                $dataTotalValider[] = $cam['_total'];
                if ($cam['etat'] == "audience_initie")
                $dataTotalInitie[] = $cam['_total'];
            }
            // if ($dataTotalRejeter == null)
            //     $dataTotalRejeter['rejeter'] = 0;
            // $dataTotalRejeter['rejeter'] = 0;
            // if ($dataTotalValider == null)
            //     $dataTotalValider['valider'] = 0;
            // if ($dataTotalInitie == null)
            //     $dataTotalInitie['initie'] = 0;


            // $dataTotalEtat = [
            //     $dataTotalInitie['initie'],
            //     $dataTotalRejeter['rejeter'],
            //     $dataTotalValider['valider']
            // ];

            $dataCompte = [
                'audienceInitie' => $dataTotalInitie,
                'audienceValider' => $dataTotalValider,
                'audienceRejeter' => $dataTotalRejeter,
            ];

            // dd($dataCompte);

            $datas = [];
            foreach ($data as $skey => $_row) {
                if ($skey == 0) {

                    $name = '';

                    if ($_row['etat'] == "audience_valider")
                    $name = "Audiences validées";
                    if ($_row['etat'] == "audience_rejeter")
                    $name = "Audiences réjettées";

                    if ($_row['etat'] == "audience_initie")
                    $name = "Audiences initiées";
                    $datas[] = [
                        'name' => $name,
                        'total' => $_row['_total'],
                        'sliced' => true,
                        'selected' => true,

                    ];
                } else {
                    $name = '';

                    if (
                        $_row['etat'] == "audience_valider"
                    )
                        $name = "Audiences validées";
                    if ($_row['etat'] == "audience_rejeter")
                    $name = "Audiences réjettées";
                    if (
                        $_row['etat'] == "audience_initie"
                    )
                        $name = "Audiences initiées";
                    $datas[] = [
                        'name' => $name,
                        'total' => $_row['_total']
                    ];
                }
            }
            $series = [

                "name" => "Audience",
               // "colorByPoint" => true,
                "data" => $datas,

            ];
            //dd($series);
            $response = $this->response($series);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
        
        // return $this->json([
        //     'data' => $series,
        //    'compteAudience' => $dataCompte
        // ]);
    }


    #[Route('/communaute', name: 'api_audience_communaute', methods: ['GET'])]
    /**
     *Affiche les statistique des audiences par mois et par communaute 
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Audience::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Audience Statistique")
     * @Security(name="Bearer")
     */
   //#[Route('/gestion/statistique/audiences/data_communaute', name: 'app_gestion_statistique_audience_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunauteAudience(Request $request, AudienceRepository $audienceRepository)
    {
      

        try {
            $all = $request->query->all();
            $communaute = $all['communaute'] ?? [];
            $date = $all['date'] ?? [];
            $data = $audienceRepository->getAudienceParMoisEtCommunaute($date, $communaute);
            // dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['etat'] == "audience_rejeter") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['etat'] == "audience_valider") {
                    $dataValider[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
            }
            $restv = [];
            $restr = [];

            foreach ($dataValider as $key => $value) {

                $restv[] = $value['total'];
            }

            foreach ($dataRejeter as $key => $value) {
                $restr[] = $value['total'];
            }

            foreach ($data as $key => $value) {
                $mois[] = $value['mois'];
            }

            $series = [
                [
                    "name" => 'Accordées',
                    "data" => $restv,
                    'stack' => 'Audience'
                ],
                [
                    "name" => 'Réfusées',
                    "data" => $restr,
                    'stack' => 'Audience'
                ]
            ];
            $datas = array_map($series,$mois);
           // dd($series);
            $response = $this->response($datas);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/categorie', name: 'api_audience_categorie', methods: ['GET'])]
    /**
     *Affiche les statistique des audiences par mois et par categorie .
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Audience::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Audience Statistique")
     * @Security(name="Bearer")
     */
   // #[Route('/gestion/statistique/audiences/data_categorie', name: 'app_gestion_statistique_audience_categorie_data', condition: "request.query.has('filters')")]
    public function dataCategorieAudiences(Request $request, AudienceRepository $audienceRepository)
    {     
      
        try {
            $all = $request->query->all();
            $categorie = $all['categorie'] ?? [];
            $date = $all['date'] ?? [];
            $data = $audienceRepository->getAudienceParMoisEtCategorie($date, $categorie);
            // dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['etat'] == "audience_rejeter") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['etat'] == "audience_valider") {
                    $dataValider[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
            }
            $restv = [];
            $restr = [];

            foreach ($dataValider as $key => $value) {

                $restv[] = $value['total'];
            }

            foreach ($dataRejeter as $key => $value) {
                $restr[] = $value['total'];
            }

            foreach ($data as $key => $value) {
                $mois[] = $value['mois'];
            }

            $series = [
                [
                    "name" => 'Accordées',
                    "data" => $restv,
                    'stack' => 'Audience'
                ],
                [
                    "name" => 'Réfusées',
                    "data" => $restr,
                    'stack' => 'Audience'
                ]
            ];
            $datas = array_merge($series,$mois);
            // dd($series);
            $response = $this->response($datas);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/localite', name: 'api_audience_localite', methods: ['GET'])]
    /**
     * Affiche les statistique des audiences par mois et par localite 
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Audience::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Audience Statistique")
     * @Security(name="Bearer")
     */
    //#[Route('/gestion/statistique/audiences/data_localite', name: 'app_gestion_statistique_audience_localite_data', condition: "request.query.has('filters')")]
    public function dataLocaliteAudience(Request $request, AudienceRepository $audienceRepository)
    {
       
       

        try {
            $all = $request->query->all();
            $localite = $all['localite'] ?? [];
            $date = $all['date'] ?? [];
            //dd($date, $localite);
            // $filters = $all['filters'] ?? [];
            // $localite = $filters['localite'];
            // $date = $filters['date'];
            //$fin = $filters['fin'];
            //dd($date,$localite);
            $data = $audienceRepository->getAudienceParMoisEtLocalite($date, $localite);
            // dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['etat'] == "audience_rejeter") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['etat'] == "audience_valider") {
                    $dataValider[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
            }
            $restv = [];
            $restr = [];

            foreach ($dataValider as $key => $value) {

                $restv[] = $value['total'];
            }

            foreach ($dataRejeter as $key => $value) {
                $restr[] = $value['total'];
            }

            foreach ($data as $key => $value) {
                $mois[] = $value['mois'];
            }

            $series = [
                [
                    "name" => 'Accordées',
                    "data" => $restv,
                    'stack' => 'Audience'
                ],
                [
                    "name" => 'Réfusées',
                    "data" => $restr,
                    'stack' => 'Audience'
                ]
            ];
            $datas = array_merge($series, $mois);
            // dd($series);
            $response = $this->response($datas);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }
}
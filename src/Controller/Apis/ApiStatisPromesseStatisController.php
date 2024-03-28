<?php

namespace App\Controller\Apis;

use App\Entity\Fielpromesse;
use OpenApi\Annotations as OA;

use App\Controller\ApiInterface;

use App\Repository\LocaliteRepository;
use App\Repository\PromesseRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommunauteRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Repository\FielpromesseRepository;
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

#[Route('/api/fielpromesse/statis')]
class ApiStatisPromesseStatisController extends ApiInterface
{
    #[Route('/annee', name: 'api_fielpromesse_annee', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fielpromesse::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Promesse Statistique")
     * @Security(name="Bearer")
     */
    public function datadashbordPromesse(Request $request, FielpromesseRepository $fieldonRepository, CommunauteRepository $communauteRepository)
    {


        try {
            $all = $request->query->all();

            $communaute = $all['communaute'] ?? [];
            $date = $all['date'] ?? [];
            //$fin = $filters['fin'];
            //dd($date,$communaute);getFielpromesseEtCommunauteTableauFielpromesse
            $data = $fieldonRepository->getPromesseParMoisCommunauteTableauPromesse($date, $communaute);
            //dd($data);
            $dataTotalValider = [];
            $dataTotalRejeter = [];
            $dataTotalInitie = [];
            $dataCompte = [];

            foreach ($data as  $cam) {
                if ($cam['typepromesse'] == "en_nature")
                    $dataTotalRejeter[] = $cam['_total'];
                if ($cam['typepromesse'] == "en_espece")
                    $dataTotalValider[] = $cam['_total'];
            }

            $dataCompte = [
                'fieldonRejeter' => $dataTotalRejeter,
                'fieldonValider' => $dataTotalValider,
            ];

            // dd($dataCompte);

            $datas = [];
            foreach ($data as $skey => $_row) {
                if ($skey == 0) {
                    $name = '';
                    if ($_row['typepromesse'] == "en_nature")
                        $name = "Promesse en natures";
                    if ($_row['typepromesse'] == "en_espece")
                        $name = "Promesse en espèces";
                    $datas[] = [
                        'name' => $name,
                        'total' => $_row['_total'],
                        'sliced' => true,
                        'selected' => true,

                    ];
                } else {
                    $name = '';

                    if ($_row['typepromesse'] == "en_nature")
                        $name = "Promesse en natures";
                    if ($_row['typepromesse'] == "en_espece")
                        $name = "Promesse en espèces";
                    $datas[] = [
                        'name' => $name,
                        'total' => $_row['_total']
                    ];
                }
            }
            $series = [

                "name" => "Promesse",
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
    }


    #[Route('/communaute', name: 'api_fielpromesse_communaute', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fielpromesse::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Promesse Statistique")
     * @Security(name="Bearer")
     */
    //#[Route('/gestion/statistique/fieldons/data_communaute', name: 'app_gestion_statistique_fieldon_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunautePromesse(Request $request, FielpromesseRepository $fieldonRepository)
    {

        try {
            $all = $request->query->all();
            $communaute = $all['communaute'] ?? [];
            $date = $all['date'] ?? [];

            $data = $fieldonRepository->getPromesseParMoisEtCommunaute($date, $communaute);
            //dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['typepromesse'] == "en_espece") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['typepromesse'] == "en_nature") {
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
                    "name" => 'En Nature',
                    "data" => $restv,
                    'stack' => 'fielpromesse'
                ],
                [
                    "name" => 'En espèce',
                    "data" => $restr,
                    'stack' => 'fielpromesse'
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

    #[Route('/categorie', name: 'api_fielpromesse_categorie', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fielpromesse::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Promesse Statistique")
     * @Security(name="Bearer")
     */
    // #[Route('/gestion/statistique/fieldons/data_categorie', name: 'app_gestion_statistique_fieldon_categorie_data', condition: "request.query.has('filters')")]

    public function dataCategoriePromesse(Request $request, FielpromesseRepository $fielpromesseRepository, CategorieRepository $categorieRepository)
    {




        try {
            $all = $request->query->all();
            //  dd($all);
            //$filters = $all['filters'] ?? [];
            $categorie = $all['categorie'];
            $date = $all['date'];
            //$fin = $filters['fin'];
            //dd($date,$categorie);
            $data = $fielpromesseRepository->getPromesseParMoisEtCategorie($date, $categorie);
            //dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['etat'] == "promesse_rejeter") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['etat'] == "promesse_valider") {
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
                    "name" => 'En Nature',
                    "data" => $restv,
                    'stack' => 'fieldon'
                ],
                [
                    "name" => 'En espèce',
                    "data" => $restr,
                    'stack' => 'fieldon'
                ]
            ];
            $datas = array_merge($series, $mois);
            //dd($series);
            $response = $this->response($datas);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/localite', name: 'api_fielpromesse_localite', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fielpromesse::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Promesse Statistique")
     * @Security(name="Bearer")
     */
    //#[Route('/gestion/statistique/fieldons/data_localite', name: 'app_gestion_statistique_fieldon_localite_data', condition: "request.query.has('filters')")]
    public function dataLocalitePromesse(Request $request, FielpromesseRepository $fielpromesseRepository)
    {

        try {
            // $all = $request->query->all();
            // $localite = $all['localite'] ?? [];
            // $date = $all['date'] ?? [];
            // $data = $fielpromesseRepository->getPromesseParMoisEtLocalite($date, $localite);
            //  dd($data);
            // $mois = [];
            // $dataValider = [];
            // $dataRejeter = [];
            // foreach ($data as $cam) {

            //     if ($cam['etat'] == "promesse_rejeter") {
            //         $dataRejeter[] = [
            //             'mois' => $cam['mois'],
            //             'total' => $cam['_total']
            //         ];
            //     }
            //     if ($cam['etat'] == "promesse_valider") {
            //         $dataValider[] = [
            //             'mois' => $cam['mois'],
            //             'total' => $cam['_total']
            //         ];
            //     }
            // }
            // $restv = [];
            // $restr = [];

            // foreach ($dataValider as $key => $value) {

            //     $restv[] = $value['total'];
            // }

            // foreach ($dataRejeter as $key => $value) {
            //     $restr[] = $value['total'];
            // }

            // foreach ($data as $key => $value) {
            //     $mois[] = $value['mois'];
            // }

            // $series = [
            //     [
            //         "name" => 'En Nature',
            //         "data" => $restv,
            //         'stack' => 'fielpromesse'
            //     ],
            //     [
            //         "name" => 'En espèce',
            //         "data" => $restr,
            //         'stack' => 'fielpromesse'
            //     ]
            // ];

            $all = $request->query->all();

            $localite = $all['localite'];
            $date = $all['date'];
            //$fin = $filters['fin'];
            //dd($date,$localite);
            $data = $fielpromesseRepository->getPromesseParMoisEtLocalite($date, $localite);
            //dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['etat'] == "promesse_rejeter") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['etat'] == "promesse_valider") {
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
                    "name" => 'En Nature',
                    "data" => $restv,
                    'stack' => 'fieldon'
                ],
                [
                    "name" => 'En espèce',
                    "data" => $restr,
                    'stack' => 'fieldon'
                ]
            ];
            $datas = array_merge($series, $mois);
            //dd($series);
            $response = $this->response($datas);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }
}

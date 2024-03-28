<?php

namespace App\Controller\Apis;

use App\Entity\Fieldon;
use OpenApi\Annotations as OA;

use App\Controller\ApiInterface;

use App\Repository\FieldonRepository;
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

#[Route('/api/fieldon/statis')]
class ApiStatisDonStatisController extends ApiInterface
{
    #[Route('/annee', name: 'api_fieldon_annee', methods: ['GET'])]
    /**
     * Affiche les statistique des dons par annee et par communaute 
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fieldon::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Don Statistique")
     * @Security(name="Bearer")
     */
    public function datadashbordDon(Request $request, FieldonRepository $fieldonRepository)
    {
      

        try {
            $all = $request->query->all();

            $communaute = $all['communaute']?? [];
            $date = $all['date'] ?? [];
            $data = $fieldonRepository->getDonParMoisCommunauteTableauDon($date, $communaute);
            $dataTotalValider = [];
            $dataTotalRejeter = [];
            $dataTotalInitie = [];
            $dataCompte = [];

            foreach ($data as  $cam) {
                if ($cam['typedonsfiel'] == "en_nature")
                    $dataTotalRejeter[] = $cam['_total'];
                if ($cam['typedonsfiel'] == "en_espece")
                    $dataTotalValider[] = $cam['_total'];
            }

            $dataCompte = [
                'fieldonRejeter' => $dataTotalRejeter,
                'fieldonValider' => $dataTotalValider,
            ];

            $datas = [];
            foreach ($data as $skey => $_row) {
                if ($skey == 0) {
                    $name = '';
                    if ($_row['typedonsfiel'] == "en_nature")
                        $name = "Dons en natures";
                    if ($_row['typedonsfiel'] == "en_espece")
                        $name = "Dons en espèces";
                    $datas[] = [
                        'name' => $name,
                        'y' => $_row['_total'],
                        'sliced' => true,
                        'selected' => true,

                    ];
                } else {
                    $name = '';

                    if ($_row['typedonsfiel'] == "en_nature")
                        $name = "Dons en natures";
                    if ($_row['typedonsfiel'] == "en_espece")
                        $name = "Dons en espèces";
                    $datas[] = [
                        'name' => $name,
                        'y' => $_row['_total']
                    ];
                }
            }
            $series = [

                "name" => "Dons",
                //"colorByPoint" => true,
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


    #[Route('/communaute', name: 'api_fieldon_communaute', methods: ['GET'])]
    /**
     * Affiche les statistique des don par mois et par communaute 
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fieldon::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Don Statistique")
     * @Security(name="Bearer")
     */
    //#[Route('/gestion/statistique/fieldons/data_communaute', name: 'app_gestion_statistique_fieldon_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunauteDon(Request $request, FieldonRepository $fieldonRepository)
    {
     
        try {
            $all = $request->query->all();
            $communaute = $all['communaute'] ?? [];
            $date = $all['date'] ?? [];
            $data = $fieldonRepository->getDonParMoisEtCommunaute($date, $communaute);
            //dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['typedonsfiel'] == "en_espece") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['typedonsfiel'] == "en_nature") {
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
            // dd($series);
            $response = $this->response($datas);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/categorie', name: 'api_fieldon_categorie', methods: ['GET'])]
    /**
     *Affiche les statistique des don par mois et par categorie
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fieldon::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Don Statistique")
     * @Security(name="Bearer")
     */
    // #[Route('/gestion/statistique/fieldons/data_categorie', name: 'app_gestion_statistique_fieldon_categorie_data', condition: "request.query.has('filters')")]
    public function dataCategorieDon(Request $request, FieldonRepository $fieldonRepository)
    {
        try {
            $all = $request->query->all();
            $categorie = $all['categorie'] ?? [];
            $date = $all['date'] ?? [];

            $data = $fieldonRepository->getDonParMoisEtCategorie($date, $categorie);
            // dd($data);
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
            // dd($series);
            $response = $this->response($datas);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/localite', name: 'api_fieldon_localite', methods: ['GET'])]
    /**
     * Affiche les statistique des don par mois et par localite
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fieldon::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Don Statistique")
     * @Security(name="Bearer")
     */
    //#[Route('/gestion/statistique/fieldons/data_localite', name: 'app_gestion_statistique_fieldon_localite_data', condition: "request.query.has('filters')")]
    public function dataLocaliteDon(Request $request, FieldonRepository $fieldonRepository)
    {
        try {
            $all = $request->query->all();

            $localite = $all['localite'] ?? [];
            $date = $all['date'] ?? [];

            $data = $fieldonRepository->getDonParMoisEtLocalite($date, $localite);
            // dd($data);
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
<?php

namespace App\Controller\Apis;

use App\Entity\Demande;
use OpenApi\Annotations as OA;

use App\Controller\ApiInterface;
use App\Repository\DemandeRepository;
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

#[Route('/api/demande/statis')]
class ApiStatisDemandeController extends ApiInterface
{
    #[Route('/annee', name: 'api_demande_annee', methods: ['GET'])]
    /**
     * Affiche les statistique des demandes par annee.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Demande::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Demande statistique")
     * @Security(name="Bearer")
     */


    public function datadashbordDemande(Request $request, DemandeRepository $demandeRepository, CommunauteRepository $communauteRepository)
    {

        //dd($series);
        try {
            $all = $request->query->all();
            $communaute = $all['communaute'] ?? [];
            $date = $all['date'] ?? [];


            $data = $demandeRepository->getdemandeEtCommunauteTableaudemande($date, $communaute);
            // dd($data);
            $dataTotalValider = [];
            $dataTotalRejeter = [];
            $dataTotalInitie = [];
            $dataCompte = [];

            foreach ($data as  $cam) {
                if ($cam['etat'] == "demande_rejeter")
                $dataTotalRejeter[] = $cam['_total'];
                if ($cam['etat'] == "demande_valider")
                $dataTotalValider[] = $cam['_total'];
                if ($cam['etat'] == "demande_initie")
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
                'demandeInitie' => $dataTotalInitie,
                'demandeValider' => $dataTotalValider,
                'demandeRejeter' => $dataTotalRejeter,
            ];

            // dd($dataCompte);

            $datas = [];
            foreach ($data as $skey => $_row) {
                if ($skey == 0) {

                    $name = '';

                    if ($_row['etat'] == "demande_valider"
                    )
                    $name = "demandes validées";
                    if ($_row['etat'] == "demande_rejeter")
                    $name = "demandes réjettées";

                    if ($_row['etat'] == "demande_initie"
                    )
                    $name = "demandes initiées";
                    $datas[] = [
                        'name' => $name,
                        'total' => $_row['_total'],
                        'sliced' => true,
                        'selected' => true,

                    ];
                } else {
                    $name = '';

                    if (
                        $_row['etat'] == "demande_valider"
                    )
                        $name = "demandes validées";
                    if ($_row['etat'] == "demande_rejeter")
                    $name = "demandes réjettées";
                    if (
                        $_row['etat'] == "demande_initie"
                    )
                        $name = "demandes initiées";
                    $datas[] = [
                        'name' => $name,
                        'total' => $_row['_total']
                    ];
                }
            }
            $series = [

                "name" => "Demande",
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


    

    #[Route('/localite', name: 'api_demande_localite', methods: ['GET'])]
    /**
     *Affiche les statistique des demandes par mois et par lacalite.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Demande::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Demande statistique")
     * @Security(name="Bearer")
     */
    //#[Route('/gestion/statistique/demandes/data_localite', name: 'app_gestion_statistique_demande_localite_data', condition: "request.query.has('filters')")]
    public function dataLocaliteDemande(Request $request, DemandeRepository $demandeRepository, LocaliteRepository $localiteRepository)
    {
        try {
            $all = $request->query->all();
            $localite = $request->get('localite', 1);
            $date = $request->get('date', 3);
            //dd($date, $localite);
            // $filters = $all['filters'] ?? [];
            // $localite = $filters['localite'];
            // $date = $filters['date'];
            //$fin = $filters['fin'];
            //dd($date,$localite);
            $data = $demandeRepository->getDemandeParMoisEtLocalite($date, $localite);
            // dd($data);
            $mois = [];
            $dataValider = [];
            $dataRejeter = [];
            foreach ($data as $cam) {

                if ($cam['etat'] == "demande_rejeter") {
                    $dataRejeter[] = [
                        'mois' => $cam['mois'],
                        'total' => $cam['_total']
                    ];
                }
                if ($cam['etat'] == "demande_valider") {
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
                    'stack' => 'Demande'
                ],
                [
                    "name" => 'Réfusées',
                    "data" => $restr,
                    'stack' => 'Demande'
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
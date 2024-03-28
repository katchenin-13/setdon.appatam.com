<?php

namespace App\Controller\Gestion\statistique;
use App\Entity\Demande;
use App\Entity\Audience;
use App\Entity\Localite;
use App\Entity\Categorie;
use App\Entity\Communaute;
use App\Service\ActionRender;
use Doctrine\ORM\QueryBuilder;
use App\Controller\BaseController;
use App\Entity\Missionrapport;
use App\Repository\DemandeRepository;
use App\Repository\LocaliteRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommunauteRepository;
use App\Repository\MissionrapportRepository;
use App\Repository\MissionRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class RapportStatisAvenirController extends BaseController
{
    #[Route('/gestion/statistique/rapports/tableau', name: 'app_gestion_statistique_rapport_tableau')]
    public function indexdashbordMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository)
    {

        $all = $request->query->all();
        $dataDebut = $missionrapportRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
            //dd($resultatDebut);


        ;



        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_rapport_tableau'))
            ->setMethod('POST');
        $formBuilder->add(
            'date',
            ChoiceType::class,
            [
                'placeholder' => '---',
                'label' => 'Année',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2 date'],
                'multiple' => false,
                'choices'  => array_flip($resultatDebut),
            ]
        );
        $formBuilder->add('date_fin', ChoiceType::class, [
            'label' => 'Fin',
            'choices' => array_flip($resultatDebut),
            'attr' => ['class' => 'has-select2 fin'],
        ]);

        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La communauté',
            'attr' => ['class' => 'has-select2 communaute'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('/gestion/statistique/rapports/tableau.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/gestion/statistique/rapports/data_tableau', name: 'app_gestion_statistique_rapport_tableau_data', condition: "request.query.has('filters')")]
    public function datadashbordMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();

        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);getAudienceEtCommunauteTableauDemande
        $data = $missionrapportRepository->getMissionRapportEtCommunauteTableauMission($date, $communaute);
        $dataTotalValider = [];
        $dataTotalRejeter = [];
        $dataTotalInitie = [];
        $dataCompte = [];

        // foreach ($data as  $cam) {
        //     if ($cam['etat'] == "audience_rejeter")
        //         $dataTotalRejeter['rejeter'] = $cam['_total'];
        //     if ($cam['etat'] == "audience_valider")
        //         $dataTotalValider['valider'] = $cam['_total'];
        //     if ($cam['etat'] == "audience_initie")
        //         $dataTotalInitie['initie'] = $cam['_total'];
        // }
        
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

        // $dataCompte = [
        //     'audienceInitie' => $dataTotalEtat[0],
        //     'audienceValider' => $dataTotalEtat[1],
        //     'audienceRejetr' => $dataTotalEtat[2],
        // ];

        //   dd($dataCompte);

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
                    'y' => $_row['_total'],
                    'sliced' => true,
                    'selected' => true,

                ];
            } else {
                $name = '';

                if ($_row['etat'] == "audience_valider")
                    $name = "Audiences validées";
                if ($_row['etat'] == "audience_rejeter")
                    $name = "Audiences réjettées";
                if ($_row['etat'] == "audience_initie")
                    $name = "Audiences initiées";
                $datas[] = [
                    'name' => $name,
                    'y' => $_row['_total']
                ];
            }
        }
        $series = [

            "name" => "Audience",
            "colorByPoint" => true,
            "data" => $datas,

        ];
        return $this->json([
            'data' => $series,
            'compteAudience' => $dataCompte
        ]);
    }



    #[Route('/gestion/statistique/rapports/communaute', name: 'app_gestion_statistique_rapport_communaute')]
    public function indexCommunauteMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository)
    {

        $dataDebut = $missionrapportRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_rapport_communaute'))
            ->setMethod('POST');
        $formBuilder->add(
            'date',
            ChoiceType::class,
            [
                'placeholder' => '---',
                'label' => 'Année',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2 date'],
                'multiple' => false,
                'choices'  => array_flip($resultatDebut),
            ]
        );
        $formBuilder->add('date_fin', ChoiceType::class, [
            'label' => 'Fin',
            'choices' => array_flip($resultatDebut),
            'attr' => ['class' => 'has-select2 fin'],
        ]);

        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La communauté',
            'attr' => ['class' => 'has-select2 communaute'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('/gestion/statistique/rapports/communaute.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/rapports/data_communaute', name: 'app_gestion_statistique_rapport_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunauteMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);
        $data = $missionrapportRepository->getMissionRapportParMoisEtCommunaute($date, $communaute);
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
                'stack' => 'demande'
            ],
            [
                "name" => 'Réfusées',
                "data" => $restr,
                'stack' => 'demande'
            ]
        ];
        return $this->json(['series' => $series, 'mois' => $mois]);
    }

    

    #[Route('/gestion/statistique/rapports/categorie', name: 'app_gestion_statistique_rapport_categorie')]
    public function indexCategorieMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository)
    {

        $dataDebut = $missionrapportRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_rapport_categorie'))
            ->setMethod('POST');
        $formBuilder->add(
            'date',
            ChoiceType::class,
            [
                'placeholder' => '---',
                'label' => 'Année',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2 date'],
                'multiple' => false,
                'choices'  => array_flip($resultatDebut),
            ]
        );
        $formBuilder->add('date_fin', ChoiceType::class, [
            'label' => 'Fin',
            'choices' => array_flip($resultatDebut),
            'attr' => ['class' => 'has-select2 fin'],
        ]);

        $formBuilder->add('categorie', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La catégorie',
            'attr' => ['class' => 'has-select2 categorie'],
            'choice_attr' => function (Categorie $categorie) {
                return ['data-value' => $categorie->getLibelle()];
            },
            'class' => Categorie::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('/gestion/statistique/rapports/categorie.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/rapports/data_categorie', name: 'app_gestion_statistique_rapport_categorie_data', condition: "request.query.has('filters')")]
    public function dataCategorieMissionRapport(Request $request, DemandeRepository $demandeRepository, CategorieRepository $categorieRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $categorie = $filters['categorie'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$categorie);
        $data = $demandeRepository->getMissionRapportParMoisEtCategorie($date, $categorie);
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
               // 'stack' => 'Demande'
            ],
            [
                "name" => 'Réfusées',
                "data" => $restr,
               // 'stack' => 'Demande'
            ]
        ];
        return $this->json(['series' => $series, 'mois' => $mois]);
    }




    #[Route('/gestion/statistique/rapports/localite', name: 'app_gestion_statistique_rapport_localite')]
    public function indexLocaliteMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository)
    {

        $dataDebut = $missionrapportRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_rapport_localite'))
            ->setMethod('POST');
        $formBuilder->add(
            'date',
            ChoiceType::class,
            [
                'placeholder' => '---',
                'label' => 'Année',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2 date'],
                'multiple' => false,
                'choices'  => array_flip($resultatDebut),
            ]
        );
        $formBuilder->add('date_fin', ChoiceType::class, [
            'label' => 'Fin',
            'choices' => array_flip($resultatDebut),
            'attr' => ['class' => 'has-select2 fin'],
        ]);

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La localité',
            'attr' => ['class' => 'has-select2 localite'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('/gestion/statistique/rapports/localite.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/rapports/data_localite', name: 'app_gestion_statistique_rapport_localite_data', condition: "request.query.has('filters')")]
    public function dataLocaliteMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository, LocaliteRepository $localiteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $localite = $filters['localite'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$localite);
        $data = $missionrapportRepository->getMissionRapportParMoisEtLocalite($date, $localite);
        // dd($data);
        $mois = [];
        $dataValider = [];
        $dataRejeter = [];
        foreach ($data as $cam) {

            if ($cam['etat'] == "rapport_rejeter") {
                $dataRejeter[] = [
                    'mois' => $cam['mois'],
                    'total' => $cam['_total']
                ];
            }
            if ($cam['etat'] == "rapport_valider") {
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }
     }





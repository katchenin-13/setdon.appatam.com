<?php

namespace App\Controller\Configuration\Statistique;

use App\Service\Utils;
use App\Entity\Audience;
use App\Entity\Localite;
use App\Entity\Categorie;
use App\Entity\Communaute;
use App\Service\ActionRender;
use App\Service\StatsService;
use Doctrine\ORM\QueryBuilder;
use App\Controller\BaseController;
use App\Repository\EmployeRepository;
use App\Repository\CommunauteRepository;
use finfo;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/dashboard')]
class DashboadController extends BaseController
{
    
    
    private function createFilterForm()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_config_statistique_index'))
            ->setMethod('POST');

        $formBuilder->add('tranche', ChoiceType::class, [
            'label' => 'Tranche d\'âge',
            'required' => false,
            'placeholder' => '---',
            'choices' => array_flip([
                '16_24' => '16-24',
                '25_35' => '25-35',
                '36_44' => '36-44',
                '45_99' => '45+'
            ])
        ]);
        $formBuilder->add('anciennete', ChoiceType::class, [
            'label' => 'Ancienneté',
            'required' => false,
            'placeholder' => '---',
            'choices' => array_flip([
                '0_5' => '0-5',
                '6_15' => '6-15',
                '16_99' => '16 et plus'
            ])
        ]);
        $formBuilder->add('annee', IntegerType::class, ['label' => 'Année']);
        $formBuilder->add('mois', ChoiceType::class, ['choices' => array_flip(Utils::MOIS), 'label' => 'Mois', 'attr' => ['class' => 'has-select2']]);
        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Type de contrat',
            'class' => Communaute::class,
            'required' => false
        ]);
        $formBuilder->add('genre', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'code',
            'label' => 'Sexe',
            'class' => Genre::class,
            'required' => false
        ]);
        $formBuilder->add('unite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Unité',
            'class' => UniteEmploye::class,
            'required' => false
        ]);
        $formBuilder->add('niveauHierarchique', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Niveau Hiérarchique',
            'class' => NiveauHierarchique::class,
            'required' => false
        ]);
        $formBuilder->add('niveauMaitrise', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Niveau de maitrise',
            'class' => NiveauMaitrise::class,
            'required' => false
        ]);
        $formBuilder->add('statut', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Statut',
            'class' => StatutEmploye::class,
            'required' => false
        ]);

        return $formBuilder->getForm();
    }

##################################################################################################################
#########################################################  Audiences de statitiques###############################
    


    #[Route('/type-contrat', name: 'app_rh_dashboard_type_contratau')]
    public function indexTypeContratau(Request $request)
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_type_contratau'))
            ->setMethod('POST');
        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La Communaute',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
            ]);
        
        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
            ]);



        $form = $formBuilder->getForm();


       
       

        return $this->renderForm('config/statistique/Audiences/type_contrat.html.twig', [
            'form' => $form,
            
        ]);
    }
    
    #[Route('/data-type-contrat', name: 'app_rh_dashboard_type_contrat_dataau', condition: "request.query.has('filters')")]
    public function dataTypeContratau(Request $request, StatsService $statsService,DataTableFactory $dataTableFactory)
    {
        $all = $request->query->all();
        dd($all);
      
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['communaute'];
        $debut = $filters['debut'];
        $fin = $filters['fin'];

        $table = $dataTableFactory->create()
            ->add('daterencontre', DateTimeColumn::class, [
                'label' => 'Date de la rencontre',
                "format" => 'd-m-Y'
            ])
            ->add('communaute', TextColumn::class, ['label' => 'Communauté', 'field' => 'co.libelle'])
            ->add('nomchef', TextColumn::class, ['label' => 'Nom du chef'])
            ->add('numero', TextColumn::class, ['label' => 'Numéro'])
            ->add('motif', TextColumn::class, ['label' => 'Motif'])
            // ->add('email', TextColumn::class, ['label' => 'Email'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function (QueryBuilder $req) use ($typeContratId, $debut, $fin) {
                    $req->select('a,co')
                        ->from(Audience::class, 'a')
                        ->leftJoin('a.communaute', 'co')
                        ->orderBy('a.daterencontre', 'DESC')
                        ->andWhere('a.etat = :status')
                        ->andWhere('a.CreatedAt >= :debut')
                        ->andWhere('a.CreatedAt <= :fin')
                        ->andWhere('co.id = :id')
                        ->setParameter(':debut', $debut)
                        ->setParameter(':fin', $fin)
                        ->setParameter('id', $typeContratId)
                        ->setParameter('status', 'audience_valider');
                }
            ])
            ->setName('dt_app_gestion_audience');

        $renders = [
            'edit' =>  new ActionRender(function () {
                return true;
            }),
            'delete' => new ActionRender(function () {
                return true;
            }),
        ];


        $hasActions = false;

        foreach ($renders as $_ => $cb) {
            if ($cb->execute()) {
                $hasActions = true;
                break;
            }
        }

        if ($hasActions) {
            $table->add('id', TextColumn::class, [
                'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Audience $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeLg2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_gestion_audience_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                            ],
                            'delete' => [
                                'target' => '#exampleModalSizeNormal',
                                'url' => $this->generateUrl('app_gestion_audience_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['delete']
                            ]
                        ]

                    ];
                    return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                }
            ]);
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }



    //     $data =   $statsService->getAudienceMoisComNo($typeContratId,$debut,$fin);
    //     $data1 = $statsService->getAudienceMoisComYes($typeContratId,$debut,$fin);

    //     $dataan =   $statsService->getAudienceAnneeComNo($typeContratId, $debut, $fin);
    //     $data1ann = $statsService->getAudienceAnneeComYes($typeContratId, $debut, $fin);
    //    //dd($dataan);
    //     $mois = [];
    //     $annee =[];
    //     $nbre = [];
    //     $nbreRejete = [];

    //     $nbre1 = [];
    //     $nbreRejete1 = [];
    //     foreach ($data as $cam) {
    //         $mois[] = $cam['mois'];
    //         $nbredata[] = $cam['nbre'];
    //     }
    //     foreach ($data1 as $cam) {
    //         $mois[] = $cam['mois'];
    //         $nbreRejete[] = $cam['nbre'];
    //     }

    //     foreach ($dataan as $cam) {
    //         $annee[] = $cam['annee'];
    //         $nbredata[] = $cam['nbre'];
    //     }
    //     foreach ($data1ann as $cam) {
    //         $annee[] = $cam['annee'];
    //         $nbreRejete1[] = $cam['nbre'];
    //     }
    //     $dataComplet = [
    //         [
    //             'name' => 'Accordéé',
    //             'data' => $nbre,
    //         ],
    //         [
    //             'name' => 'Rejeté',
    //             'data' => $nbreRejete,
    //         ],
    //     ];

    //     $dataComplet1 = [
    //         [
    //             'name' => 'Accordéé',
    //             'data' => $nbre1,
    //         ],
    //         [
    //             'name' => 'Rejeté',
    //             'data' => $nbreRejete1,
    //         ],
    //     ];
    //  return $this->render('gestion/statistique/audiences/communaute.html.twig', [
    //         'datatable' => $table,
    //     ]);
    //     //($dataComplet1);
    //  //   dd($this->json($dataComplet));
        // return $this->json([
        //      'annee'=>$annee,
        //     'mois' => $mois,
        //     'data' => $dataComplet,
        //     'data1' => $dataComplet1,
        // ]);
     }

    #[Route('/categorie', name: 'app_rh_dashboard_genreau')]
    public function indexGenreau()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genreau'))
            ->setMethod('POST');

        $formBuilder->add('categorie', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Catégories',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Categorie $categorie) {
                return ['data-value' => $categorie->getLibelle()];
            },
            'class' => Categorie::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Audiences/genre.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/data-genre', name: 'app_rh_dashboard_genre_dataau', condition: "request.query.has('filters')")]
    public function dataGenreau(Request $request, StatsService $statsService)
    {

        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['categorie'];
        $data =   $statsService->getAudienceMoisCetagorieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisCetagorieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/localite', name: 'app_rh_dashboard_hierarchie_sexeau')]
    public function indexHierarchiqueSexeau()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genreau'))
            ->setMethod('POST');

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Localitées',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Audiences/hierarchie_sexe.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/data-hierarchie-sexe', name: 'app_rh_dashboard_hierarchie_sexe_dataau', condition: "request.query.has('filters')")]
    public function dataHierarchiqueSexeau(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['localite'];
        $data =   $statsService->getAudienceMoisLocalieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisLocalieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }


    ##################################################################################################################
    #########################################################  Audiences de demandes ###############################

   

    #[Route('/type-contrat', name: 'app_rh_dashboard_type_contratdem')]
    public function indexTypeContratdem()
    {
        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_type_contratdem'))
            ->setMethod('POST');

        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La Communaute',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Demandes/type_contrat.html.twig', [
            'form' => $form
        ]);
    }
    #[Route('/data-type-contrat', name: 'app_rh_dashboard_type_contrat_datadem', condition: "request.query.has('filters')")]
    public function dataTypeContratdem(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['communaute'];
        $data =   $statsService->getAudienceMoisComNo($typeContratId);
        $data1 = $statsService->getAudienceMoisComYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/categorie', name: 'app_rh_dashboard_genredem')]
    public function indexGenredem()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genredem'))
            ->setMethod('POST');

        $formBuilder->add('categorie', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Catégories',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Categorie $categorie) {
                return ['data-value' => $categorie->getLibelle()];
            },
            'class' => Categorie::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Demandes/genre.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/data-genre', name: 'app_rh_dashboard_genre_datadem', condition: "request.query.has('filters')")]
    public function dataGenredem(Request $request, StatsService $statsService)
    {

        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['categorie'];
        $data =   $statsService->getAudienceMoisCetagorieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisCetagorieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/localite', name: 'app_rh_dashboard_hierarchie_sexedem')]
    public function indexHierarchiqueSexedem()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genredem'))
            ->setMethod('POST');

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Localitées',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Demandes/hierarchie_sexe.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/data-hierarchie-sexe', name: 'app_rh_dashboard_hierarchie_sexe_datadem', condition: "request.query.has('filters')")]
    public function dataHierarchiqueSexedem(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['localite'];
        $data =   $statsService->getAudienceMoisLocalieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisLocalieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }


    ##################################################################################################################
    #########################################################  Audiences de don ###############################


    // #[Route('/periode', name: 'app_rh_dashboard_statutdon')]
    // public function indexStatutdon()
    // {
    //     return $this->render('config/statistique/Dons/statut.html.twig');
    // }

    // #[Route('/data-statut', name: 'app_rh_dashboard_statut_datadon')]
    // public function dataStatutdon(Request $request, StatsService $statsService)
    // {
    //     $all = $request->query->all();
    //     $filters = $all['filters'] ?? [];
    //     $totalGlobal = $employeRepository->countAll($filters);

    //     $data = $employeRepository->getStatusData($filters);
    //     $results = [];
    //     foreach ($data as $row) {
    //         $total = ($row['total'] / $totalGlobal) * 100;
    //         $results[] = [
    //             'name' => $row['libelle'],
    //             'y' => round($total),
    //             'value' => $row['total'],
    //             'drilldown' => null
    //         ];
    //     }
    //     return $this->json($results);
    // }


    #[Route('/type-contrat', name: 'app_rh_dashboard_type_contratdon')]
    public function indexTypeContratdon()
    {
        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_type_contratdon'))
            ->setMethod('POST');

        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La Communaute',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Dons/type_contrat.html.twig', [
            'form' => $form
        ]);
    }
    #[Route('/data-type-contrat', name: 'app_rh_dashboard_type_contrat_datadon', condition: "request.query.has('filters')")]
    public function dataTypeContratdon(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['communaute'];
        $data =   $statsService->getAudienceMoisComNo($typeContratId);
        $data1 = $statsService->getAudienceMoisComYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/categorie', name: 'app_rh_dashboard_genredon')]
    public function indexGenredon()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genredon'))
            ->setMethod('POST');

        $formBuilder->add('categorie', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Catégories',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Categorie $categorie) {
                return ['data-value' => $categorie->getLibelle()];
            },
            'class' => Categorie::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Dons/genre.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/data-genre', name: 'app_rh_dashboard_genre_datadon', condition: "request.query.has('filters')")]
    public function dataGenredon(Request $request, StatsService $statsService)
    {

        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['categorie'];
        $data =   $statsService->getAudienceMoisCetagorieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisCetagorieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/localite', name: 'app_rh_dashboard_hierarchie_sexedon')]
    public function indexHierarchiqueSexedon()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genredon'))
            ->setMethod('POST');

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Localitées',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Dons/hierarchie_sexe.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/data-hierarchie-sexe', name: 'app_rh_dashboard_hierarchie_sexe_datadon', condition: "request.query.has('filters')")]
    public function dataHierarchiqueSexedon(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['localite'];
        $data =   $statsService->getAudienceMoisLocalieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisLocalieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }


    ##################################################################################################################
    #########################################################  Audiences de Promesses ###############################


    // #[Route('/periode', name: 'app_rh_dashboard_statutpro')]
    // public function indexStatutpro()
    // {
    //     return $this->render('config/statistique/Promesses/statut.html.twig');
    // }

    // #[Route('/data-statut', name: 'app_rh_dashboard_statut_datapro')]
    // public function dataStatutpro(Request $request, StatsService $statsService)
    // {
    //     $all = $request->query->all();
    //     $filters = $all['filters'] ?? [];
    //     $totalGlobal = $employeRepository->countAll($filters);

    //     $data = $employeRepository->getStatusData($filters);
    //     $results = [];
    //     foreach ($data as $row) {
    //         $total = ($row['total'] / $totalGlobal) * 100;
    //         $results[] = [
    //             'name' => $row['libelle'],
    //             'y' => round($total),
    //             'value' => $row['total'],
    //             'drilldown' => null
    //         ];
    //     }
    //     return $this->json($results);
    // }


    #[Route('/type-contrat', name: 'app_rh_dashboard_type_contratpro')]
    public function indexTypeContratpro()
    {
        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_type_contratpro'))
            ->setMethod('POST');

        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La Communaute',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Promesses/type_contrat.html.twig', [
            'form' => $form
        ]);
    }
    #[Route('/data-type-contrat', name: 'app_rh_dashboard_type_contrat_datapro', condition: "request.query.has('filters')")]
    public function dataTypeContratpro(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['communaute'];
        $data =   $statsService->getAudienceMoisComNo($typeContratId);
        $data1 = $statsService->getAudienceMoisComYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/categorie', name: 'app_rh_dashboard_genrepro')]
    public function indexGenrepro()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genrepro'))
            ->setMethod('POST');

        $formBuilder->add('categorie', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Catégories',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Categorie $categorie) {
                return ['data-value' => $categorie->getLibelle()];
            },
            'class' => Categorie::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Promesses/genre.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/data-genre', name: 'app_rh_dashboard_genre_datapro', condition: "request.query.has('filters')")]
    public function dataGenrepro(Request $request, StatsService $statsService)
    {

        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['categorie'];
        $data =   $statsService->getAudienceMoisCetagorieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisCetagorieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/localite', name: 'app_rh_dashboard_hierarchie_sexepro')]
    public function indexHierarchiqueSexepro()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genrepro'))
            ->setMethod('POST');

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Localitées',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);

        $formBuilder->add('debut', DateType::class, [
            "label" => "Date de debut*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de debut'
            ]
        ]);

        $formBuilder->add('fin', DateType::class, [
            "label" => "Date de fin*",
            "required" => true,
            "widget" => 'single_text',
            "input_format" => 'Y-m-d',
            "by_reference" => true,
            "empty_data" => '',
            'attr' => [
                'class' => 'date',
                'placeholder' => 'Date de fin'
            ]
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Promesses/hierarchie_sexe.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/data-hierarchie-sexe', name: 'app_rh_dashboard_hierarchie_sexe_datapro', condition: "request.query.has('filters')")]
    public function dataHierarchiqueSexepro(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['localite'];
        $data =   $statsService->getAudienceMoisLocalieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisLocalieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/pyramide-age', name: 'app_rh_dashboard_pyramide_agepro')]
    public function indexPyramideAgepro()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genrepro'))
            ->setMethod('POST');

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Localitées',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);

        


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Promesses/hierarchie_sexe.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/pyramide-age-data', name: 'app_rh_dashboard_pyramide_age_datapro', condition: "request.query.has('filters')")]
    public function dataPyramideAgepro(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['localite'];
        $data =   $statsService->getAudienceMoisLocalieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisLocalieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/pyramide-anc', name: 'app_rh_dashboard_pyramide_ancpro')]
    public function indexPyramideAnciennetepro()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genrepro'))
            ->setMethod('POST');

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Localitées',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Promesses/hierarchie_sexe.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/pyramide-anc-data', name: 'app_rh_dashboard_pyramide_anc_data', condition: "request.query.has('filters')")]
    public function dataPyramideAnciennetepro(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['localite'];
        $data =   $statsService->getAudienceMoisLocalieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisLocalieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }

    #[Route('/pyramide-an', name: 'app_rh_dashboard_pyramide_anpro')]
    public function indexPyramideAnciennetpro()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_genrepro'))
            ->setMethod('POST');

        $formBuilder->add('localite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Localitées',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (Localite $localite) {
                return ['data-value' => $localite->getLibelle()];
            },
            'class' => Localite::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('config/statistique/Promesses/hierarchie_sexe.html.twig', [
            'form' => $form
        ]);
    }

   
    #[Route('/pyramide-an-data', name: 'app_rh_dashboard_pyramide_an_datapro', condition: "request.query.has('filters')")]
    public function dataPyramideAnciennetpro(Request $request, StatsService $statsService)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['localite'];
        $data =   $statsService->getAudienceMoisLocalieNo($typeContratId);
        $data1 = $statsService->getAudienceMoisLocalieYes($typeContratId);
        $mois = [];
        $nbre = [];
        $nbreRejete = [];
        foreach ($data as $cam) {
            $mois[] = $cam['mois'];
            $nbredata[] = $cam['nbre'];
        }
        foreach ($data1 as $cam) {
            $mois[] = $cam['mois'];
            $nbreRejete[] = $cam['nbre'];
        }
        $dataComplet = [
            [
                'name' => 'Accordéé',
                'data' => $nbre,
            ],
            [
                'name' => 'Rejeté',
                'data' => $nbreRejete,
            ],
        ];
        //dd($this->json($dataComplet));
        return $this->json([
            'mois' => $mois,
            'data' => $dataComplet,
        ]);
    }
    

    ##################################################################################################################
    #########################################################  Audiences de Promesses ###############################





    #[Route('/repportingaudience', name: 'app_rh_dashboard_audience')]
    public function indexAudiences( StatsService $statsService)
    {
      
        
        
///dd($audiencesyes);
        // $data =[
        //     'audiencesno'=> $audiencesno,
        //     'audiencesyes' => $audiencesyes
        // ];
        return $this->render('config/statistique/Repportages/indexaudiences.html.twig');
    }

    #[Route('/repportingdemande', name: 'app_rh_dashboard_demande')]
    public function indexdemandes()
    {
        return $this->render('config/statistique/Repportages/indexdemandes.html.twig');
    }

    #[Route('/repportingdon', name: 'app_rh_dashboard_don')]
    public function indexDons(StatsService $statsService)
    {
        // $data = $statsService->getPromesseespece();
        // dd($data);
        return $this->render('config/statistique/Repportages/indexdons.html.twig');
    }

    #[Route('/repportingpromesses', name: 'app_rh_dashboard_promesses')]
    public function dataPromessess()
    {
        return $this->render('config/statistique/Repportages/indexpromesse.html.twig');
    }


    #[Route('/pyramide-age', name: 'app_rh_dashboard_pyramide_age')]
    public function indexPyramideAge()
    {
        return $this->render('config/statistique/pyramide_age.html.twig');
    }



    #[Route('/pyramide-age-data', name: 'app_rh_dashboard_pyramide_age_data')]
    public function dataPyramideAge(Request $request, EmployeRepository $employeRepository, GenreRepository $genreRepository, NiveauHierarchiqueRepository $niveauHierarchiqueRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];

        $tranches =  ['16-24', '25-35', '36-44', '45+'];


        $genres = $genreRepository->findBy([], ['id' => 'ASC']);

        $results = [];
        foreach ($genres as $genre) {
            $idGenre = $genre->getId();
            $filters['genre'] = $idGenre;
            $data[$idGenre] = $employeRepository->getPyramideAge($filters);
        }


        /**
         * ['id' => 1 , 'value']
         */

        //xAxis = [...liste_niveaux]
        /**
         * series : [{name: 'Feminin', data: [x0,y0,z0]}, {name: 'Masculin', data: [x1,y1,z1]}]
         */



        foreach ($data as $rows) {
            usort($rows, function ($a, $b) {
                return $a['tranche_age'] <=> $b['tranche_age'];
            });
        }

        $index = 0;

        foreach ($data as $idGenre => $rows) {

            $allTranches = [];
            foreach ($rows as $_row) {
                $currentTranche = $_row['tranche_age'];
                $allTranches[$currentTranche] = $index == 0 ? -$_row['_total'] : $_row['_total'];
            }
            foreach ($tranches as $tranche) {
                if (!isset($allTranches[$tranche])) {
                    $allTranches[$tranche] = 0;
                }
            }

            ksort($allTranches);


            $results[$idGenre] = array_values($allTranches);
            $index += 1;
        }


        $getLibelleGenre = function ($idGenre) use ($genreRepository) {
            return $genreRepository->find($idGenre);
        };

        $series = [];

        foreach ($results as $idGenre => $data) {
            $_genre = $getLibelleGenre($idGenre);
            $colors = ['M' => '#262626', 'F' => '#cf2e2e'];
            $series[] = ['name' => $_genre->getLibelle(), 'data' => $data, 'color' => $colors[$_genre->getCode()]];
        }

        return $this->json(['series' => $series, 'tranches' => $tranches]);
    }




    #[Route('/pyramide-anc', name: 'app_rh_dashboard_pyramide_anc')]
    public function indexPyramideAnciennete(Request $request, EmployeRepository $employeRepository, GenreRepository $genreRepository, NiveauHierarchiqueRepository $niveauHierarchiqueRepository)
    {
        return $this->render('config/statistique/pyramide_anc.html.twig');
    }



    #[Route('/pyramide-anc-data', name: 'app_rh_dashboard_pyramide_anc_data')]
    public function dataPyramideAnciennete(Request $request, EmployeRepository $employeRepository, GenreRepository $genreRepository, NiveauHierarchiqueRepository $niveauHierarchiqueRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];

        $tranches =   ['0-5', '6-15', '16+'];


        $genres = $genreRepository->findBy([], ['id' => 'ASC']);

        $results = [];
        foreach ($genres as $genre) {
            $idGenre = $genre->getId();
            $filters['genre'] = $idGenre;
            $data[$idGenre] = $employeRepository->getPyramideAnciennete($filters);
        }
        foreach ($data as $rows) {
            usort($rows, function ($a, $b) {
                return $a['tranche_age'] <=> $b['tranche_age'];
            });
        }

        $index = 0;

        foreach ($data as $idGenre => $rows) {

            $allTranches = [];

            foreach ($rows as $_row) {
                $currentTranche = $_row['tranche_age'];
                $allTranches[$currentTranche] = $index == 0 ? -$_row['_total'] : $_row['_total'];
            }


            foreach ($tranches as $tranche) {
                if (!isset($allTranches[$tranche])) {
                    $allTranches[$tranche] = 0;
                }
            }



            ksort($allTranches);


            $results[$idGenre] = array_values($allTranches);
            $index += 1;
        }


        $getLibelleGenre = function ($idGenre) use ($genreRepository) {
            return $genreRepository->find($idGenre);
        };

        $series = [];

        foreach ($results as $idGenre => $data) {
            $_genre = $getLibelleGenre($idGenre);
            $colors = ['M' => '#262626', 'F' => '#cf2e2e'];
            $series[] = ['name' => $_genre->getLibelle(), 'data' => $data, 'color' => $colors[$_genre->getCode()]];
        }

        return $this->json(['series' => $series, 'tranches' => $tranches]);
    }
}

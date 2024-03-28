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
use App\Repository\DemandeRepository;
use App\Repository\LocaliteRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommunauteRepository;
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

class DemandeStatisAvenirController extends BaseController
{
    #[Route('/gestion/statistique/demandes/tableau', name: 'app_gestion_statistique_demande_tableau')]
    public function indexdashbordDemande(Request $request, DemandeRepository $demandeRepository)
    {

        $all = $request->query->all();
        $dataDebut = $demandeRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        };

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_demande_tableau'))
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

        /*$formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'La communauté',
            'attr' => ['class' => 'has-select2 communaute'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);*/


        $form = $formBuilder->getForm();

        return $this->renderForm('/gestion/statistique/demandes/tableau.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/gestion/statistique/demandes/data_tableau', name: 'app_gestion_statistique_demande_tableau_data', condition: "request.query.has('filters')")]
    public function datadashbordDemande(Request $request, DemandeRepository $demandeRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();

        $filters = $all['filters'] ?? [];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);getAudienceEtCommunauteTableauDemande
        $data = $demandeRepository->getDemandeParMoisCommunauteTableauDemande($date);
        // dd($data);
        
        $mois = [];
        $dataValider = [];
        $dataRejeter = [];
        $dataInitie = [];
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
            if ($cam['etat'] == "demande_initie") {
                $dataInitie[] = [
                    'mois' => $cam['mois'],
                    'total' => $cam['_total']
                ];
            }
        }
        $restv = [];
        $restr = [];
        $resti = [];

        foreach ($dataValider as $key => $value) {
            $restv[] = $value['total'];
        }
        foreach ($dataRejeter as $key => $value) {
            $restr[] = $value['total'];
        }
        foreach ($dataInitie as $key => $value) {
            $resti[] = $value['total'];
        }
        foreach ($data as $key => $value) {
            $mois[] = $value['mois'];
        }

        $dataCompte = [
            'audienceInitie' => $resti,
            'audienceValider' => $restv,
            'audienceRejeter' => $restr,
        ];

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
                
            ],
             [
                "name" => 'Initie',
                "data" => $resti,
                'stack' => 'demande'
             
            ]
        ];
        //dd($series);

        return $this->json([
                'series' => $series,
                'mois' => $mois,
                'compteAudience' => $dataCompte
            ]);
  
    }



    #[Route('/gestion/statistique/demandes/communaute', name: 'app_gestion_statistique_demande_communaute')]
    public function indexCommunauteDemande(Request $request, DemandeRepository $demandeRepository)
    {

        $dataDebut = $demandeRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_demande_communaute'))
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

        return $this->renderForm('/gestion/statistique/demandes/communaute.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/demandes/data_communaute', name: 'app_gestion_statistique_demande_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunauteDemande(Request $request, DemandeRepository $demandeRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);
        $data = $demandeRepository->getDemandeParMoisEtCommunaute($date, $communaute);
        //dd($data);
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

    

    #[Route('/gestion/statistique/demandes/categorie', name: 'app_gestion_statistique_demande_categorie')]
    public function indexCategorieDemande(Request $request, DemandeRepository $demandeRepository)
    {

        $dataDebut = $demandeRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_demande_categorie'))
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
            'label' => 'La nature de la communauté',
            'attr' => ['class' => 'has-select2 categorie'],
            'choice_attr' => function (Categorie $categorie) {
                return ['data-value' => $categorie->getLibelle()];
            },
            'class' => Categorie::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('/gestion/statistique/demandes/categorie.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/demande/data_categorie', name: 'app_gestion_statistique_demande_categorie_data', condition: "request.query.has('filters')")]
    public function dataCategorieDemande(Request $request, DemandeRepository $demandeRepository, CategorieRepository $categorieRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $categorie = $filters['categorie'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$categorie);
        $data = $demandeRepository->getDemandeParMoisEtCategorie($date, $categorie);
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




    #[Route('/gestion/statistique/demande/localite', name: 'app_gestion_statistique_demande_localite')]
    public function indexLocalite(Request $request, DemandeRepository $demandeRepository)
    {

        $dataDebut = $demandeRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_demande_localite'))
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
        
        return $this->renderForm('/gestion/statistique/demandes/localite.html.twig', [
            'form' => $form 
        ]);
    }

    #[Route('/gestion/statistique/demande/data_localite', name: 'app_gestion_statistique_demande_localite_data', condition: "request.query.has('filters')")]
    public function dataLocalite(Request $request, DemandeRepository $demandeRepository, LocaliteRepository $localiteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $localite = $filters['localite'];
        $date = $filters['date'];
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





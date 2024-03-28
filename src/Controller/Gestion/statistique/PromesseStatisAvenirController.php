<?php

namespace App\Controller\Gestion\statistique;

use App\Entity\Audience;
use App\Entity\Localite;
use App\Entity\Promesse;
use App\Entity\Categorie;
use App\Entity\Communaute;
use App\Service\ActionRender;
use Doctrine\ORM\QueryBuilder;
use App\Controller\BaseController;
use App\Entity\Fielpromesse;
use App\Repository\FieldonRepository;
use App\Repository\LocaliteRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommunauteRepository;
use App\Repository\DonRepository;
use App\Repository\FielpromesseRepository;
use App\Repository\PromesseRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PromesseStatisAvenirController extends BaseController
{




    #[Route('/gestion/statistique/promesses/tableau', name: 'app_gestion_statistique_promesse_tableau')]
    public function indexdashbordPromesse(Request $request, FielpromesseRepository $fielpromesseRepository)
    {

        $all = $request->query->all();
        $dataDebut = $fielpromesseRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
            //dd($resultatDebut);


        ;



        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_promesse_tableau'))
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

        return $this->renderForm('/gestion/statistique/promesses/tableau.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/gestion/statistique/promesses/data_tableau', name: 'app_gestion_statistique_promesse_tableau_data', condition: "request.query.has('filters')")]
    public function datadashbordPromesse(Request $request, FielpromesseRepository $fielpromesseRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();

        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'] ?? [];
        $date = $filters['date'] ?? [];
        //$fin = $filters['fin'];
        //dd($date,$communaute);getAudienceEtCommunauteTableauDemande
        $data = $fielpromesseRepository->getPromesseParMoisCommunauteTableauPromesse($date, $communaute);
        //dd($data);
        $mois = [];
        $dataValider = [];
        $dataRejeter = [];
        $dataInitie = [];
        foreach ($data as $cam) {

            if ($cam['etat'] == "fielpromesse_rejeter") {
                $dataRejeter[] = [
                    'mois' => $cam['mois'],
                    'total' => $cam['_total']
                ];
            }
            if ($cam['etat'] == "fielpromesse_valider") {
                $dataValider[] = [
                    'mois' => $cam['mois'],
                    'total' => $cam['_total']
                ];
            }
            if ($cam['etat'] == "fielpromesse_initie") {
                $dataInitie[] = [
                    'mois' => $cam['mois'],
                    'total' => $cam['_total']
                ];
            }
        }
        $restv = [];
        $restr = [];
        $resti = [];

       // dd($dataInitie);
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

            ],
            [
                "name" => 'Réfusées',
                "data" => $restr,
                

            ],
            [
                "name" => 'Initie',
                "data" => $resti,
               

            ]
        ];

    
        
        return $this->json([
            'data' => $series,
             'mois' => $mois,
            'compteAudience' => $dataCompte
        ]);
    }


    


    #[Route('/gestion/statistique/promesses/communaute', name: 'app_gestion_statistique_promesse_communaute')]
    public function indexCommunautePromesse(Request $request, FielpromesseRepository $fielpromesseRepository)
    {

        $dataDebut = $fielpromesseRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_promesse_communaute'))
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

        return $this->renderForm('/gestion/statistique/promesses/communaute.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/promesses/data_communaute', name: 'app_gestion_statistique_promesse_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunautePromesse(Request $request, FielpromesseRepository $fielpromesseRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'] ?? [];
        $date = $filters['date'] ?? [];
        //$fin = $filters['fin'];
        //dd($date,$communaute);
        $data = $fielpromesseRepository->getPromesseParMoisEtCommunaute($date, $communaute);
        // dd($data);
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
                'stack' => 'fieldon'
            ],
            [
                "name" => 'En espèce',
                "data" => $restr,
                'stack' => 'fieldon'
            ]
        ];
        return $this->json(['series' => $series, 'mois' => $mois]);
    }



    #[Route('/gestion/statistique/promesses/categorie', name: 'app_gestion_statistique_promesse_categorie')]
    public function indexCategoriePromesse(Request $request, FielpromesseRepository $fielpromesseRepository)
    {

        $dataDebut = $fielpromesseRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_promesse_categorie'))
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

        return $this->renderForm('/gestion/statistique/promesses/categorie.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/demande/data_categorie', name: 'app_gestion_statistique_promesse_categorie_data', condition: "request.query.has('filters')")]
    public function dataCategoriePromesse(Request $request, FielpromesseRepository $fielpromesseRepository, CategorieRepository $categorieRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $categorie = $filters['categorie'] ?? [];
        $date = $filters['date'] ?? [];
        //$fin = $filters['fin'];
        //dd($date,$categorie);
        $data = $fielpromesseRepository->getPromesseParMoisEtCategorie($date, $categorie);
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }




    #[Route('/gestion/statistique/promesses/localite', name: 'app_gestion_statistique_promesse_localite')]
    public function indexLocalitePromesse(Request $request, FielpromesseRepository $fielpromesseRepository)
    {

        $dataDebut = $fielpromesseRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_promesse_localite'))
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

        return $this->renderForm('/gestion/statistique/promesses/localite.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/demande/data_localite', name: 'app_gestion_statistique_promesse_localite_data', condition: "request.query.has('filters')")]
    public function dataLocalitePromesse(Request $request, FielpromesseRepository $fielpromesseRepository, LocaliteRepository $localiteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $localite = $filters['localite'] ?? [];
        $date = $filters['date'] ?? [];
        //$fin = $filters['fin'];
        //dd($date,$localite);
        $data = $fielpromesseRepository->getPromesseParMoisEtLocalite($date, $localite);
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }

}


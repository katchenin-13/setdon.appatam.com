<?php

namespace App\Controller\Gestion\statistique;


use App\Entity\Don;
use App\Entity\Typedon;
use App\Entity\Localite;
use App\Entity\Categorie;
use App\Entity\Communaute;
use App\Service\ActionRender;
use Doctrine\ORM\QueryBuilder;
use App\Repository\DonRepository;
use App\Controller\BaseController;
use App\Repository\FieldonRepository;
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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DonStatisAvenirController extends BaseController
{
    #[Route('/gestion/statistique/dons/tableau', name: 'app_gestion_statistique_don_tableau')]
    public function indexdashbordDon(Request $request, FieldonRepository $fieldonRepository)
    {

        $all = $request->query->all();
        $dataDebut = $fieldonRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
            //dd($resultatDebut);


        ;



        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_don_tableau'))
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

        return $this->renderForm('/gestion/statistique/dons/tableau.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/gestion/statistique/dons/data_tableau', name: 'app_gestion_statistique_don_tableau_data', condition: "request.query.has('filters')")]
    public function datadashbordDon(Request $request, FieldonRepository $fieldonRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();

        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);getAudienceEtCommunauteTableauDemande
        $data = $fieldonRepository->getDonParMoisCommunauteTableauDon($date, $communaute);
        //dd($data);
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
            'audienceRejeter' => $dataTotalRejeter,
            'audienceValider' => $dataTotalValider,
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
            "colorByPoint" => true,
            "data" => $datas,

        ];
        return $this->json([
            'data' => $series,
            'compteAudience' => $dataCompte
           
        ]);

    }

    
    #[Route('/gestion/statistique/dons/communaute', name: 'app_gestion_statistique_don_communaute')]
    public function indexCommunauteDon(Request $request, FieldonRepository $fieldonRepository)
    {

        $dataDebut = $fieldonRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_don_communaute'))
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
            'label' => 'La localité',
            'attr' => ['class' => 'has-select2 communaute'],
            'choice_attr' => function (Communaute $communaute) {
                return ['data-value' => $communaute->getLibelle()];
            },
            'class' => Communaute::class,
            'required' => false
        ]);

        $form = $formBuilder->getForm();

        return $this->renderForm('/gestion/statistique/dons/communaute.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/dons/data_communaute', name: 'app_gestion_statistique_don_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunauteDon(Request $request, FieldonRepository $fieldonRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }

    #[Route('/gestion/statistique/dons/categorie', name: 'app_gestion_statistique_don_categorie')]
    public function indexCategorieDon(Request $request, FieldonRepository $fieldonRepository)
    {

        $dataDebut = $fieldonRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_don_categorie'))
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

        return $this->renderForm('/gestion/statistique/dons/categorie.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/demande/data_categorie', name: 'app_gestion_statistique_don_categorie_data', condition: "request.query.has('filters')")]
    public function dataCategorieDon(Request $request, FieldonRepository $fieldonRepository, CategorieRepository $categorieRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $categorie = $filters['categorie'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$categorie);
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }




    #[Route('/gestion/statistique/demande/localite', name: 'app_gestion_statistique_don_localite')]
    public function indexLocaliteDon(Request $request, FieldonRepository $fieldonRepository)
    {

        $dataDebut = $fieldonRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_don_localite'))
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

        return $this->renderForm('/gestion/statistique/dons/localite.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/demande/data_localite', name: 'app_gestion_statistique_don_localite_data', condition: "request.query.has('filters')")]
    public function dataLocaliteDon(Request $request, FieldonRepository $fieldonRepository, LocaliteRepository $localiteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $localite = $filters['localite'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$localite);
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }

}

<?php

namespace App\Controller\Gestion\statistique;

use App\Service\Utils;
use App\Entity\Audience;
use App\Entity\Communaute;
use App\Service\ActionRender;
use Doctrine\ORM\QueryBuilder;
use App\Controller\BaseController;
use App\Entity\Categorie;
use App\Entity\Localite;
use App\Repository\AudienceRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommunauteRepository;
use App\Repository\LocaliteRepository;
use FontLib\Table\Type\name;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use phpDocumentor\Reflection\Types\Nullable;
use PhpParser\Node\Stmt\Foreach_;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AudienceStatisAvenirController extends BaseController
{
    
   

    #[Route('/gestion/statistique/audiences/tableau', name: 'app_gestion_statistique_audience_tableau')]
    public function indexdashbordAudience(Request $request, AudienceRepository $audienceRepository)
    {
      
        $all = $request->query->all();
        $dataDebut = $audienceRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

       
       ;



        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_audience_tableau'))
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

        return $this->renderForm('/gestion/statistique/audiences/tableau.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/gestion/statistique/audiences/data_tableau', name: 'app_gestion_statistique_audience_tableau_data', condition: "request.query.has('filters')")]
    public function datadashbordAudience(Request $request, AudienceRepository $audienceRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();

        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);
        $data = $audienceRepository->getAudienceEtCommunauteTableauAudience($date, $communaute);
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

  
    private function createFilterForm()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_audience_communaute'))
            ->setMethod('POST');


        $formBuilder->add('annee', IntegerType::class, ['label' => 'Année']);
        $formBuilder->add('mois', ChoiceType::class, ['choices' => array_flip(Utils::MOIS), 'label' => 'Mois', 'attr' => ['class' => 'has-select2']]);
        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Type de contrat',
            'class' => Communaute::class,
            'required' => false
        ]);


        return $formBuilder->getForm();
    }

    #[Route('/gestion/statistique/audiences/communaute', name: 'app_gestion_statistique_audience_communaute')]
    public function indexCommunauteAudience(Request $request, AudienceRepository $audienceRepository)
    {

        $dataDebut = $audienceRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_audience_communaute'))
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

        return $this->renderForm('/gestion/statistique/audiences/communaute.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/audiences/data_communaute', name: 'app_gestion_statistique_audience_communaute_data', condition: "request.query.has('filters')")]
    public function dataCommunauteAudience(Request $request, AudienceRepository $audienceRepository, CommunauteRepository $communauteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $communaute = $filters['communaute'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$communaute);
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }





    #[Route('/gestion/statistique/audiences/categorie', name: 'app_gestion_statistique_audience_categorie')]
    public function indexCategorieAudiences(Request $request, AudienceRepository $audienceRepository)
    {

        $dataDebut = $audienceRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_audience_categorie'))
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

        return $this->renderForm('/gestion/statistique/audiences/categorie.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/audiences/data_categorie', name: 'app_gestion_statistique_audience_categorie_data', condition: "request.query.has('filters')")]
    public function dataCategorieAudiences(Request $request, AudienceRepository $audienceRepository, CategorieRepository $categorieRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $categorie = $filters['categorie'];
        $date = $filters['date'];
        //$fin = $filters['fin'];
        //dd($date,$categorie);
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }




    #[Route('/gestion/statistique/audiences/localite', name: 'app_gestion_statistique_audience_localite')]
    public function indexLocaliteAudience(Request $request, AudienceRepository $audienceRepository)
    {

        $dataDebut = $audienceRepository->getDateDebut();
        $resultatDebut = [];

        foreach ($dataDebut as $key => $value) {
            $resultatDebut[$value['annee']] = $value['annee'];
        }
        //dd($resultatDebut);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_gestion_statistique_audience_localite'))
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

        return $this->renderForm('/gestion/statistique/audiences/localite.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/gestion/statistique/audiences/data_localite', name: 'app_gestion_statistique_audience_localite_data', condition: "request.query.has('filters')")]
    public function dataLocaliteAudience(Request $request, AudienceRepository $audienceRepository, LocaliteRepository $localiteRepository)
    {
        $all = $request->query->all();
        //  dd($all);
        $filters = $all['filters'] ?? [];
        $localite = $filters['localite'];
        $date = $filters['date'];
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
        return $this->json(['series' => $series, 'mois' => $mois]);
    }
}
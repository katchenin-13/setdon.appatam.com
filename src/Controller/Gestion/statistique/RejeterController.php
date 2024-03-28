<?php

namespace App\Controller\Gestion\statistique;

use App\Entity\Demande;
use App\Entity\Audience;
use App\Entity\Promesse;
use App\Service\ActionRender;
use Doctrine\ORM\QueryBuilder;
use App\Controller\BaseController;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RejeterController extends BaseController
{


    #[Route('/gestion/statistique/rejeter/audience', name: 'app_gestion_statistique_rejeter_audience')]
    public function indexaudience(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), 'app_gestion_statistique_rejeter_avenir');

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
                'entity' => Audience::class,
                'query' => function (QueryBuilder $req) {
                    $req->select('a,co')
                        ->from(Audience::class, 'a')
                        ->leftJoin('a.communaute', 'co')
                        ->andWhere('a.etat = :status')
                        ->setParameter('status', 'audience_initie');
                }
            ])
            ->setName('dt_app_gestion_audience');

        if ($permission != null) {
            $renders = [


                'edit' =>  new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'delete' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return false;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return false;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'show' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } else {
                        return true;
                    }
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

                                'show' => [
                                    'url' => $this->generateUrl('app_gestion_audience_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']
                                ],
                                'edit' => [
                                    'url' => $this->generateUrl('app_gestion_audience_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                                ],
                                'delete' => [
                                    'target' => '#exampleModalSizeNormal',
                                    'url' => $this->generateUrl('app_gestion_audience_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-danger'],  'render' => $renders['delete']
                                ]
                            ]

                        ];
                        return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                    }
                ]);
            }
        }



        //dd($table);
        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }


        return $this->render('gestion/statistique/rejeter/audience.html.twig', [
            'datatable' => $table,
            'permition' => $permission
        ]);
    }


    #[Route('/gestion/statistique/rejeter/demande', name: 'app_gestion_statistique_rejeter_demande')]
    public function indexdemande(Request $request, DataTableFactory $dataTableFactory): Response
    {

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), 'app_gestion_statistique_rejeter_demande');

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
                'query' => function (QueryBuilder $req) {
                    $req->select('a,co')
                        ->from(Demande::class, 'a')
                        ->leftJoin('a.communaute', 'co')
                        ->andWhere('a.etat = :status')
                        ->setParameter('status', 'demande_initie');
                }
            ])
            ->setName('dt_app_gestion_demande');

        if ($permission != null) {
            $renders = [


                'edit' =>  new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'delete' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return false;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return false;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'show' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } else {
                        return true;
                    }
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
                    'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Demande $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [

                                'show' => [
                                    'url' => $this->generateUrl('app_gestion_demande_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']
                                ],
                                'edit' => [
                                    'url' => $this->generateUrl('app_gestion_demande_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                                ],
                                'delete' => [
                                    'target' => '#exampleModalSizeNormal',
                                    'url' => $this->generateUrl('app_gestion_demande_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-danger'],  'render' => $renders['delete']
                                ]
                            ]

                        ];
                        return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                    }
                ]);
            }
        }



        //dd($table);
        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }


        return $this->render('gestion/statistique/rejeter/demande.html.twig', [
            'datatable' => $table,
            'permition' => $permission
        ]);
    }

    #[Route('/gestion/statistique/rejeter/promesse', name: 'app_gestion_statistique_rejeter_promesse')]
    public function indexannne(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), 'app_gestion_statistique_rejeter_promesse');

        $table = $dataTableFactory->create()
            ->add('dateremise', DateTimeColumn::class, [
                'label' => 'Date de réalisation',
                "format" => 'd-m-Y'
            ])
            ->add('nom', TextColumn::class, ['label' => 'Beneficiaire'])
            ->add('numero', TextColumn::class, ['label' => 'Tel du béneficiare'])
            ->add('communaute', TextColumn::class, ['label' => 'Communauté', 'field' => 'com.libelle'])
            //->add('Type', TextColumn::class, ['label' => 'Type'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Promesse::class,
                'query' => function (QueryBuilder $req) {
                    $req->select('p,co')
                        ->from(Promesse::class, 'p')
                        ->leftJoin('p.communaute', 'co')
                        ->orderBy('p.dateremise', 'DESC')
                        ->andWhere('p.etat = :status')
                        ->setParameter('status', 'promesse_rejeter');
                }
            ])
            ->setName('dt_app_gestion_promesse');

        if ($permission != null) {
            $renders = [


                'edit' =>  new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'delete' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return false;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return false;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'show' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } else {
                        return true;
                    }
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

                                'show' => [
                                    'url' => $this->generateUrl('app_gestion_promesse_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']
                                ],
                                'edit' => [
                                    'url' => $this->generateUrl('app_gestion_promesse_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                                ],
                                'delete' => [
                                    'target' => '#exampleModalSizeNormal',
                                    'url' => $this->generateUrl('app_gestion_promesse_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-danger'],  'render' => $renders['delete']
                                ]
                            ]

                        ];
                        return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                    }
                ]);
            }
        }



        //dd($table);
        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }


        return $this->render('gestion/statistique/rejeter/promesse.html.twig', [
            'datatable' => $table,
            'permition' => $permission
        ]);
    }
}

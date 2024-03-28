<?php

namespace App\Controller\Gestion;

use Dompdf\Dompdf;
use App\Entity\Promesse;
use App\Form\PromesseType;
use App\Service\FormError;
use App\Entity\Fielpromesse;
use App\Service\ActionRender;
use App\Service\StatsService;
use Doctrine\ORM\QueryBuilder;
use App\Controller\BaseController;
use App\Repository\PromesseRepository;
use App\Repository\FielpromesseRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Workflow\Exception\LogicException;
#[Route('/gestion/promesse')]
class PromesseController extends BaseController
{

    const INDEX_ROOT_NAME = 'app_gestion_promesse_index';
    
    #[Route('/acte/{id}/active/rejeter', name: 'acte_gestion_fielpromesse_rejeter', methods: ['GET'])]
    public function RejeterPromesse(Request $request, Fielpromesse $fielpromesse, FielpromesseRepository $fielpromesseRepository, FormError $formError): Response
    {

        $form = $fielpromesse;

        $data = null;
        $statutCode = Response::HTTP_OK;
        $isAjax = $request->isXmlHttpRequest();
        if ($form->getEtat() == 'fielpromesse_initie') {
            $response = [];
            $redirect = $this->generateUrl('app_gestion_promesse_index');
            $workflow = $this->workflow->get($fielpromesse, 'fielpromesse');

            try {
              
                if ($workflow->can($fielpromesse, 'rejeter')) 
                {
                    $workflow->apply($fielpromesse, 'rejeter');
                    $this->em->flush();
                }

                } catch (LogicException $e) {

                $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
            }
            $fielpromesseRepository->save($fielpromesse, true);
            $data = true;
            $message       = 'Opération effectuée avec succès';
            $statut = 1;
            $this->addFlash('success', $message);
             if ($isAjax) {
                return $this->json(compact(
                    'statut',
                    'message',
                    'redirect',
                    'data'
                ), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return  $this->json(
            [
                'code' => 200,
                'message' => 'ça marche bien',
                'etat' => $fielpromesse->getEtat(),
            ],
            200
        );
    }

    #[Route('/acte/{id}/active/valider', name: 'acte_gestion_fielpromesse_valider', methods: ['GET'])]
    public function ValiderPromesse(Request $request, Fielpromesse $fielpromesse, FielpromesseRepository $fielpromesseRepository, FormError $formError): Response
    {

        $form = $fielpromesse;

        $data = null;
        $statutCode = Response::HTTP_OK;
        $isAjax = $request->isXmlHttpRequest();
        if ($form->getEtat() == 'fielpromesse_initie') {
            $response = [];
            $redirect = $this->generateUrl('app_gestion_promesse_index');
            $workflow = $this->workflow->get($fielpromesse, 'fielpromesse');

            try {
                if ($workflow->can($fielpromesse, 'valider')) {
                    $workflow->apply($fielpromesse, 'valider');
                    $this->em->flush();
                }
            } catch (LogicException $e) {

                $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
            }
            $fielpromesseRepository->save($fielpromesse, true);
            $data = true;
            $message       = 'Opération effectuée avec succès';
            $statut = 1;
            $this->addFlash('success', $message);
            if ($isAjax) {
                return $this->json(compact(
                    'statut',
                    'message',
                    'redirect',
                    'data'
                ), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }
   
        return  $this->json(
            [
                'code' => 200,
                'message' => 'ça marche bien',
                'etat' => $fielpromesse->getEtat(),
            ],
            200
        );
    }

    #[Route('/', name: 'app_gestion_promesse_index', methods: ['GET', 'POST'])]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {
       

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

        $table = $dataTableFactory->create()
            ->add('dateremise', DateTimeColumn::class, [
                'label' => 'Date de réalisation',
                "format" => 'Y-m-d'
            ])
            ->add('nom', TextColumn::class, ['label' => 'Beneficiaire'])
            ->add('numero', TextColumn::class, ['label' => 'Tel du béneficiare'])
            ->add('communaute', TextColumn::class, ['label' => 'Communauté', 'field' => 'com.libelle'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Promesse::class,
                'query' => function (QueryBuilder $req) {
                    $req->select('c,com')
                        ->from(Promesse::class, 'c')
                        ->join('c.communaute', 'com')
                      
                    ;
                }
            ])
            ->setName('dt_app_gestion_promesse');

        if ($permission != null) {

            $renders = [

                'workflow_validation' =>  new ActionRender(function () use ($permission) {
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
                    'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Promesse $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                                'workflow_validation' => [
                                    'url' => $this->generateUrl('app_gestion_fielpromesse_workflow', ['id' => $value]), 'ajax' => true, 'icon' => '%icon%  bi bi-list', 'attrs' => ['class' => 'btn-info'], 'render' => $renders['workflow_validation']
                                ],
                                
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


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }


        return $this->render('gestion/promesse/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission
        ]);
    }

    #[Route('/new', name: 'app_gestion_promesse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PromesseRepository $promesseRepository, FormError $formError): Response
    {
        $promesse = new Promesse();
        $fielpromesse = new Fielpromesse();
        $promesse->addFielpromess($fielpromesse);

        $form = $this->createForm(PromesseType::class, $promesse, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_gestion_promesse_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_gestion_promesse_index');




            if ($form->isValid()) {

                $promesseRepository->save($promesse, true);
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('gestion/promesse/new.html.twig', [
            'promesse' => $promesse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_gestion_promesse_show', methods: ['GET'])]
    public function show(Promesse $promesse, StatsService $statsService,FielpromesseRepository $red): Response
    {
        //dd($statsService->getfielpromesse($promesse, 2));
      // dd($soro = $red->listFieldByGroup($promesse, 'en_nature')); 
        return $this->render('gestion/promesse/show.html.twig', [
            'promesse' => $promesse,
            'espece' => $statsService->getfielpromesse($promesse, 'en_espece'),
            'nature' => $statsService->getfielpromesse($promesse, 'en_nature'),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestion_promesse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promesse $promesse, PromesseRepository $promesseRepository, FormError $formError): Response
    {

        $form = $this->createForm(PromesseType::class, $promesse, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_gestion_promesse_edit', [
                'id' =>  $promesse->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_gestion_promesse_index');


            if ($form->isValid()) {

                $promesseRepository->save($promesse, true);
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('gestion/promesse/edit.html.twig', [
            'promesse' => $promesse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_gestion_promesse_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Promesse $promesse, PromesseRepository $promesseRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_gestion_promesse_delete',
                    [
                        'id' => $promesse->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $promesseRepository->remove($promesse, true);

            $redirect = $this->generateUrl('app_gestion_promesse_index');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'redirect' => $redirect,
                'data' => $data
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }
        }

        return $this->renderForm('gestion/promesse/delete.html.twig', [
            'promesse' => $promesse,
            'form' => $form,
        ]);
    }


    
  

    #[Route('/{id}/workflow/validation', name: 'app_gestion_fielpromesse_workflow', methods: ['GET', 'POST'])]
    public function workflow(Request $request,string $id, DataTableFactory $dataTableFactory, FormError $formError): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), 'app_config_fiel_index');

   
        $table = $dataTableFactory->create()
            ->add('typepromesse', TextColumn::class, ['label' => 'Type', 'field' => 'f.typepromesse'])
            ->add('motif', TextColumn::class, ['label' => 'Motif'])
            ->add('montant', TextColumn::class, ['label' => 'Montant/Valeur'])
            ->add('etat', TextColumn::class, ['label' => 'etat'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Fielpromesse::class,
                'query' => function (QueryBuilder $req) use($id) {
                    $req->select('f')
                        ->from(Fielpromesse::class, 'f')
                        ->innerJoin('f.promesse', 'p')
                        ->andWhere('p.id =:promesse ')
                        ->setParameter('promesse', $id)
                    ;
                }
            ])
            ->setName('dt_app_gestion_fielpromesse_workflow'.$id);

        $renders = [
            'valider' =>  new ActionRender(function () {
                return true;
            }),
            'rejeter' =>  new ActionRender(function () {
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
                'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Fielpromesse $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeLg2',

                        'actions' => [
                            'valider' => [
                                'url' => $this->generateUrl('acte_gestion_fielpromesse_valider', ['id' => $value]),
                                'ajax' => false,
                                'icon' => '%icon% bi bi-check-lg',
                                'attrs' => ['class' => 'btn-main validation'],
                                'render' => $renders['valider']
                               

                            ],
                            'rejeter' => [
                                'url' => $this->generateUrl('acte_gestion_fielpromesse_rejeter', ['id' => $value]),
                                'ajax' => false,
                                'icon' => '%icon%  bi bi-x-lg',
                                'attrs' => ['class' => 'btn-danger  validation'],
                                'render' => $renders['rejeter']
                                
                            ],
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
        return $this->renderForm('gestion/promesse/workflow.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            'id' => $id,
           
        ]);




    }
}

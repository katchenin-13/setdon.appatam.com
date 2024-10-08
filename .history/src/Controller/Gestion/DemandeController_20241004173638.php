<?php

namespace App\Controller\Gestion;

use Mpdf\MpdfException;
use App\Entity\Denande;
use App\Form\demandeType;
use App\Service\FormError;
use App\Service\PdfService;
use App\Service\ActionRender;
use App\Service\StatsService;
use App\Form\JutificationType;
use Doctrine\ORM\QueryBuilder;
use App\Controller\BaseController;
use ContainerV1HJB67\getEventService;
use App\Form\JutificationdemandeType;
use App\Repository\demandeRepository;
use Symfony\Component\Workflow\Registry;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use App\Repository\ModuleGroupePermitionRepository;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Workflow\Exception\LogicException;

#[Route('/gestion/denande')]
class demandeController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_config_demande_ls';

    /**
     * Cette fonction permet de generer un pdf(reporting de sur audiance)
     * @param demandeRepository $denande
     * @return Response
     */
    #[Route('/pdf/generator', name: 'app_pdf_generator')]
    public function generatePdf(demandeRepository $denande): Response
    {
        $data = $denande->findAll();

        $html =  $this->renderView('gestion/denande/detail.html.twig', [
            'data' => $data
        ]);


        $mpdf = new \Mpdf\Mpdf([

            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L'
        ]);
        $mpdf->PageNumSubstitutions[] = [
            'from' => 1,
            'reset' => 0,
            'type' => 'I',
            'suppress' => 'on'
        ];

        $mpdf->WriteHTML($html);
        $mpdf->SetFontSize(6);
        $mpdf->Output();
    }

    #[Route('/{etat}/liste', name: 'app_config_demande_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        if ($etat == 'demande_initie') {
            $titre = "Denandes en Attentes de validation";
        } elseif ($etat == 'demande_valider') {
            $titre = "demandes acceptée ";
        } elseif ($etat == 'demande_rejeter') {
            $titre = "La liste des blacklistes";
        }
        $table = $dataTableFactory->create()
            ->add('daterencontre', DateTimeColumn::class, [
                'label' => 'Date de la rencontre',
                "format" => 'd-m-Y'
            ])
            ->add('communaute', TextColumn::class, ['label' => 'Communauté', 'field' => 'co.libelle'])
            ->add('nomchef', TextColumn::class, ['label' => 'Nom du chef'])
            ->add('numero', TextColumn::class, ['label' => 'Numéro'])
            ->add('motif', TextColumn::class, ['label' => 'Motif'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => App\Controller\Gestion\Denande::class,
                'query' => function (QueryBuilder $req) use ($etat) {
                    $req->select('a,co')
                        ->from(Denande::class, 'a')
                        ->leftJoin('a.communaute', 'co');
                    if ($etat == 'demande_initie') {
                        $req->andWhere("a.etat =:etat")
                            ->setParameter('etat', "demande_initie");
                    } elseif ($etat == 'demande_valider') {
                        $req->andWhere("a.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    } elseif ($etat == 'demande_rejeter') {
                        $req->andWhere("a.etat =:etat")
                            ->setParameter('etat', "demande_rejeter");
                    }
                }
            ])
            ->setName('dt_app_config_denande_' . $etat);

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
                    'label' => 'Actions',
                    'orderable' => false,
                    'globalSearchable' => false,
                    'className' => 'grid_row_actions',
                    'render' => function ($value, Denande $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                                'workflow_validation' => [
                                    'url' => $this->generateUrl('app_gestion_demande_workflow', ['id' => $value]),
                                    'ajax' => true,
                                    'icon' => '%icon%  bi bi-arrow-repeat',
                                    'attrs' => ['class' => 'btn-danger'],
                                    'render' => $renders['workflow_validation']
                                ],
                                'show' => [
                                    'url' => $this->generateUrl('app_gestion_demande_show', ['id' => $value]),
                                    'ajax' => true,
                                    'icon' => '%icon% bi bi-eye',
                                    'attrs' => ['class' => 'btn-success'],
                                    'render' => $renders['show']
                                ],
                                'edit' => [
                                    'url' => $this->generateUrl('app_gestion_demande_edit', ['id' => $value]),
                                    'ajax' => true,
                                    'icon' => '%icon% bi bi-pen',
                                    'attrs' => ['class' => 'btn-default'],
                                    'render' => $renders['edit']
                                ],
                                'delete' => [
                                    'target' => '#exampleModalSizeNormal',
                                    'url' => $this->generateUrl('app_gestion_demande_delete', ['id' => $value]),
                                    'ajax' => true,
                                    'icon' => '%icon% bi bi-trash',
                                    'attrs' => ['class' => 'btn-danger'],
                                    'render' => $renders['delete']
                                ]

                            ]

                        ];
                        return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                    }
                ]);
            }
        }





        $table->handleRequest($request);
        if ($table->isCallback() == true) {
            return $table->getResponse();
        }


        return $this->render('gestion/denande/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            'etat' => $etat,
            'titre' => $titre
        ]);
    }

    #[Route('/new', name: 'app_gestion_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, demandeRepository $demandeRepository, FormError $formError): Response
    {
        $denande = new Denande();
        $form = $this->createForm(demandeType::class, $denande, [
            'method' => 'POST',
            'etat' => 'create',
            'action' => $this->generateUrl('app_gestion_demande_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_demande_index');




            if ($form->isValid()) {

                $demandeRepository->save($denande, true);
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

        return $this->renderForm('gestion/denande/new.html.twig', [
            'denande' => $denande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_gestion_demande_show', methods: ['GET'])]
    public function show(Denande $denande): Response
    {
        return $this->render('gestion/denande/show.html.twig', [
            'denande' => $denande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestion_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Denande $denande, demandeRepository $demandeRepository, FormError $formError): Response
    {
        $form = $this->createForm(demandeType::class, $denande, [
            'method' => 'POST',
            'etat' => 'create',
            'action' => $this->generateUrl('app_gestion_demande_edit', [
                'id' =>  $denande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_demande_index');


            if ($form->isValid()) {

                $demandeRepository->save($denande, true);
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

        return $this->renderForm('gestion/denande/edit.html.twig', [
            'denande' => $denande,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/gestion/denande/tableau', name: 'app_gestion_demande_justification', methods: ['GET', 'POST'])]
    public function justification(Request $request, Denande $denande, demandeRepository $demandeRepository, FormError $formError): Response
    {
        $form = $this->createForm(JutificationdemandeType::class, $denande, [
            'method' => 'POST',
            'type' => 'create',
            'etat' => 'create',
            'action' => $this->generateUrl('app_gestion_demande_justification', [
                'id' =>  $denande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_demande_index');


            if ($form->isValid()) {

                $denande->setEtat('demande_rejeter');
                $demandeRepository->save($denande, true);
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

        return $this->renderForm('gestion/denande/jutification.html.twig', [
            'denande' => $denande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_gestion_demande_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Denande $denande, demandeRepository $demandeRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_gestion_demande_delete',
                    [
                        'id' => $denande->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $demandeRepository->remove($denande, true);

            $redirect = $this->generateUrl('app_config_demande_index');

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

        return $this->renderForm('gestion/denande/delete.html.twig', [
            'denande' => $denande,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/workflow/validation', name: 'app_gestion_demande_workflow', methods: ['GET', 'POST'])]
    public function workflow(Request $request, Denande $denande, demandeRepository $demandeRepository, FormError $formError): Response
    {
        $etat =  $denande->getEtat();
        $form = $this->createForm(demandeType::class, $denande, [
            'method' => 'POST',
            'etat' => $etat,
            'action' => $this->generateUrl('app_gestion_demande_workflow', [
                'id' =>  $denande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_demande_index');
            $workflow = $this->workflow->get($denande, 'denande');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'accorder') {
                    try {
                        if ($workflow->can($denande, 'valider')) {
                            $workflow->apply($denande, 'valider');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($denande, true);
                } else {
                    $demandeRepository->save($denande, true);
                }

                if ($form->getClickedButton()->getName() === 'rejeter') {
                    try {
                        if ($workflow->can($denande, 'rejeter')) {
                            $workflow->apply($denande, 'rejeter');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($denande, true);
                } else {
                    $demandeRepository->save($denande, true);
                }

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

        return $this->renderForm('gestion/denande/workflow.html.twig', [
            'denande' => $denande,
            'form' => $form,
            'id' => $denande->getId(),
            'etat' => json_encode($etat)
        ]);
    }
}

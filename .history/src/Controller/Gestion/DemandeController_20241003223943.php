<?php

namespace App\Controller\Gestion;

use App\Controller\BaseController;
use App\Entity\Demande;
use App\Form\DemandeType;
use App\Form\JutificationDemandeType;
use App\Form\JutificationType;
use App\Repository\DemandeRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Dompdf\Dompdf;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Mpdf\MpdfException;
use Symfony\Component\Workflow\Exception\LogicException;

#[Route('/gestion/demande')]
class DemandeController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_config_demande_ls';
    /**
     * @Route("/acte/{id}/active", name="acte_demande", methods={"GET"})
     * @param $id
     * @param Acte $parentf
     * @param EntityManagerInterface $entityManager
     * @return Response
     */




    /**
     * Cette fonction permet de generer un pdf de demande
     *
     * @param DemandeRepository $demande
     * @return Response
     */
    #[Route('/pdf/generator/demande', name: 'app_pdf_generator_demande')]
    public function generatePdf(DemandeRepository $demande): Response
    {
        $data = $demande->findAll();
        $html =  $this->renderView('gestion/demande/detail.html.twig', [
            'data' => $data
        ]);
        $mpdf = new \Mpdf\Mpdf([

            'mode' => 'utf-8', 'format' => 'A4-L', 'orientation' => 'L'
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
    public function index(Request $request, DataTableFactory $dataTableFactory, string $etat): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        if ($etat == 'demande_initie') {
            $titre = "demandes en Attentes de validation";
        } elseif ($etat == 'demande_valider') {
            $titre = "Audiences accordées ";
        } elseif ($etat == 'demande_rejeter') {
            $titre = "La liste des blacklistes";
        }

        $table = $dataTableFactory->create()

            ->add('daterencontre', DateTimeColumn::class, [
                'label' => 'Date de la rencontre',
                "format" => 'd-m-Y'
            ])
            ->add('communaute', TextColumn::class, ['label' => 'Communauté', 'field' => 'co.libelle'])
            ->add('nom', TextColumn::class, ['label' => 'Nom et Prénom(s)'])
            ->add('lieu_habitation', TextColumn::class, ['label' => 'Village/Ville'])
            ->add('numero', TextColumn::class, ['label' => 'Numéro'])
            ->add('motif', TextColumn::class, ['label' => 'Motif'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function (QueryBuilder $req) use ($etat) {
                    $req->select('d,co')
                        ->from(Demande::class, 'd')
                        ->join('d.communaute', 'co');
                    if ($etat == 'demande_initie') {
                        $req->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_initie");
                    } elseif ($etat == 'demande_valider') {
                        $req->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    } elseif ($etat == 'demande_rejeter') {
                        $req->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_rejeter");
                    }
                }

            ])
            ->setName('dt_app_config_demande_' . $etat);

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
                    'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Demande $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                                'workflow_validation' => [
                                    'url' => $this->generateUrl('app_gestion_demande_workflow', ['id' => $value]), 'ajax' => true, 'icon' => '%icon%  bi bi-arrow-repeat', 'attrs' => ['class' => 'btn-danger'], 'render' => $renders['workflow_validation']
                                ],
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






        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }


        return $this->render('gestion/demande/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            'etat' => $etat,
            'titre' => $titre,
        ]);
    }

    #[Route('/new', name: 'app_gestion_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeRepository $demandeRepository, FormError $formError): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande, [
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
            $redirect = $this->generateUrl('app_config_audience_index');




            if ($form->isValid()) {

                $demandeRepository->save($demande, true);
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

        return $this->renderForm('gestion/demande/new.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_gestion_demande_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        return $this->render('gestion/demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestion_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, DemandeRepository $demandeRepository, FormError $formError): Response
    {

        $form = $this->createForm(DemandeType::class, $demande, [
            'method' => 'POST',
            'etat' => 'create',
            'action' => $this->generateUrl('app_gestion_demande_edit', [
                'id' =>  $demande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_audience_index');


            if ($form->isValid()) {

                $demandeRepository->save($demande, true);
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

        return $this->renderForm('gestion/demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/gestion/demande/tableau', name: 'app_gestion_demande_justification', methods: ['GET', 'POST'])]
    public function justification(Request $request, Demande $demande, DemandeRepository $demandeRepository, FormError $formError): Response
    {

        $form = $this->createForm(JutificationDemandeType::class, $demande, [
            'method' => 'POST',
            'type' => 'create',
            'etat' => 'create',
            'action' => $this->generateUrl('app_gestion_demande_justification', [
                'id' =>  $demande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_audience_index');


            if ($form->isValid()) {
               $demande->setEtat('demande_rejeter');
                $demandeRepository->save($demande, true);
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

        return $this->renderForm('gestion/demande/jutification.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_gestion_demande_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Demande $demande, DemandeRepository $demandeRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_gestion_demande_delete',
                    [
                        'id' => $demande->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $demandeRepository->remove($demande, true);

            $redirect = $this->generateUrl('app_config_audience_index');

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
        return $this->renderForm('gestion/demande/delete.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/workflow/validation', name: 'app_gestion_demande_workflow', methods: ['GET', 'POST'])]
    public function workflow(Request $request, Demande $demande, DemandeRepository $demandeRepository, FormError $formError): Response
    {

        $etat = $demande->getEtat();
        $form = $this->createForm(DemandeType::class, $demande, [
            'method' => 'POST',
            'etat' => $etat,
            'action' => $this->generateUrl('app_gestion_demande_workflow', [
                'id' =>  $demande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_audience_index');
            $workflow = $this->workflow->get($demande, 'demande');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'accorder') {
                    try {
                        if ($workflow->can($demande, 'valider')) {
                            $workflow->apply($demande, 'valider');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                } else {
                    $demandeRepository->save($demande, true);
                }

                if ($form->getClickedButton()->getName() === 'rejeter') {
                    try {
                        if ($workflow->can($demande, 'rejeter')) {
                            $workflow->apply($demande, 'rejeter');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                } else {
                    $demandeRepository->save($demande, true);
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

        return $this->renderForm('gestion/demande/workflow.html.twig', [
            'demande' => $demande,
            'form' => $form,
            
        ]);
    }
}

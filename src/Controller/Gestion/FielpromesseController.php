<?php

namespace App\Controller\Gestion;

use App\Controller\BaseController;
use App\Entity\Fieldon;
use App\Service\FormError;
use App\Entity\Fielpromesse;
use App\Service\ActionRender;
use App\Form\FielpromesseType;
use Doctrine\ORM\QueryBuilder;
use App\Repository\FielpromesseRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\workflow;

#[Route('/gestion/fielpromesse')]
class FielpromesseController extends BaseController
{

     const INDEX_ROOT_NAME = 'app_config_fielpromesse_ls';

    #[Route('/{etat}/liste', name: 'app_config_fielpromesse_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        if ($etat == 'fielpromesse_initie') {
            $titre = "Promesse en Attentes de validation";
        } elseif ($etat == 'fielpromesse_valider') {
            $titre = "Promesses éffectuées ";
        } elseif ($etat == 'fielpromesse_rejeter') {
            $titre = "La liste des blacklistes";
        }
        $table = $dataTableFactory->create()
            ->add('typepromesse', TextColumn::class, ['label' => 'Type', 'field' => 'f.typepromesse'])
            ->add('motif', TextColumn::class, ['label' => 'Motif'])
            ->add('montant', TextColumn::class, ['label' => 'Montant/Valeur'])

            ->createAdapter(ORMAdapter::class, [
                'entity' => Fielpromesse::class,
                'query' => function (QueryBuilder $req) use ($etat) {
                    $req->select('f')
                        ->from(Fielpromesse::class, 'f');

                    if ($etat == 'fielpromesse_initie') {
                        $req->andWhere("f.etat =:etat")
                        ->setParameter('etat', "fielpromesse_initie");
                    } elseif ($etat == 'fielpromesse_valider') {
                        $req->andWhere("f.etat =:etat")
                        ->setParameter('etat', "fielpromesse_valider");
                    } elseif ($etat == 'fielpromesse_rejeter') {
                        $req->andWhere("f.etat =:etat")
                        ->setParameter('etat', "fielpromesse_rejeter");
                    }
                }
            ])
            ->setName('dt_app_config_fielpromesse_' . $etat);

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
                    'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Fielpromesse $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [

                                'show' => [
                                    'url' => $this->generateUrl('app_gestion_fielpromesse_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['show']
                                ],
                                'edit' => [
                                    'url' => $this->generateUrl('app_gestion_fielpromesse_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                                ],
                                'delete' => [
                                    'target' => '#exampleModalSizeNormal',
                                    'url' => $this->generateUrl('app_gestion_fielpromesse_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-danger'],  'render' => $renders['delete']
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


        return $this->render('gestion/fielpromesse/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            'etat' => $etat,

        ]);
    } 

    #[Route('/new', name: 'app_gestion_fielpromesse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FielpromesseRepository $fielpromesseRepository, FormError $formError): Response
    {
        $fielpromesse = new Fielpromesse();
        $form = $this->createForm(FielpromesseType::class, $fielpromesse, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_gestion_fielpromesse_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_fiel_index');




            if ($form->isValid()) {

                $fielpromesseRepository->save($fielpromesse, true);
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
                 return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }


        }

        return $this->renderForm('gestion/fielpromesse/new.html.twig', [
            'fielpromesse' => $fielpromesse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_gestion_fielpromesse_show', methods: ['GET'])]
    public function show(Fielpromesse $fielpromesse): Response
    {
        return $this->render('gestion/fielpromesse/show.html.twig', [
            'fielpromesse' => $fielpromesse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestion_fielpromesse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fielpromesse $fielpromesse, FielpromesseRepository $fielpromesseRepository, FormError $formError): Response
    {

        $form = $this->createForm(FielpromesseType::class, $fielpromesse, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_gestion_fielpromesse_edit', [
                    'id' =>  $fielpromesse->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_fiel_index');


            if ($form->isValid()) {

                $fielpromesseRepository->save($fielpromesse, true);
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
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('gestion/fielpromesse/edit.html.twig', [
            'fielpromesse' => $fielpromesse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_gestion_fielpromesse_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Fielpromesse $fielpromesse, FielpromesseRepository $fielpromesseRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                'app_gestion_fielpromesse_delete'
                ,   [
                        'id' => $fielpromesse->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $fielpromesseRepository->remove($fielpromesse, true);

            $redirect = $this->generateUrl('app_config_fiel_index');

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

        return $this->renderForm('gestion/fielpromesse/delete.html.twig', [
            'fielpromesse' => $fielpromesse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/workflow/validation', name: 'app_gestion_fielpromesse_workflow', methods: ['GET', 'POST'])]
    public function workflow(Request $request, Fielpromesse $fielpromesse, FielpromesseRepository $fielpromesseRepository, FormError $formError): Response
    {

        $form = $this->createForm(FielpromesseType::class, $fielpromesse, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_gestion_fielpromesse_workflow', [
                'id' =>  $fielpromesse->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_fiel_index');
            $workflow = $this->workflow->get($fielpromesse, 'fielpromesse');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'valider') {
                    try {
                        if ($workflow->can($fielpromesse, 'valider')) {
                            $workflow->apply($fielpromesse, 'valider');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $fielpromesseRepository->save($fielpromesse, true);
                } else {
                    $fielpromesseRepository->save($fielpromesse, true);
                }

                if ($form->getClickedButton()->getName() === 'rejeter') {
                    try {
                        if ($workflow->can($fielpromesse, 'rejeter')) {
                            $workflow->apply($fielpromesse, 'rejeter');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $fielpromesseRepository->save($fielpromesse, true);
                } else {
                    $fielpromesseRepository->save($fielpromesse, true);
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
                return $this->json(compact('statut', 'message', 'redirect',
                    'data'
                ), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('gestion/fielpromesse/workflow.html.twig', [
            'fielpromesse' => $fielpromesse,
            'form' => $form,
        ]);
    }
}

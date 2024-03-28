<?php

namespace App\Controller\Gestion;

use Dompdf\Dompdf;
use App\Entity\Mission;
use Mpdf\MpdfException;
use App\Service\FormError;
use App\Service\ActionRender;
use App\Entity\Missionrapport;
use App\Entity\Rapportmission;
use App\Form\JutificationType;
use Doctrine\ORM\QueryBuilder;
use App\Form\MissionrapportType;
use App\Controller\BaseController;
use App\Form\JutificationMissionType;
use App\Repository\MissionRepository;
use App\Repository\MissionrapportRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

#[Route('/gestion/missionrapport')]
class MissionrapportController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_config_mission_index';

    /**
     * Undocumented function
     *cette fonction permet de generer un pdf de rapport de mission
     * @param MissionrapportRepository $missionrapportRepository
     * @return Response
     */
    #[Route('/pdf/generator/rapportmission', name: 'app_pdf_generator_rapportmision')]
    public function generatePdf( MissionrapportRepository $missionrapportRepository): Response
    {
        $data = $missionrapportRepository->findAll();
        $html =  $this->renderView('gestion/missionrapport/detail.html.twig', [
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

    #[Route('/{etat}/liste', name: 'app_config_mission_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        //dd($permission);
        if ($etat == 'missionrapport_initie') {
            $titre = "rapport de mission en Attentes de validation";
        }elseif($etat == 'missionrapport_attend') {
            $titre = "rapport de mission en Attentes de validation";
        } elseif ($etat == 'missionrapport_valider') {
            $titre = "rapport de mission acceptée ";
        } elseif ($etat == 'missionrapport_rejeter') {
            $titre = "La liste des blacklistes";
        }
        //dd($etat);
        $table = $dataTableFactory->create()
            ->add('titre_mission',TextColumn::class, ['label' => 'Titre de la mission'])
            ->add('employe',TextColumn::class, ['label' => 'Chef de Mission/Répresentant', 'field' => 'e.nom'])
            // ->add('nombrepersonne')
            // ->add('communaute')
            // ->add('objectifs')
            // ->add('action')
            // ->add('opportunite')
            // ->add('prochaineetat')
          
            // ->add('libelle', TextColumn::class, )
          //->add('objectif', TextColumn::class, ['label' => 'Objectif (s) de la mission', 'field' => 'm.objectif'])

            // ->add('mission')
            // ->add('opportunite'
            // ->add('difficulte')
            // ->add('prochaineetape')
            // ->add('action', TextColumn::class, ['label' => 'Action réalisée(s) sur la mission'])
            // ->add('opportunite', TextColumn::class, ['label' => ' Opportunité(s) de la mission'])
            // ->add('difficulte', TextColumn::class, ['label' => 'Difficulté(s) de la mission'])
            ->add('communaute', TextColumn::class, ['label' => 'Communauté', 'field' => 'co.libelle'])
            ->add('prochaineetat', TextColumn::class, ['label' => 'Prochainé étape'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Missionrapport::class,
                'query' => function (QueryBuilder $req) use ($etat) {
                    $req->select('r,co')
                    ->from(Missionrapport::class, 'r')
                        ->leftJoin('r.communaute', 'co')
                        ->leftJoin('r.employe','e')
                        ->innerJoin('r.utilisateur','u')
                        ->andWhere('u =:user or e =:user2')
                        ->setParameter('user2', $this->getUser()->getEmploye())
                        ->setParameter('user',$this->getUser())
                        ;
                

                    if ($etat == 'missionrapport_initie') {
                        $req->andWhere("r.etat =:etat")
                        ->setParameter('etat', "missionrapport_initie");
                     }elseif ($etat == 'missionrapport_attend') {
                        $req->andWhere("r.etat =:etat")
                        ->setParameter('etat', "missionrapport_attend");
                    } elseif ($etat == 'missionrapport_valider') {
                        $req->andWhere("r.etat =:etat")
                        ->setParameter('etat', "missionrapport_valider");
                    } elseif ($etat == 'missionrapport_rejeter') {
                    $req->andWhere("r.etat =:etat")
                        ->setParameter('etat', "missionrapport_rejeter");
               
                   }
                } 
            ])
            ->setName('dt_app_config_mission_' . $etat);

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
                    'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Missionrapport $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                                'workflow_validation' => [
                                    'url' => $this->generateUrl('app_gestion_mission_rapport_workflow', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-arrow-repeat', 'attrs' => ['class' => 'btn-danger workflow'], 'render' => $renders['workflow_validation']
                                ],
                                'show' => [
                                    'url' => $this->generateUrl('app_gestion_mission_rapport_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']
                                ],
                                'edit' => [
                                    'url' => $this->generateUrl('app_gestion_mission_rapport_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                                ],
                                'delete' => [
                                    'target' => '#exampleModalSizeNormal',
                                    'url' => $this->generateUrl('app_gestion_mission_rapport_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-danger'],  'render' => $renders['delete']
                                ]
                            ]

                        ];
                        return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                    }
                ]);
            }
        }





        $table->handleRequest($request);
        //dd($table->isCallback());
        if ($table->isCallback() == true) {
            return $table->getResponse();
        }


        return $this->render('gestion/missionrapport/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            'titre' => $titre,
            'etat' => $etat,
            'valide' => json_encode($etat)
        ]);
    }
    #[Route('/new', name: 'app_gestion_mission_rapport_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MissionrapportRepository $missionrapportRepository, FormError $formError): Response
    {
        $missionrapport = new Missionrapport();
        $form = $this->createForm(MissionrapportType::class, $missionrapport, [
            'method' => 'POST',
            'type' => 'create',
            'etat'=>'create' ,
            'action' => $this->generateUrl('app_gestion_mission_rapport_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_mission_index');




            if ($form->isValid()) {

                $missionrapportRepository->save($missionrapport, true);
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

        return $this->renderForm('gestion/missionrapport/new.html.twig', [
            'missionrapport' => $missionrapport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_gestion_mission_rapport_show', methods: ['GET'])]
    public function show(Missionrapport $missionrapport): Response
    {
        return $this->render('gestion/missionrapport/show.html.twig', [
            'missionrapport' => $missionrapport,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestion_mission_rapport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Missionrapport $missionrapport, MissionrapportRepository $missionrapportRepository, FormError $formError): Response
    {

        $form = $this->createForm(MissionrapportType::class, $missionrapport, [
            'method' => 'POST',
            'type' => 'create',
            'etat' => 'create',
            'action' => $this->generateUrl('app_gestion_mission_rapport_edit', [
                'id' =>  $missionrapport->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_mission_index');


            if ($form->isValid()) {

                $missionrapportRepository->save($missionrapport, true);
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

        return $this->renderForm('gestion/missionrapport/edit.html.twig', [
            'misionrapport' => $missionrapport,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/gestion/missionrapport/tableau', name: 'app_gestion_mission_rapport_justification', methods: ['GET', 'POST'])]
    public function justification(Request $request, Missionrapport $missionrapport, MissionrapportRepository $missionrapportRepository, FormError $formError): Response
    {
       

        $form = $this->createForm(JutificationMissionType::class, $missionrapport, [
            'method' => 'POST',
            'type' => 'create',
            'etat' => 'create',
            'action' => $this->generateUrl('app_gestion_mission_rapport_justification', [
                'id' =>  $missionrapport->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_mission_index');


            if ($form->isValid()) {

                $missionrapport->setEtat('missionrapport_rejeter');
                $missionrapportRepository->save($missionrapport, true);
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

        return $this->renderForm('gestion/missionrapport/jutification.html.twig', [
            'misionrapport' => $missionrapport,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}/delete', name: 'app_gestion_mission_rapport_delete', methods: ['DELETE', 'GET'])]

    public function delete(Request $request, Missionrapport $missionrapport, MissionrapportRepository $missionrapportRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_gestion_mission_rapport_delete',
                    [
                        'id' => $missionrapport->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $missionrapportRepository->remove($missionrapport, true);

            $redirect = $this->generateUrl('app_config_mission_index');

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

        return $this->renderForm('gestion/missionrapport/delete.html.twig', [
            'missionrapport' => $missionrapport,
            'form' => $form,
        ]);
    }


    #[Route('/gestion/missionrapport/data_justification', name: 'app_gestion_mission_rapport_justification_data', condition: "request.query.has('filters')", methods: ['GET', 'POST'])]
    public function datadashbordMissionRapport(Request $request, MissionrapportRepository $missionrapportRepository)
    {
        $all = $request->query->all();
        if ($request->isXmlHttpRequest()) {
            dump($request->request->get('justification'));
            die();
        }
        $filters = $all['filters'] ?? [];

       /// dd($all);

        $communaute = $filters['justification'];
        dd($communaute);
        $date = $filters['date'];
        $data = $missionrapportRepository->getMissionRapportEtCommunauteTableauMission($date, $communaute);
   
        
        return $this->json([
            'data' => $series,
            'compteAudience' => $dataCompte
        ]);
    }

    #[Route('/{id}/workflow/validation', name: 'app_gestion_mission_rapport_workflow', methods: ['GET', 'POST'])]
    public function workflow(Request $request, Missionrapport $missionrapport, MissionrapportRepository $missionrapportRepository, FormError $formError): Response
    {
       $etat= $missionrapport->getEtat();
       $id = $missionrapport->getId();

        if ($etat == 'missionrapport_initie') {
            $titre = "rapport de mission en Attentes de validation";
        } elseif ($etat == 'missionrapport_attend') {
            $titre = "rapport de mission en Attentes de validation";
        } elseif ($etat == 'missionrapport_valider') {
            $titre = "rapport de mission acceptée ";
        } elseif ($etat == 'missionrapport_rejeter') {
            $titre = "La liste des blacklistes";
        }

       
        $form = $this->createForm(MissionrapportType::class, $missionrapport, [
            'method' => 'POST',
            'type' => 'worklflow', 
            'etat'=> $etat,
            'action' => $this->generateUrl('app_gestion_mission_rapport_workflow', [
                'id' =>  $missionrapport->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_mission_index');
            $workflow = $this->workflow->get($missionrapport, 'missionrapport');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'accorder') {
                    try {
                        if ($workflow->can($missionrapport, 'valider')) {
                            $workflow->apply($missionrapport, 'valider');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {
                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    try {
                        if ($workflow->can($missionrapport, 'attend')) {
                            $workflow->apply($missionrapport, 'attend');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                  
                    try {
                        if ($workflow->can($missionrapport, 'retour')) {
                            $workflow->apply($missionrapport, 'retour');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $missionrapportRepository->save($missionrapport, true);
                } else {
                    $missionrapportRepository->save($missionrapport, true);
                }
                if ($form->getClickedButton()->getName() === 'rejeter') {
                    try {
                        if ($workflow->can($missionrapport, 'rejeter')) {
                            $workflow->apply($missionrapport, 'rejeter');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $missionrapportRepository->save($missionrapport, true);
                } else {
                    $missionrapportRepository->save($missionrapport, true);
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
       // dd(json_encode($etat));
        return $this->renderForm('gestion/missionrapport/workflow.html.twig', [
            'missionrapport' => $missionrapport,
            'id'=>$missionrapport->getId(),
            'form' => $form,
             'etat'=> $etat
        ]);
       
    }
}


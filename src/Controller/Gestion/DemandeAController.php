<?php

namespace App\Controller\Gestion;

use App\Controller\BaseController;
use App\Entity\Demande;
use App\Form\DemandeType;
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

#[Route('/gestion/demandeavenir')]
class DemandeAController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_gestion_demandeavenir_index';
    /**
     * @Route("/acte/{id}/active", name="acte_demande", methods={"GET"})
     * @param $id
     * @param Acte $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
/*    public function active(Demande $parent, EntityManagerInterface $entityManager): Response
    {

        //  dd($parent);
        if ($parent->isStatusdemande() == true) {

            $parent->setStatusdemande(false);
        } else {

            $parent->setStatusdemande(true);
        }
        // if ($parent->isMentions() == true) {

        //     $parent->setMentions(false);
        // } else {

        //     $parent->setMentions(true);
        // }

        $entityManager->persist($parent);
        $entityManager->flush();
        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'active' => $parent->isStatusdemande(),
            // 'mention' =>$parent->isMentions(),
        ], 200);
    }



    /**
     * Cette fonction permet de generer un pdf de demande
     *
     * @param DemandeRepository $demande
     * @return Response
     */
    /*
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
*/

    #[Route('/', name: 'app_gestion_demandeavenir_index', methods: ['GET', 'POST'])]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

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
                'query' => function (QueryBuilder $req) {
                    $req->select('d,co')
                        ->from(Demande::class, 'd')
                        ->join('d.communaute', 'co')
                        ->Where("CURRENT_DATE() <= d.daterencontre ")
                        ->andWhere('d.etat = :status')
                        ->setParameter('status', 'demande_valider');
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






        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }


        return $this->render('gestion/demandeAvenir/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission
        ]);
    }

    #[Route('/new', name: 'app_gestion_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeRepository $demandeRepository, FormError $formError): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande, [
            'method' => 'POST',
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

        return $this->renderForm('gestion/demandeAvenir/new.html.twig', [
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
            $redirect = $this->generateUrl('app_config_demande_index');


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

        return $this->renderForm('gestion/demande/delete.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }
}

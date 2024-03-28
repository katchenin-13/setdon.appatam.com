<?php

namespace App\Controller\Gestion;

use App\Controller\BaseController;
use App\Entity\Audience;
use App\Form\AudienceType;
use App\Service\FormError;
use App\Service\ActionRender;
use Doctrine\ORM\QueryBuilder;
use App\Repository\AudienceRepository;
use App\Repository\ModuleGroupePermitionRepository;
use App\Service\Menu;
use App\Service\PdfService;
use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Length;
use Mpdf\MpdfException;

#[Route('/gestion/audienceavenir')]
class AudienceAController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_gestion_audienceaveni_index';
    /**
     * @Route("/acte/{id}/active", name="acte_audience", methods={"GET"})
     * @param $id
     * @param Acte $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active(Audience $parent, EntityManagerInterface $entityManager): Response
    {

        //  dd($parent);
        if ($parent->isStatusaudience() == true) {

            $parent->setStatusaudience(false);
        } else {

            $parent->setStatusaudience(true);
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
            'active' => $parent->isStatusaudience(),
            // 'mention' =>$parent->isMentions(),
        ], 200);
    }

    /**
     * Cette fonction permet de generer un pdf(reporting de sur audiance)
     * @param AudienceRepository $audience
     * @return Response
     */

     // cette fonction permet de generer un pdf 
    #[Route('/pdf/generator', name: 'app_pdf_generator')]
    public function generatePdf(AudienceRepository $audience): Response
    {
        $data = $audience->findAll();

        $html =  $this->renderView('gestion/audience/detail.html.twig', [
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



    #[Route('/pdf', name: 'app_pdt_generator')]
    public function generatePdfPersonne(AudienceRepository $audience, PdfService $pdf)
    {
        $audiences = $audience->findAll();
        //dd($audiences);
        $html = $this->render('pdf_generator/pdt.html.twig', ['audiences' => $audiences]);
        $pdf->showPdfFile($html);

        // $html = $this->render('personne/detail.html.twig', ['personne' => $personne]);
        // $pdf->showPdfFile($html);
    }




    #[Route('/', name: 'app_gestion_audienceaveni_index', methods: ['GET', 'POST'])]
    public function index(Request $request, DataTableFactory $dataTableFactory, StatsService $statsService): Response
    {


        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

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
                'entity' => Audience::class,
                'query' => function (QueryBuilder $req) {
                    $req->select('a,co')
                        ->from(Audience::class, 'a')
                        ->leftJoin('a.communaute', 'co')
                        ->Where("CURRENT_DATE() <= a.daterencontre ")
                        ->andWhere('a.etat = :status')
                        ->setParameter('status', 'audience_valider');
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


        return $this->render('gestion/audienceAvenir/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission
        ]);
    }

    #[Route('/new', name: 'app_gestion_audience_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AudienceRepository $audienceRepository, FormError $formError): Response
    {
       
        $audience = new Audience();
        $form = $this->createForm(AudienceType::class, $audience, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_gestion_audience_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_audience_index');




            if ($form->isValid()) {

                $audienceRepository->save($audience, true);
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

        return $this->renderForm('gestion/audience/new.html.twig', [
            'audience' => $audience,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_gestion_audience_show', methods: ['GET'])]
    public function show(Audience $audience): Response
    {
        return $this->render('gestion/audience/show.html.twig', [
            'audience' => $audience,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestion_audience_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Audience $audience, AudienceRepository $audienceRepository, FormError $formError): Response
    {

        $form = $this->createForm(AudienceType::class, $audience, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_gestion_audience_edit', [
                'id' =>  $audience->getId()
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

                $audienceRepository->save($audience, true);
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

        return $this->renderForm('gestion/audience/edit.html.twig', [
            'audience' => $audience,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_gestion_audience_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Audience $audience, AudienceRepository $audienceRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_gestion_audience_delete',
                    [
                        'id' => $audience->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $audienceRepository->remove($audience, true);

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

        return $this->renderForm('gestion/audience/delete.html.twig', [
            'audience' => $audience,
            'form' => $form,
        ]);
    }
}

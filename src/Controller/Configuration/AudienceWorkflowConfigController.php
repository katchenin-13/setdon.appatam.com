<?php

namespace App\Controller\Configuration;

use App\Controller\BaseController;
use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/config/workflow')]
class AudienceWorkflowConfigController extends BaseController
{
    private const MODULE_NAME = 'Audience ';
    const INDEX_ROOT_NAME = 'app_config_audience_index';


    #[Route(path: '/', name: 'app_config_audience_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {


        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        $modules = [
            [
                'label' => 'Liste des audiences( Communauté )',
                'icon' => 'bi bi-list',
                'name' => 'audience',
                'href' => $this->generateUrl('app_config_audiences_ls', ['module' => 'audience'])
            ],
            [
                'label' => 'Liste des audiences(Individuelle)',
                'icon' => 'bi bi-list',
                'name' => 'demande',
                'href' => $this->generateUrl('app_config_audiences_ls', ['module' => 'demande'])
            ],


        ];

        $breadcrumb->addItem([
            [
                'route' => 'app_default',
                'label' => 'Tableau de bord'
            ],
            [
                'label' => 'Paramètres'
            ]
        ]);

        return $this->render('config/audience/index.html.twig', [
            'modules' => $modules,
            'module_name' => self::MODULE_NAME,
            'breadcrumb' => $breadcrumb,
            'permition' => $permission
        ]);
    }


    #[Route(path: '/{module}', name: 'app_config_audiences_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {
        /**
         * @todo: A déplacer dans un service
         */
        $parametres = [


            'audience' => [
                // [
                //     'label' => 'Créer',
                //     'id' => 'audience_creer',
                //     'href' => $this->generateUrl('app_gestion_audience_new')
                // ], 

                [
                    'label' => 'En attentes de validation',
                    'id' => 'audience_initie',
                    'href' => $this->generateUrl('app_config_audience_ls', ['etat' => 'audience_initie'])
                ],

                [
                    'label' => 'Accordées',
                    'id' => 'audience_valider',
                    'href' => $this->generateUrl('app_config_audience_ls', ['etat' => 'audience_valider'])
                ],
                [
                    'label' => 'Blacklistes',
                    'id' => 'audience_rejeter',
                    'href' => $this->generateUrl('app_config_audience_ls', ['etat' => 'audience_rejeter'])
                ],

               
            ],

            'demande' => [
                // [
                //     'label' => 'Créer',
                //     'id' => 'demande_créer',
                //     'href' => $this->generateUrl('app_gestion_demande_new')
                // ], 
                [
                    'label' => 'En attentes de validation',
                    'id' => 'demande_initie',
                    'href' => $this->generateUrl('app_config_demande_ls', ['etat' => 'demande_initie'])
                ],

                [
                    'label' => 'Accordées',
                    'id' => 'demande_valider',
                    'href' => $this->generateUrl('app_config_demande_ls', ['etat' => 'demande_valider'])
                ],
                [
                    'label' => 'Blacklistes',
                    'id' => 'demande_rejeter',
                    'href' => $this->generateUrl('app_config_demande_ls', ['etat' => 'demande_rejeter'])
                ],
            ],
        ];



        return $this->render('config/audience/liste.html.twig', ['links' => $parametres[$module] ?? []]);
    }
}

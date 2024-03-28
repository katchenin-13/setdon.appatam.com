<?php

namespace App\Controller\Configuration;

use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/config/fiel')]
class  FielController extends BaseController
{

    private const MODULE_NAME = 'Dons et Promessses';
    const INDEX_ROOT_NAME = 'app_config_fiel_index';

    #[Route(path: '/', name: 'app_config_fiel_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        $modules = [
            [
                'label' => 'Liste des dons',
                'icon' => 'bi bi-list',
                'href' => $this->generateUrl('app_gestion_fieldon_index')
            ],
            
            [
                'label' => 'Liste des promesses',
                'icon' => 'bi bi-list',
                'href' => $this->generateUrl('app_config_fiel_ls', ['module' => 'promesse'])
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

        return $this->render('config/fielpromesse/index.html.twig', [
            'modules' => $modules,
            'module_name' => self::MODULE_NAME,
            'breadcrumb' => $breadcrumb,
            'permition' => $permission
        ]);
    }


    #[Route(path: '/{module}', name: 'app_config_fiel_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {
        /**
         * @todo: A déplacer dans un service
         */
        $parametes = [

            'don' => [
                     
                     'label' => 'Liste des dons',
                     'id' => 'param_hhh',
                     'href' => $this->generateUrl('app_gestion_fieldon_index')
                ] ,
            'promesse' => [
                [
                    'label' => 'En attentes de validation',
                    'id' => 'fielpromesse_initie',
                    'href' => $this->generateUrl('app_config_fielpromesse_ls', ['etat' => 'fielpromesse_initie'])
                ],

                [
                    'label' => 'Accordées',
                    'id' => 'fielpromesse_valider',
                    'href' => $this->generateUrl('app_config_fielpromesse_ls', ['etat' => 'fielpromesse_valider'])
                ],
                [
                    'label' => 'Blacklistes',
                    'id' => 'fielpromesse_rejeter',
                    'href' => $this->generateUrl('app_config_fielpromesse_ls', ['etat' => 'fielpromesse_rejeter'])
                ],
            ],
        ];


        return $this->render('config/fielpromesse/liste.html.twig', ['links' => $parametes[$module] ?? []]);
    }
}
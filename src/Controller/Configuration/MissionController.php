<?php

namespace App\Controller\Configuration;

use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/config/mission')]
class   MissionController extends BaseController
{
    private const MODULE_NAME = 'Rapports de missions';
    const INDEX_ROOT_NAME = 'app_config_mission_index';


    #[Route(path: '/', name: 'app_config_mission_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {
    $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        $modules = [
            [
                'label' => 'Nouveau',
                'icon' => 'bi bi-list',
                'href' => $this->generateUrl('app_gestion_mission_rapport_new')
            ],
            [
                'label' => '  rapports Initiés',
                'icon' => 'bi bi-list',
                'href' => $this->generateUrl('app_config_mission_ls', ['etat' => 'missionrapport_initie'])
            ],
            [
                'label' => '  rapports en attends',
                'icon' => 'bi bi-list',
                'href' => $this->generateUrl('app_config_mission_ls', ['etat' => 'missionrapport_attend'])
            ],
            [
                'label' => 'Rapports  rejetés',
                'icon' => 'bi bi-list',
                'href' => $this->generateUrl('app_config_mission_ls', ['etat' => 'missionrapport_rejeter'])
            ],
            [
                'label' => 'Rapports validés',
                'icon' => 'bi bi-list',
                'href' => $this->generateUrl('app_config_mission_ls', ['etat' => 'missionrapport_valider'])
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

        return $this->render('config/mission/index.html.twig', [
            'modules' => $modules,
            'breadcrumb' => $breadcrumb,
            'module_name' => self::MODULE_NAME,
            'permition' => $permission
        ]);
    }


    #[Route(path: '/{module}', name: 'app_config_mission_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {
        /**
         * @todo: A déplacer dans un service
         */
        $parametes = [

           
        ];


        return $this->render('config/mission/liste.html.twig', ['links' => $parametes[$module] ?? []]);
    }
}
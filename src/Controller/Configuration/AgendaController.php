<?php

namespace App\Controller\Configuration;

use App\Repository\AgendaRepository;
use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/config/agenda')]
class   AgendaController extends BaseController
{
    private const MODULE_NAME = 'Agenda';
    const INDEX_ROOT_NAME = 'app_config_agenda_index';
    #[Route(path: '/', name: 'app_config_agenda_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        $modules = [
                        [
                            'label' => 'Liste des évènements ',
                            'icon' => 'bi bi-people',
                            'href' => $this->generateUrl('app_gestion_agenda_index', ['module' => 'evenement'])
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


        return $this->render('config/agenda/index.html.twig', [
            'modules' => $modules,
            'breadcrumb' => $breadcrumb,
            'module_name' => self::MODULE_NAME, 
            'permition' => $permission
          
        ]);
    }


    #[Route(path: '/{module}', name: 'app_config_agenda_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {
        /**
         * @todo: A déplacer dans un service
         */
        $parametes = [

            'evenement' => [
                     
                     'label' => 'Liste des évènements',
                     'id' => 'param_audience',
                     'href' => $this->generateUrl('app_gestion_agenda_index')
                ] ,
                
        //    'evenement' => [
                     
        //             'label' => 'Liste des évènements passé',
        //             'id' => 'param_r',
        //             'href' => $this->generateUrl('app_gestion_agenda_index')
        //        ] ,
        ];


        return $this->render('config/agenda/liste.html.twig', ['links' => $parametes[$module] ?? []]);
    }
}
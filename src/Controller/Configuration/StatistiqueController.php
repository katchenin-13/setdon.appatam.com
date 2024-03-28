<?php

namespace App\Controller\Configuration;

use App\Entity\Communaute;
use App\Repository\CommunauteRepository;
use App\Service\Breadcrumb;
use App\Service\Menu;
use App\Service\StatsService;
use App\Service\Utils;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/config/statistique')]
class StatistiqueController extends BaseController
{
    private const MODULE_NAME = 'Statistiques';
    const INDEX_ROOT_NAME = 'app_config_statistique_index';
    #[Route(path: '/', name: 'app_config_statistique_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {

        
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
        $modules = [
           
            [
                'label' => 'Audiences(communauté)  ',
                'icon' => 'bi bi-people',
                'href' => $this->generateUrl('app_config_statistique_ls', ['module' => 'audience'])
            ],
            [
                'label' => 'Audiences(personne unique)',
                'icon' => 'bi bi-people',
                'href' => $this->generateUrl('app_config_statistique_ls', ['module' => 'demande'])
            ],
            [
                'label' => 'Dons',
                'icon' => 'bi bi-people',
                'href' => $this->generateUrl('app_config_statistique_ls', ['module' => 'don'])
            ],

            [
                'label' => 'Promesses',
                'icon' => 'bi bi-people',
                'href' => $this->generateUrl('app_config_statistique_ls', ['module' => 'promesse'])
            ],
            // [
            //     'label' => 'Rapport de missions',
            //     'icon' => 'bi bi-people',
            //     'href' => $this->generateUrl('app_config_statistique_ls', ['module' => 'rapport'])
            // ],
        

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

        return $this->render('config/statistique/index.html.twig', [
            'modules' => $modules,
            'breadcrumb' => $breadcrumb,
            'permition' => $permission
        ]);
    }


    #[Route(path: '/{module}', name: 'app_config_statistique_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {
        /**
         * @todo: A déplacer dans un service
         */
        $parametres = [

            'audience' => [
               
                [
                    'label' => 'Par année',
                    'id' => 'audience_tableau',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_audience_tableau')
                 ],
             
                 [
                    'label' => 'Par mois',
                    'id' => 'audience_communaute_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_audience_communaute')
                ],

                [
                    'label' => 'Par nature de communauté',
                    'id' => 'audience_categorie_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_audience_categorie')
                ],

                [
                    'label' => 'Par localité ',
                    'id' => 'audience_localite_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_audience_localite')
                ],

            ],


            'demande' => [
               
                [
                    'label' => 'Par année',
                    'id' => 'demande_tableau',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_demande_tableau')
                ],
                
                [
                    'label' => 'Par localite ',
                    'id' => 'demande_localite_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_demande_localite')
                ],
            ],

            'don' => [

                [
                    'label' => 'Par année',
                    'id' => 'demande_tableau',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_don_tableau')
                ],

                [
                    'label' => 'Par mois ',
                    'id' => 'don_communaute_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_don_communaute')
                ],

                [
                    'label' => 'Par nature communauté ',
                    'id' => 'don_categorie_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_don_categorie')
                ],
                [
                    'label' => 'Par localité ',
                    'id' => 'don_localite_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_don_localite')
                ],
             
            ],
            'promesse' => [

                [
                    'label' => 'Par année',
                    'id' => 'demande_tableau',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_promesse_tableau')
                ],
                [
                    'label' => 'Par mois',
                    'id' => 'promesse_communaute_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_promesse_communaute')
                ],

                [
                    'label' => 'Par nature de communauté ',
                    'id' => 'promesse_categorie_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_promesse_categorie')
                ],
                [
                    'label' => 'Par localité ',
                    'id' => 'promesse_localite_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_promesse_localite')
                ]
            ],

            'rapport' => [

                [
                    'label' => 'Tableau de bord',
                    'id' => 'rapport_tableau',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_rapport_tableau')
                ],


                [
                    'label' => 'Graphe communaute ',
                    'id' => 'rapport_communaute_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_rapport_communaute')
                ],

                [
                    'label' => 'Graphe categorie ',
                    'id' => 'rapport_categorie_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_rapport_categorie')
                ],
                [
                    'label' => 'Graphe localite ',
                    'id' => 'rapport_localite_graphe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_gestion_statistique_rapport_localite')
                ],

            ], 
            

            'graphiquesdon' => [
              
                [
                    'label' => 'Communauté',
                    'id' => 'chart_contrat',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_type_contratdon')
                ],
                [
                    'label' => 'Catégorie',
                    'id' => 'chart_genre',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_genredon')
                ],
                [
                    'label' => 'Localité',
                    'id' => 'chart_h_sexe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_hierarchie_sexedon')
                ],
            ],
            'graphiquesprom' => [
              
                [
                    'label' => 'Communauté',
                    'id' => 'chart_contrat',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_type_contratpro')
                ],
                [
                    'label' => 'Catégorie',
                    'id' => 'chart_genre',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_genrepro')
                ],
                [
                    'label' => 'Localité',
                    'id' => 'chart_h_sexe',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_hierarchie_sexepro')
                ],

                [
                    'label' => 'Promesses Réalisée',
                    'id' => 'chart_py_anc',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_pyramide_ancpro')
                ],
                
                [
                    'label' => 'Promesses non Réalisée',
                    'id' => 'chart_py_anc',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_pyramide_anpro')
                ],

            ],

            'attend' => [
                [
                    'label' => 'Audience avec un personne',
                    'id' => 'audience_avec_une_personne',
                    'href' => $this->generateUrl('app_gestion_statistique_attante_audience')
                ],
                [
                    'label' => 'Audiences avec groupes de personne',
                    'id' => 'audience_avec_groupe_de_personne',
                    'href' => $this->generateUrl('app_gestion_statistique_attante_demande')
                ],
                [
                    'label' => 'Promesse',
                    'id' => 'promesse',
                    'href' => $this->generateUrl('app_gestion_statistique_attante_promesse')
                ],
                [
                    'label' => 'Rapport de mission',
                    'id' => 'rapport',
                    'href' => $this->generateUrl('app_gestion_statistique_attante_rapport')
                ],
            

            ],
            'blacklist' => [
                [
                    'label' => 'Audience avec un personne',
                    'id' => 'audience_avec_une_personne',
                    'href' => $this->generateUrl('app_gestion_statistique_rejeter_audience')
                ],
                [
                    'label' => 'Audiences avec groupes de personne',
                    'id' => 'audience_avec_groupe_de_personne',
                    'href' => $this->generateUrl('app_gestion_statistique_rejeter_demande')
                ],
                [
                    'label' => 'Promesse',
                    'id' => 'promesse',
                    'href' => $this->generateUrl('app_gestion_statistique_rejeter_promesse')
                ],
             

            ],
            'valider' => [
                [
                    'label' => 'Audience avec un personne',
                    'id' => 'audience_avec_une_personne',
                    'href' => $this->generateUrl('app_gestion_statistique_valider_audience')
                ],
                [
                    'label' => 'Audiences avec groupes de personne',
                    'id' => 'audience_avec_groupe_de_personne',
                    'href' => $this->generateUrl('app_gestion_statistique_valider_demande')
                ],
                [
                    'label' => 'Promesse',
                    'id' => 'promesse',
                    'href' => $this->generateUrl('app_gestion_statistique_valider_promesse')
                ],
              
               [
                    'label' => 'Rapport de missions',
                    'id' => 'rapport',
                    'href' => $this->generateUrl('app_gestion_statistique_valider_rapport')
                ],
            ],

            // 'graphiques' => [

            //     [
            //         'label' => 'Communauté',
            //         'id' => 'chart_contrat',
            //         'icon' => 'bi bi-list',
            //         'href' => $this->generateUrl('app_rh_dashboard_type_contratpro')
            //     ],
            //     [
            //         'label' => 'Catégorie',
            //         'id' => 'chart_genre',
            //         'icon' => 'bi bi-list',
            //         'href' => $this->generateUrl('app_rh_dashboard_genrepro')
            //     ],
            //     [
            //         'label' => 'Localité',
            //         'id' => 'chart_h_sexe',
            //         'icon' => 'bi bi-list',
            //         'href' => $this->generateUrl('app_rh_dashboard_hierarchie_sexepro')
            //     ],

            //     [
            //         'label' => 'Promesses Réalisée',
            //         'id' => 'chart_py_anc',
            //         'icon' => 'bi bi-list',
            //         'href' => $this->generateUrl('app_rh_dashboard_pyramide_ancpro')
            //     ],

            //     [
            //         'label' => 'Promesses non Réalisée',
            //         'id' => 'chart_py_anc',
            //         'icon' => 'bi bi-list',
            //         'href' => $this->generateUrl('app_rh_dashboard_pyramide_anpro')
            //     ],

            // ],

        ];


        return $this->render('config/statistique/liste.html.twig', ['links' => $parametres[$module] ??[]]);
    }
    
}
<?php

namespace App\Controller\Gestion\statistique;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GraphiqueController extends AbstractController
{
    #[Route('/gestion/statistique/graphique', name: 'app_gestion_statistique_graphique')]
    public function index(): Response
    {
        return $this->render('gestion/statistique/graphique/index.html.twig', [
            'controller_name' => 'GraphiqueController',
        ]);
    }




    private function createFilterForm()
    {

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_config_statistique_index'))
            ->setMethod('POST');

       
        $formBuilder->add('annee', IntegerType::class, ['label' => 'Année']);
        $formBuilder->add('mois', ChoiceType::class, ['choices' => array_flip(Utils::MOIS), 'label' => 'Mois', 'attr' => ['class' => 'has-select2']]);
        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Type de contrat',
            'class' => Communaute::class,
            'required' => false
        ]);
        $formBuilder->add('genre', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'code',
            'label' => 'Sexe',
            'class' => Genre::class,
            'required' => false
        ]);
        $formBuilder->add('unite', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Unité',
            'class' => UniteEmploye::class,
            'required' => false
        ]);
        $formBuilder->add('niveauHierarchique', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Niveau Hiérarchique',
            'class' => NiveauHierarchique::class,
            'required' => false
        ]);
        $formBuilder->add('niveauMaitrise', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Niveau de maitrise',
            'class' => NiveauMaitrise::class,
            'required' => false
        ]);
        $formBuilder->add('statut', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Statut',
            'class' => StatutEmploye::class,
            'required' => false
        ]);

        return $formBuilder->getForm();
    }


    #[Route('/type-contrat', name: 'app_rh_dashboard_type_contrat')]
    public function indexTypeContrat(Request $request, EmployeRepository $employeRepository, GenreRepository $genreRepository, NiveauHierarchiqueRepository $niveauHierarchiqueRepository)
    {
        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_rh_dashboard_type_contrat'))
            ->setMethod('POST');

        $formBuilder->add('communaute', EntityType::class, [
            'placeholder' => '---',
            'choice_label' => 'libelle',
            'label' => 'Type de contrat',
            'attr' => ['class' => 'has-select2'],
            'choice_attr' => function (TypeContrat $typeContrat) {
                return ['data-value' => $typeContrat->getLibelle()];
            },
            'class' => TypeContrat::class,
            'required' => false
        ]);


        $form = $formBuilder->getForm();

        return $this->renderForm('gestion/statistique/graphique/type_contrat.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/data-type-contrat', name: 'app_rh_dashboard_type_contrat_data', condition: "request.query.has('filters')")]
    public function dataTypeContrat(Request $request, EmployeRepository $employeRepository, TypeContratRepository $typeContratRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['typeContrat'];
        $dataAnnees = $employeRepository->getAnneeRangeContrat($typeContratId);
        $annees = range($dataAnnees['min_year'], $dataAnnees['max_year']);
        $data = $employeRepository->getDataTypeContrat($typeContratId);

        $typeContrat = $typeContratRepository->find($typeContratId);

        $series = [['name' => $typeContrat->getLibelle(), 'data' => []]];

        foreach ($data as $_row) {
            $series[0]['data'][] = $_row['_total'];
        }


        return $this->json(['series' => $series, 'annees' => $annees]);
    }
}

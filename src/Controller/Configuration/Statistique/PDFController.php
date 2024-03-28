<?php

namespace App\Controller\Configuration\Statistique;

use App\Repository\DemandeRepository;
use App\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/Pdfgeneretor')]
class PDFController extends AbstractController
{

    ##################################################################################################################
    #########################################################  Audiences de Promesses ###############################


    #[Route('/pdf/audience/accord', name: 'app_pdf_generator_audience_accord')]
    public function generateAudienceAccordPdf(StatsService $statsService): Response
    {
        // return $this->render('pdf_generator/index.html.twig', [
        //     'controller_name' => 'PdfGeneratorController',
        // ]);
        $data = $statsService->getAudiencerealieyes();
        //$data = $audience->findAll();

        $html =  $this->renderView('config/statistique/Repportages/Audiences/accord.html.twig', [
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

    #[Route('/pdf/audience/rejet', name: 'app_pdf_generator_audience_rejet')]
    public function generateAudiencerejetPdf(StatsService $statsService): Response
    {
        // return $this->render('pdf_generator/index.html.twig', [
        //     'controller_name' => 'PdfGeneratorController',
        // ]);
        $data = $statsService->getAudiencerealieno();
        $html =  $this->renderView('config/statistique/Repportages/Audiences/rejete.html.twig', [
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



    #[Route('/pdf/demande/accord', name: 'app_pdf_generator_demende_accord')]
    public function generateDemendeAccordPdf(StatsService $statsService): Response
    {
       
        $data= $statsService->getDemanderealieyes();

        //dd($audiencesyes);
       
        

        $html =  $this->renderView('config/statistique/Repportages/Demandes/accord.html.twig', [
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

    #[Route('/pdf/demande/rejet', name: 'app_pdf_generator_demande_rejet')]
    public function generateDemanderejetPdf(StatsService $statsService): Response
    {
        
        $data = $statsService->getDeamnderealieno();

        $html =  $this->renderView('config/statistique/Repportages/Demandes/rejete.html.twig', [
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


    #[Route('/pdf/don/nature', name: 'app_pdf_generator_don_nature')]
    public function generateDonsNaturePdf(StatsService $statsService): Response
    {
        // return $this->render('pdf_generator/index.html.twig', [
        //     'controller_name' => 'PdfGeneratorController',
        // ]);
        $data = $statsService->getDonenature();

        $html =  $this->renderView('config/statistique/Repportages/Dons/nature.html.twig', [
            'data' => $data
        ]);


        $mpdf = new \Mpdf\Mpdf([

            'mode' => 'utf-8', 'format' => 'A4',
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

    #[Route('/pdf/don/rejet', name: 'app_pdf_generator_don_espece')]
    public function generateDonsrejetPdf(StatsService $statsService): Response
    {
        // return $this->render('pdf_generator/index.html.twig', [
        //     'controller_name' => 'PdfGeneratorController',
        // ]);
        $data = $statsService->getDonespece();

        $html =  $this->renderView('config/statistique/Repportages/Dons/espece.html.twig', [
            'data' => $data
        ]);


        $mpdf = new \Mpdf\Mpdf([

            'mode' => 'utf-8', 'format' => 'A4',
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



    #[Route('/pdf/promesses/accord', name: 'app_pdf_generator_promesse_accord')]
    public function generatePromesseAccordPdf(StatsService $statsService): Response
    {
       
        $data = $statsService->getPormesserealieyes();
   

        $html =  $this->renderView('config/statistique/Repportages/Promesses/accord.html.twig', [
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

    #[Route('/pdf/promesses/rejet', name: 'app_pdf_generator_promesse_rejet')]
    public function generatepromessesrejetPdf(StatsService $statsService): Response
    {
     
        $data = $statsService->getPormesserealieyes();

        $html =  $this->renderView('config/statistique/Repportages/Promesses/rejete.html.twig', [
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


    #[Route('/pdf/promesses/nature', name: 'app_pdf_generator_promesses_nature')]
    public function generatePromesesessNaturePdf(StatsService $statsService): Response
    {
        // return $this->render('pdf_generator/index.html.twig', [
        //     'controller_name' => 'PdfGeneratorController',
        // ]);
        $data = $statsService->getPromessenature();

        $html =  $this->renderView('config/statistique/Repportages/Promesses/nature.html.twig', [
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

    #[Route('/pdf/promesses/espece', name: 'app_pdf_generator_promesses_espece')]
    public function generatePromesseEspecePdf(StatsService $statsService): Response
    {
    
        $data = $statsService->getPromesseespece();
        $html =  $this->renderView('config/statistique/Repportages/Promesses/espece.html.twig', [
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

}
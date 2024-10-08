<?php

namespace App\Controller\Gestion;

use App\Controller\ApiInterface;
use App\Controller\BaseController;
use App\Entity\Agenda;
use App\Repository\AgendaRepository;
use App\Repository\CalendrierRepository;
use App\Repository\EventRepository;
use App\Service\StatsService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/calendrier')]
class CalendrierController extends ApiInterface
{



    #[Route('/', name: 'api_audience')]
    public function index(EventRepository $eventRepository, StatsService $statsService): Response
    {


        $events = $eventRepository->findAll();
        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'title' => $event->getNom(),
                // 'description' => $event->getDescription(),
                'start' => $event->getStartdate()->format('Y-m-d'),
                'end' => $event->getEnddate()->format('Y-m-d'),
            ];
        }
        $demandecefuture = $statsService->getCalendrier();
        $audiencesAvenir = [];
        foreach ($demandecefuture as $audiancefutures) {
            $audiencesAvenir[] = [
                'title' => $audiancefutures->getMotif(),

                'start' => $audiancefutures->getDaterencontre()->format('Y-m-d'),
                'end' => $audiancefutures->getDaterencontre()->format('Y-m-d'),
            ];
        }
        $datas = array_merge($data, $audiencesAvenir);

        $data = json_encode($datas);
        //dd($datas);
        return $this->render('gestion/calendrier/aganda.html.twig', [
            'data' => $data,
        ]);
    }
}

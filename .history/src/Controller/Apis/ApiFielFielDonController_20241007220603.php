<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Fieldon;

use App\Repository\FieldonRepository;
use App\Repository\ModuleGroupePermitionRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Symfony\Component\String\toString;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route('/api/fieldon')]
class ApiFielDonController extends ApiInterface
{
    #[Route('/', name: 'api_fieldon', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fieldon::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'Fieldon')]
    #[Security(name: 'Bearer')]
    public function getAll(FieldonRepository $fieldonRepository,Request $request): Response
    {
        try {
             $fieldons = $fieldonRepository->findAll();
            $tabaFielDon = [];
            $i = 0;
            foreach ($fieldons as $key => $value) {

                        $tabaFielDon[$i]['id'] = $value->getId();
                        $tabaFielDon[$i]['dateremise'] = $value->getDon()->getDateremise();
                        $tabaFielDon[$i]['communaute'] = $value->getDon()->getCommunaute()->getLibelle();
                        $tabaFielDon[$i]['recipiandaire'] = $value->getDon()->getNom();
                        $tabaFielDon[$i]['remise'] = $value->getDon()->getRemispar();
                        $tabaFielDon[$i]['nature'] = $value->getNaturedon();
                        $tabaFielDon[$i]['quantite'] = $value->getQte();
                        $tabaFielDon[$i]['motif'] = $value->getMotifdon();
                        $tabaFielDon[$i]['valeur'] = $value->getMontantdon();
                
                        $tabaFielDon[$i]['typedonsfiel'] = $value->getTypedonsfiel() ;
                            
                $i++;
                
            }
          
            $response = $this->response($tabaFielDon);
            //dd($fieldons);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse

        return $response;
    }
}


public function getAll(FielpromesseRepository $fielpromesseRepository, Request $request): Response
    {
        //dd($this->response($promesseRepository->findAll()));
        try {
            //dd($groupePermitionRepository->getNombreDemandeParMois());
            $total_items = $fielpromesseRepository->countItems();
            $fielpromesses = $fielpromesseRepository->findAll();

            $tabaFielPromesse = [];
            $item=[];
            $i = 0;
            foreach ($fielpromesses as $key => $value) {

                $item[$i]['id'] = $value->getId();
                $item[$i]['dateremise'] = $value->getPromesse()->getDate();
                $item[$i]['communaute'] = $value->getPromesse()->getCommunaute()->getLibelle();
                $item[$i]['recipiandaire'] = $value->getPromesse()->getNom();
                $item[$i]['nature'] = $value->getNature();
                $item[$i]['quantite'] = $value->getQte();
                $item[$i]['motif'] = $value->getMotif();
                $item[$i]['valeur'] = $value->getMontant();
              
                $item[$i]['typedonsfiel'] = $value->getTypepromesse();

                $i++;
            }
            $tabaFielPromesse = [
                'total_count' => $total_items,
                'items' => $item,

            ];
            $response = $this->response($tabaFielPromesse);
            //dd($promesses);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse

        return $response;
    }
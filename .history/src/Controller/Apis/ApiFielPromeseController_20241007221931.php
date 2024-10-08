<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Fielpromesse;
use App\Entity\Promesse;
use App\Repository\FielpromesseRepository;
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
use OpenApi\Annotations as OA;

#[Route('/api/fielpromesse')]
class ApiFielPromeseController extends ApiInterface
{
    #[Route('/', name: 'api_promesse', methods: ['GET'])]
    /**
     * Affiche toutes les FielPromesses.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=FielPromesse::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Fielpromesse")
     * @Security(name="Bearer")
     */
    public function getAll(FielpromesseRepository $fielpromesseRepository, Request $request): Response
    {
        //dd($this->response($promesseRepository->findAll()));
        try {
            //dd($groupePermitionRepository->getNombreDemandeParMois());
           
            $fielpromesses = $fielpromesseRepository->findAll();

            $tabaFielPromesse = [];
            
            $i = 0;
            foreach ($fielpromesses as $key => $value) {

                $tabaFielPromesse[$i]['id'] = $value->getId();
                $tabaFielPromesse[$i]['dateremise'] = $value->getPromesse()->getDate();
                $tabaFielPromesse[$i]['communaute'] = $value->getPromesse()->getCommunaute()->getLibelle();
                $tabaFielPromesse[$i]['recipiandaire'] = $value->getPromesse()->getNom();
                $tabaFielPromesse[$i]['nature'] = $value->getNature();
                $tabaFielPromesse[$i]['quantite'] = $value->getQte();
                $tabaFielPromesse[$i]['motif'] = $value->getMotif();
                $tabaFielPromesse[$i]['valeur'] = $value->getMontant();
              
                $tabaFielPromesse[$i]['typedonsfiel'] = $value->getTypepromesse();

                $i++;
            }
            // $tabaFielPromesse = [
            //     'total_count' => $total_items,
            //     'items' => $item,

            //];
            $response = $this->response($tabaFielPromesse);
            //dd($promesses);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }

        // On envoie la réponse

        return $response;
    }

    
}

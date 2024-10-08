<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Fieldon;
use OpenApi\Attributes as OA;
use App\Repository\FieldonRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Symfony\Component\String\toString;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;


#[Route('/api/fieldon')]
class ApiFieldonController extends ApiInterface
{

    private FieldonRepository $fieldonRepository;

    public function __construct(FieldonRepository $fieldonRepository)
    {
        $this->fieldonRepository = $fieldonRepository;
    }
    #[Route('/', name: 'api_fieldon', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Returns the rewards of an user",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Fieldon::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'Fieldon')]
    #[Security(name: 'Bearer')]
public function getAll(): Response
    {
        try {
            $fieldons = $$this->fieldonRepository->findAll();
            $tabaFieldon = [];
            $i = 0;
            foreach ($fieldons as $key => $value) {
                $tabafieldons[$i]['id'] = $value->getDon()->getDateremise();
                $tabafieldons[$i]['dateremise'] = $value->getDon()->getDateremise();
                $tabafieldons[$i]['communaute'] = $value->getDon()->getCommunaute()->getLibelle();
                // $tabafieldons[$i]['recipiandaire'] = $value->getDon()->getRecipiandaire();
                $tabafieldons[$i]['remise'] =   $value->getDon()->getRemispar();
                $tabafieldons[$i]['nature'] = $value->getNaturedon();
                $tabafieldons[$i]['quantite'] = $value->getQte();
                $tabafieldons[$i]['motif'] = $value->getMotifdon();
                $tabafieldons[$i]['valeur'] = $value->getMontantdon();
                $tabafieldons[$i]['typedonsfiel'] = $value->getTypedonsfiel();
                $i++;
            }

            //dd($tabaAudience);


            $response = $this->response($tabaFieldon);
            //dd($fieldons);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response(null);
        }


        return $response;
    }


    // #[Route('/justification_fieldon/{id}', name: 'api_fieldon_justification', methods: ['POST'])]
    // #[OA\Tag(name: 'Fieldon')]
    // #[Security(name: 'Bearer')]

    // public function justification(Request $request, FieldonRepository $fieldonRepository, $id)
    // {
    //     try {
    //         $data = json_decode($request->getContent());

    //         $fieldon = $fieldonRepository->find($id);
    //         if ($fieldon != null) {

    //             $fieldon->setEtat($data->etat);
    //             $fieldon->setJustification($data->justification);

    //             // On sauvegarde en base
    //             $fieldonRepository->save($fieldon, true);
    //             // On retourne la confirmation

    //             $response = $this->json([
    //                 'statut' => 200,
    //                 'message' => 'Fieldon mise à jour avec succès',

    //             ], Response::HTTP_OK);
    //             dd($this->json([
    //                 'statut' => 200,
    //                 'message' => 'Fieldon mise à jour avec succès',

    //             ], Response::HTTP_OK));
    //             return $response;
    //         } else {
    //             $response = $this->json([
    //                 'statut' => 404,
    //                 'message' => 'Fieldon non trouvée',

    //             ]);
    //             return $response;
    //         }
    //     } catch (\Exception $exception) {
    //         $response = $this->json([
    //             'statut' => 500,
    //             'message' => 'Erreur : ' . $exception->getMessage()
    //         ]);

    //         return $response;
    //     }
    //     return $response;
    // }
}

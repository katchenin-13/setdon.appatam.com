<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Contact;
use App\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route('/api/contacts')]
class ApiContactController extends ApiInterface
{
    #[Route('/', name: 'api_contact', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of Contact entities',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Contact::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'Contact')]
    #[Security(name: 'Bearer')]
    public function getAll(ContactRepository $fielpromesseRepository, Request $request): Response
    {
        try {
            $contacts = $fielpromesseRepository->findAll();
            $tabaContact = [];

            foreach ($contacts as $i => $value) {
                $tabaContact[$i] = [
                    'id' => $value->getId(),
                    'nom' => $value->getNom(),
                    'fonction' => $value->getFonction(),
                    'email' => $value->getEmail(),
                    'telephone' => $value->getNumero(),
                    'adresse' => $value->getObservation(),
                    'communaute' => $value->getCommunaute()->getLibelle(),
                    
                ];
            }

            return $this->response($tabaContact);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response(null);
        }
    }
}

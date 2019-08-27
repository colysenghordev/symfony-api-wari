<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Depot;
use App\Form\UserType;
use App\Form\DepotType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api")
 */
class WariController extends AbstractController
{
    /**
     * @Route("/user", name="user-partenaire", methods={"POST"})
     * @IsGranted("ROLE_Partenaire")
     */
    public function user(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user  = new User();

        $form  = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $data = $request->request->all();
        $form->submit($data);
        $roles=['ROLE_Utilisateur simple'];
        $user->setRoles($roles);;

        if ($form->isSubmitted()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        
            $data = [
            'status' => 201,
            'message' => 'L\utilisateur a bien été ajouté'
            ];
            return new JsonResponse($data, 201);
        }
    }

    /**
     * @Route("/depot", name="depot-partenaire", methods={"POST"})
     * @IsGranted("ROLE_Caissier")
     */
    public function depot(Request $request)
    {
        $depot = new Depot();

        $form  = $this->createForm(DepotType::class, $depot);
        $form->handleRequest($request);
        $data = $request->request->all();
        $form->submit($data);
        $data = $request->request->all();
        $user=$this->getUser();
        $depot->setUser($user);
        $compte=$depot->getCompte();
        $depot->setDateDepot(new \DateTime);
        $compte->setSolde($compte->getSolde()+$depot->getmontant());

        if ($form->isSubmitted()) 
        {
            $entityManager   = $this->getDoctrine()->getManager();

            $entityManager->persist($depot);
            $entityManager->flush();

            $data = [
            'status' => 201,
            'message' => 'Le dépot a bien été effectué'
            ];
            return new JsonResponse($data, 201);
        }
    }
}

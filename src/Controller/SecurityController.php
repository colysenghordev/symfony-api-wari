<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Depot;
use App\Entity\Compte;
use App\Form\UserType;
use App\Form\DepotType;
use App\Form\CompteType;
use App\Entity\Partenaire;
use App\Form\PartenaireType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $partenaire = new Partenaire();
        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);
        $data = $request->request->all();
        $form->submit($data);

        $user  = new User();
        $form  = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $data = $request->request->all();
        $form->submit($data);
        $user->setPartenaire($partenaire);
            $roles=[];
            $profil=$user->getProfil()->getLibelle();
            if($profil =="Administrateur général"){
                $roles=["ROLE_Super-Admin"];
            }
            elseif($profil == "Administrateur secondaire"){
                $roles=["ROLE_ADMIN"];
            }
            elseif($profil == "Caissier"){
                $roles=["ROLE_Caissier"];
            }
            elseif($profil == "Partenaire"){
                $roles=["ROLE_Partenaire"];
            }
            elseif($profil == "Utilisateur simple"){
                $roles=["ROLE_Utilisateur simple"];
            }; 
        $user->setRoles($roles);

        $compte = new Compte();
        $random = random_int(100000, 10000000000);
        $form   = $this->createForm(CompteType::class, $compte);
        $form->handleRequest($request);
        $data = $request->request->all();
        $form->submit($data);
        $compte->setNumeroCompte("$random");
        $compte->setPartenaire($partenaire);

        $depot = new Depot();
        $form  = $this->createForm(DepotType::class, $depot);
        $form->handleRequest($request);
        $data = $request->request->all();
        $form->submit($data);
        $depot->setUser($user);
        $depot->setCompte($compte);
        $depot->setDateDepot(new \DateTime);

        if ($form->isSubmitted()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($partenaire);
            $entityManager->persist($user);
            $entityManager->persist($compte);
            $entityManager->persist($depot);
            $entityManager->flush();

            $data = [
                'status' => 201,
                'message' => 'Le partenaire a été créé'
            ];

            return new JsonResponse($data, 201);
        }
        $data = [
            'status' => 500,
            'message' => 'Vérifier les clés de renseignement'
        ];
        return new JsonResponse($data, 500);
    }
}

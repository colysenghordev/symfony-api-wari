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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     * @IsGranted("ROLE_Super-Admin")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator)
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
        $roles=['ROLE_Partenaire'];
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

        $errors = $validator->validate($user);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

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
     /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request)
    {
        $user=$this->getUser();
        return $this->json([
            'username'=>$user->getUsername(),
            'roles'=>$user->getRoles()]
        );
    }

    /**
     * @Route("/caissier_admin_secondaire", name="ajout caissier ou partenaire", methods={"POST"})
     * @IsGranted("ROLE_Super-Admin")
     */
    public function caissier_admin_secondaire(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user  = new User();

        $form  = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $data = $request->request->all();
        $form->submit($data);

            $roles=[];
            $profil=$user->getProfil()->getLibelle();
            if($profil == "Administrateur secondaire"){
                $roles=["ROLE_ADMIN"];
            }
            elseif($profil == "Caissier"){
                $roles=["ROLE_Caissier"];
            }; 
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
}

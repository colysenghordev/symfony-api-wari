<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Profil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminSupFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $profil = new Profil();
        $profil->setLibelle('Administrateur général');

        $manager->persist($profil);

        // $product = new Product();

        $user = new User();

        $user->setUsername('adminsup');
        $user->setRoles(['ROLE_Super-Admin']);
        $password = $this->encoder->encodePassword($user, 'coly');
        $user->setPassword($password);
        $user->setPrenom('Coly');
        $user->setNom('Senghor');
        $user->setAdresse('Keur Massar');
        $user->setTelephone('771933121');
        $user->setEmail('sakouthiang@gmail.com');
        $user->setProfil($profil);

        // $manager->persist($product);
        $manager->persist($user);
        $manager->flush();
    }
}

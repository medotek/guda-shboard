<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

/**
 * @Route("/api")
 */
class AuthController extends AbstractController
{
    private UserRepository $userRepository;
    private Security $security;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        Security $security,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/register", name="user.register")
     */
    public function register(Request $request) : JsonResponse
    {
        $jsonData = json_decode($request->getContent());
        // Create the user
        $user = new User();
        $user->setName($jsonData->username);
        $user->setEmail($jsonData->email);
        $user->setRoles(['ROLE_USER']);
        $user->setCreationDate(new \DateTime());

        // Configure different password hashers via the factory
        $factory   = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        // Retrieve the right password hasher by its name
        $passwordHasher = $factory->getPasswordHasher('common');

        $user->setPassword(
            $passwordHasher->hash(
                $jsonData->password
            )
        );
        // Persist user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'user' => $this->serializer->serialize($user, 'json')
        ], 201);
    }

    /**
     * @Route("/profile", name="user.profile")
     */
    public function profile() : JsonResponse
    {
        $currentUser = $this->security->getUser();
        $user = $this->serializer->serialize($currentUser, 'json');

        return new JsonResponse([
            $user
        ],200);
    }

}

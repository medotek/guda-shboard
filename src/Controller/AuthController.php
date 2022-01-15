<?php

namespace App\Controller;

use App\Entity\DiscordCredentials;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        UserRepository         $userRepository,
        Security               $security,
        SerializerInterface    $serializer,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Manage hashing
     * @return \Symfony\Component\PasswordHasher\PasswordHasherInterface
     */
    private function passwordHasher(): \Symfony\Component\PasswordHasher\PasswordHasherInterface
    {
        // Configure different password hashers via the factory
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        // Retrieve the right password hasher by its name
        return $factory->getPasswordHasher('common');

    }
    /**
     * @Route("/register", name="user.register")
     */
    public function register(Request $request): JsonResponse
    {
        $jsonData = json_decode($request->getContent());
        $existingEmail = $this->userRepository->findOneBy(['email' => $jsonData->email]);
        $existingName = $this->userRepository->findOneBy(['name' => $jsonData->username]);

        if (empty($existingName) && empty($existingEmail)) {
            // Create the user
            $user = new User();
            $user->setName($jsonData->username);
            $user->setEmail($jsonData->email);
            $user->setRoles(['ROLE_USER']);
            $user->setCreationDate(new \DateTime());


            $user->setPassword(
                $this->passwordHasher()->hash(
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

        $error = [];

        if (!empty($existingName) && !empty($existingEmail)) {
            $error['message'] = 'L\'email et le nom d\'utilisateur sont déja pris';
        } else {
            if (!empty($existingName)) {
                $error['message'] = 'Le nom d\'utilisateur est déja pris';
            }
            if (!empty($existingEmail)) {
                $error['message'] = 'L\'email est déja pris';
            }
        }
        return new JsonResponse([
            'error' => $error
        ]);
    }

    /**
     * @Route("/discord/register", name="user.discord.register")
     */
    public function loginDiscord(Request $request): JsonResponse
    {
        $jsonData = json_decode($request->getContent());
        $user = $this->entityManager->getRepository(User::class)->find($jsonData->userId);

        if ($user) {
            /** @var User $user **/
            if (!$user->getDiscordCredentials()) {
                $discordCredentials = new DiscordCredentials();
                $discordCredentials->setUser($user);

                $discordCredentials->setAccessToken($jsonData->accessToken);
                $discordCredentials->setRefreshToken($jsonData->refreshToken);
                $this->entityManager->persist($discordCredentials);
                $this->entityManager->flush();
            } else {
                // If the discord credentials exists or were created
                $discordCredentialsId = $user->getDiscordCredentials()->getId();
                $discordCredentials = $this->entityManager->getRepository(DiscordCredentials::class)->findOneBy(['id' => $discordCredentialsId, 'user' => $user]);
                /** @var DiscordCredentials $discordCredentials **/
                $discordCredentials->setAccessToken($jsonData->accessToken);
                $discordCredentials->setRefreshToken($jsonData->refreshToken);
                $this->entityManager->persist($discordCredentials);
                $this->entityManager->flush();
            }
            return new JsonResponse(
                'Discord account linked'
                , 200);
        }
        return new JsonResponse(
            'Discord account not linked'
            , 500);
    }

    /**
     * @Route("/profile", name="user.profile")
     */
    public function profile(): JsonResponse
    {
        $currentUser = $this->security->getUser();
        /** @var User $currentUser */
        if ($currentUser) {
            $currentUser->setLastConnectionDate(new \DateTime());
            $this->entityManager->persist($currentUser);
            $this->entityManager->flush();

            $user = $this->serializer->serialize($currentUser, 'json', ['groups' => ['user', 'discord_credentials']]);
            return new JsonResponse([
                $user
            ], 200);
        }
        return new JsonResponse('You need to be authenticated', 500);
    }
}

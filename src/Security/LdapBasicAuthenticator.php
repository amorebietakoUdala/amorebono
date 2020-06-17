<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class LdapBasicAuthenticator extends AbstractGuardAuthenticator
{
    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $ldap;
    private $userRepository;
    private $container;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, Ldap $ldap, UserRepository $userRepository, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->ldap = $ldap;
        $this->userRepository = $userRepository;
        $this->container = $container;
        $this->logger = $logger;
    }

    public function supports(Request $request)
    {
        return null !== $request->headers->has('authorization') && 0 === strpos($request->headers->get('authorization'), 'Basic ');
    }

    public function getCredentials(Request $request)
    {
        $authorizationHeader = $request->server->get('HTTP_AUTHORIZATION');
        $rawCredentials = base64_decode(str_replace('Basic ', '', $authorizationHeader));
        $username = $user = strstr($rawCredentials, ':', true);
        $password = $user = substr(strstr($rawCredentials, ':'), 1);
        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $domain = $this->container->getParameter('domain');
        $username = $domain.'\\'.$credentials['username'];
        try {
            $this->ldap->bind($username, $credentials['password']);
            $bindSuccessfull = true;
            $this->logger->debug('Succesfully binded to the LDAP');
        } catch (ConnectionException $e) {
            $bindSuccessfull = false;
            $this->logger->debug('Could not bind to LDAP with the credentials provided');
            throw new CustomUserMessageAuthenticationException('Could not bind to LDAP with the credentials provided');
        }

        /*
         * If bindSuccessfull, find the user in the ldap.
         * Then check DB for the same username.
         * If not found in DB add the user
         */
        if ($bindSuccessfull) {
            $query = $this->ldap->query(
                $this->container->getParameter('ldap_users_dn'),
                str_replace(
                    '{username}',
                    $credentials['username'],
                    $this->container->getParameter('ldap_users_filter')
                )
            );
            $results = $query->execute()->toArray();
            $resultsDB = $this->userRepository->findOneBy(['username' => $credentials['username']]);
            if (null === $resultsDB) {
                $user = $this->_addUser($results[0], $credentials['password']);
            }
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->logger->debug('Authetication Failed!!!');

        return new JsonResponse([
            'message' => $exception->getMessage(),
        ], 401);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Allow the request to continue
        return null;
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login');
    }

    /**
     * Called when authentication is needed, but it's not sent.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required',
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }

    private function _addUser($newUser, $password)
    {
        $user = new User();
        $user->setUsername($newUser->getAttribute('sAMAccountName')[0]);
        $user->setEmail($newUser->getAttribute('mail')[0]);
        $user->setFirstName($newUser->getAttribute('givenName')[0]);
        $user->setRoles([]);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}

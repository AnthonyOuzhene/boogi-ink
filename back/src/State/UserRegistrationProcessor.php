<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Exécuté sur POST /api/users (inscription), APRÈS la validation et AVANT l'enregistrement.
 * Il hache le mot de passe puis délègue l'enregistrement au processor Doctrine d'API Platform.
 *
 * @implements ProcessorInterface<User, User>
 */
final class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        // Le processor standard qui fait persist() + flush()
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        \assert($data instanceof User);

        $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPlainPassword()));
        $data->setPlainPassword(null);
        // Un compte créé depuis le site est toujours un simple client (ROLE_USER est implicite)
        $data->setRoles([]);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCrudController extends AbstractCrudController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $user): void
    {
        parent::updateEntity($entityManager, $user);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $user): void
    {
        $this->encodePassword($user);
        parent::persistEntity($entityManager, $user);
    }

    private function encodePassword($user): void
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('username'),
            TextField::new('password')->onlyOnForms(),
            TextField::new('name'),
            EmailField::new('email'),
            ArrayField::new('roles'),
            DateTimeField::new('passwordChangedDate'),
            TextField::new('confirmationToken'),
            BooleanField::new('enabled'),
            CollectionField::new('comments'),
            CollectionField::new('posts')
        ];
    }
}

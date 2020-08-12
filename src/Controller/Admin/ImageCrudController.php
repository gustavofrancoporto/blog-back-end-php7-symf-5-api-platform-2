<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            ImageField::new('url')
                ->setLabel('Image')
                ->hideOnForm(),
            Field::new('file')
                ->setFormType(VichFileType::class)
                ->setTemplatePath('vich_uploader_image.html.twig')
                ->onlyOnForms()
        ];
    }
}

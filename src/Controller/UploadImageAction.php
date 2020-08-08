<?php


namespace App\Controller;


use ApiPlatform\Core\Validator\Exception\ValidationException;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Controller\Form\ImageType;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class UploadImageAction
{
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $entityManager,
        ValidatorInterface $validator)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function __invoke(Request $request)
    {
        $image = new Image();

        $form = $this->formFactory->create(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $image->setFile(null);

            return $image;
        }
        // Uploading done in background by VichUploader

        throw new ValidationException($this->validator->validate($image));
    }
}
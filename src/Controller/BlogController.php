<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    private BlogPostRepository $blogPostRepository;

    public function __construct(BlogPostRepository $blogPostRepository)
    {
        $this->blogPostRepository = $blogPostRepository;
    }

    /**
     * @Route("/{page}", name="blog_list", defaults={"page": 1}, requirements={"page"="\d+"})
     */
    public function list(int $page, Request $request)
    {
        $limit = $request->get('limit', 10);
        $items = $this->blogPostRepository->findAll();

        return new JsonResponse([
            'page' => $page,
            'limit' => $limit,
            'data' => array_map(function($item) {
                return $this->generateUrl('blog_by_id', ['id' => $item->getId()]);
            }, $items)
        ]);
    }

    /**
     * @Route("/post/{id}", name="blog_by_id", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function post(BlogPost $post)
    {
        return $this->json($post);
    }

    /**
     * @Route("/post/{slug}", name="blog_by_slug", methods={"GET"})
     */
    public function postBySlug(BlogPost $post)
    {
        return $this->json($post);
    }

    /**
     * @Route("/add", name="blog_add", methods={"POST"})
     */
    public function add(Request $request)
    {
        $serializer = $this->get('serializer');
        $post = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();

        return $this->json($post);
    }

    /**
     * @Route("/post/{id}", name="blog_delete", methods={"DELETE"})
     */
    public function delete(BlogPost $post)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($post);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
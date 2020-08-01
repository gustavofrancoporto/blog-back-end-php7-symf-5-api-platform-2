<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var Generator
     */
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);

        $manager->flush();
    }

    public function loadBlogPosts(ObjectManager $manager)
    {
        $user = $this->getReference('admin');

        for ($i = 0; $i < 100; $i++) {
            $blogPost = (new BlogPost())
                ->setTitle($this->faker->realText(30))
                ->setPublished($this->faker->dateTimeThisYear)
                ->setContent($this->faker->realText())
                ->setAuthor($user)
                ->setSlug($this->faker->slug);

            $this->addReference("blog_post_$i", $blogPost);

            $manager->persist($blogPost);
        }
    }

    public function loadComments(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {

                $comment = (new Comment())
                    ->setContent($this->faker->realText())
                    ->setPublished($this->faker->dateTimeThisYear)
                    ->setAuthor($this->getReference("admin"))
                    ->setPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }
    }

    public function loadUsers(ObjectManager $manager)
    {
        $user = (new User())
            ->setUsername('admin')
            ->setEmail('admin@blog.com')
            ->setName('Gustavo Porto');

        $user->setPassword($this->passwordEncoder->encodePassword($user, '123456'));

        $this->addReference('admin', $user);

        $manager->persist($user);
    }
}

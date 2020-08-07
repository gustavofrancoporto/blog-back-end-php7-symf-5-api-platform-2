<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
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
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    private const USERS = [
        [
            'username' => 'sadmin',
            'name' => 'Super Administrator',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'enabled' => true
        ],
        [
            'username' => 'admin',
            'name' => 'Administrator',
            'roles' => [User::ROLE_ADMIN],
            'enabled' => true
        ],
        [
            'username' => 'robert',
            'name' => 'Robert Rob',
            'roles' => [User::ROLE_WRITER],
            'enabled' => true
        ],
        [
            'username' => 'richard',
            'name' => 'Richard Rich',
            'roles' => [User::ROLE_WRITER],
            'enabled' => true
        ],
        [
            'username' => 'samantha',
            'name' => 'Samantha Sam',
            'roles' => [User::ROLE_EDITOR],
            'enabled' => false
        ],
        [
            'username' => 'nickolas',
            'name' => 'Nickolas Nick',
            'roles' => [User::ROLE_COMMENTATOR],
            'enabled' => true
        ]
    ];

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenGenerator $tokenGenerator)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
        $this->tokenGenerator = $tokenGenerator;
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
        for ($i = 0; $i < 100; $i++) {
            $blogPost = (new BlogPost())
                ->setTitle($this->faker->realText(30))
                ->setPublished($this->faker->dateTimeThisYear)
                ->setContent($this->faker->realText())
                ->setAuthor($this->getRandomUserReference(BlogPost::class))
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
                    ->setAuthor($this->getRandomUserReference(Comment::class))
                    ->setBlogPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userData) {
            $user = (new User())
                ->setUsername($userData['username'])
                ->setEmail($userData['username'].'@blog.com')
                ->setName($userData['name'])
                ->setRoles($userData['roles'])
                ->setEnabled($userData['enabled'])
                ->setConfirmationToken($userData['enabled'] ? null : $this->tokenGenerator->getRandomSecureToken());

            $user->setPassword($this->passwordEncoder->encodePassword($user, '123456aB#'));

            $this->addReference('user_'.$user->getUsername(), $user);

            $manager->persist($user);
        }
    }

    public function getRandomUserReference($class): User
    {
        $randomUser = self::USERS[rand(0, sizeof(self::USERS) - 1)];
        $canPost = count(
            array_intersect($randomUser['roles'], [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_WRITER])
        );
        $canComment = count(
            array_intersect($randomUser['roles'], [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_WRITER, User::ROLE_COMMENTATOR])
        );

        if ($class === BlogPost::class && !$canPost) {
            return $this->getRandomUserReference($class);
        }

        if ($class === Comment::class && !$canComment) {
            return $this->getRandomUserReference($class);
        }

        return $this->getReference('user_' . $randomUser['username']);
    }
}

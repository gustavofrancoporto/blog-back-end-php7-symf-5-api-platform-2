<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $blogPost = (new BlogPost())
            ->setTitle('A first post!')
            ->setPublished(new \DateTime('2020-07-20 15:54:23'))
            ->setContent('Content of the first post')
            ->setAuthor('Gustavo Porto')
            ->setSlug('a-first-post')
        ;
        $manager->persist($blogPost);

        $blogPost = (new BlogPost())
            ->setTitle('A second post!')
            ->setPublished(new \DateTime('2020-07-22 17:01:12'))
            ->setContent('Content of the second post')
            ->setAuthor('Gustavo Porto')
            ->setSlug('a-second-post')
        ;
        $manager->persist($blogPost);

        $manager->flush();
    }
}

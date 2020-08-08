<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={"order"={"published": "DESC"}},
 *     itemOperations={
 *         "get",
 *         "put"={
 *             "security"="is_granted('ROLE_EDITOR') or (is_granted('ROLE_COMMENTATOR') and object.getAuthor() == user)"
 *         }
 *     },
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "security"="is_granted('ROLE_COMMENTATOR')"
 *         }
 *     },
 *     subresourceOperations={
 *         "api_blog_posts_comments_get_subresource"={
 *             "normalization_context"={
 *                 "groups"={"get-comments-with-author"}
 *             }
 *         }
 *     },
 *     denormalizationContext={
 *         "groups"={"post"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get-comments-with-author"})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=3000)
     * @Groups({"get-comments-with-author", "post"})
     */
    private ?string $content;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get-comments-with-author"})
     */
    private ?\DateTimeInterface $published;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get-comments-with-author"})
     */
    private User $author;

    /**
     * @ORM\ManyToOne(targetEntity=BlogPost::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"post"})
     */
    private BlogPost $blogPost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): PublishedDateEntityInterface
    {
        $this->published = $published;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(UserInterface $author): AuthoredEntityInterface
    {
        $this->author = $author;

        return $this;
    }

    public function getBlogPost(): BlogPost
    {
        return $this->blogPost;
    }

    public function setBlogPost(BlogPost $blogPost): self
    {
        $this->blogPost = $blogPost;

        return $this;
    }

    public function __toString(): string
    {
        return strlen($this->content) <= 20 ? $this->content : substr($this->content, 0, 20) . "...";
    }
}

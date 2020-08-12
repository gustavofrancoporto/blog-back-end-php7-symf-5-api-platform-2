<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\AuthoredEntityInterface;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\EventSubscriber\AuthoredEntitySubscriber;
use DG\BypassFinals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthoredEntitySubscriberTest extends TestCase
{
    private MockObject $eventMock;
    private MockObject $requestMock;
    private MockObject $tokenMock;
    private MockObject $tokenStorageMock;

    public function setUp()
    {
        BypassFinals::enable();
        $this->eventMock = $this->getMockBuilder(ViewEvent::class)->disableOriginalConstructor()->getMock();
        $this->requestMock = $this->getMockBuilder(Request::class)->getMock();
        $this->tokenMock = $this->getMockBuilder(TokenInterface::class)->getMockForAbstractClass();
        $this->tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)->getMockForAbstractClass();
    }

    public function testConfiguration(): void
    {
        $result = AuthoredEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::VIEW, $result);
        $this->assertSame(
            ['getAuthenticatedUser', EventPriorities::PRE_WRITE],
            $result[KernelEvents::VIEW]
        );
    }

    public function testNoTokenPresent(): void
    {
        $blogPost = new BlogPost();

        $this->eventMock->expects($this->once())->method('getControllerResult')->willReturn($blogPost);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock->expects($this->once())->method('getMethod')->willReturn('POST');
        $this->tokenStorageMock->expects($this->once())->method('getToken')->willReturn(null);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function getUser() on null');

        (new AuthoredEntitySubscriber($this->tokenStorageMock))->getAuthenticatedUser($this->eventMock);
    }

    public function setAuthorDataProvider(): array
    {
        return [
            [new BlogPost(), 'POST', fn ($author, $entity) => $this->assertSame($author, $entity->getAuthor())],
            [new BlogPost(), 'GET',  fn ($author, $entity) => $this->assertNull($entity->getAuthor())],
            [new Comment(), 'POST', fn ($author, $entity) => $this->assertSame($author, $entity->getAuthor())],
            [new Comment(), 'GET',  fn ($author, $entity) => $this->assertNull($entity->getAuthor())],
        ];
    }

    /**
     * @dataProvider setAuthorDataProvider
     *
     * @param string $method
     * @param callable $assertion
     */
    public function testSetAuthor(AuthoredEntityInterface $entity, string $method, callable $assertion): void
    {
        $author = new User();

        $this->eventMock->expects($this->once())->method('getControllerResult')->willReturn($entity);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock->expects($this->once())->method('getMethod')->willReturn($method);
        $this->tokenMock->expects($this->once())->method('getUser')->willReturn($author);
        $this->tokenStorageMock->expects($this->once())->method('getToken')->willReturn($this->tokenMock);

        (new AuthoredEntitySubscriber($this->tokenStorageMock))->getAuthenticatedUser($this->eventMock);

        $assertion($author, $entity);
    }
}
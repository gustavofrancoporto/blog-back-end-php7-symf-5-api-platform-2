<?php


namespace App\EventListener;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();

        if ($throwable->getPrevious() instanceof ItemNotFoundException) {

            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    $throwable->getMessage(),
                    null,
                    [],
                    '',
                    '',
                    ''
                )
            ]);

            $e = new ValidationException($violations);
            $event->setThrowable($e);
        }
    }
}
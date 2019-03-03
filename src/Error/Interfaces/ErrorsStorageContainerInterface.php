<?php


namespace Minwork\Error\Interfaces;


interface ErrorsStorageContainerInterface
{
    public function getErrorsStorage(): ErrorsStorageInterface;
    public function setErrorsStorage(ErrorsStorageInterface $errors): ErrorsStorageContainerInterface;

}
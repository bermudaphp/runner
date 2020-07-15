<?php


namespace Bermuda\RequestHandlerRunner;


use Bermuda\ErrorHandler\ErrorResponseGeneratorInterface;
use Psr\Container\ContainerInterface;
use Bermuda\Pipeline\PipelineInterface;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;


/**
 * Class RequestHandlerRunnerFactory
 * @package Bermuda\RequestHandlerRunner
 */
final class RequestHandlerRunnerFactory
{
    public function __invoke(ContainerInterface $c): RequestHandlerRunner
    {
       return new RequestHandlerRunner($c->get(PipelineInterface::class), $this->getEmitter($c),
            $this->getServerRequestFactory($c), static function(\Throwable $e) use ($c): ResponseInterface
            {
                return $c->get(ErrorResponseGeneratorInterface::class)->generate($e, (new ServerRequestFactory)());
            }
       );
    }
    
    private function getEmitter(ContainerInterface $c): EmitterInterface
    {
        if($c->has(EmitterInterface::class))
        {
            return $c->get(EmitterInterface::class);
        }
        
        return (new EmitterFactory)($c);
    }
    
    private function getServerRequestFactory(ContainerInterface $c): callable
    {
        if($c->has('serverRequestFactory'))
        {
            return $c->get('serverRequestFactory');
        }
        
        return new ServerRequestFactory;
    }
    
    
    private function getServerRequestCreator(): ServerRequestCreator
    {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        return new ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    }
}
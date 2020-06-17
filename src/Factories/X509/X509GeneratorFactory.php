<?php

namespace AndrewSvirin\Ebics\Factories\X509;

use AndrewSvirin\Ebics\Contracts\X509GeneratorInterface;

/**
 * Default X509 generator factory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Guillaume Sainthillier
 */
class X509GeneratorFactory
{
    /** @var string */
    private static $generatorClass = LegacyX509Generator::class;

    /** @var callable|null */
    private static $generatorFunction;

    public static function setGeneratorFunction(callable $generatorFunction) : void
    {
        self::$generatorFunction = $generatorFunction;
    }

    public static function setGeneratorClass(string $generatorClass) : void
    {
        if (!is_a($generatorClass, X509GeneratorInterface::class, true)) {
            throw new \RuntimeException(sprintf('The class "%s" must implements %s', $generatorClass, X509GeneratorInterface::class));
        }

        self::$generatorClass = $generatorClass;
    }

    /**
     * @return X509GeneratorInterface
     */
    public static function create(array $options = []): X509GeneratorInterface
    {
        //Default behaviour
        if (null === self::$generatorFunction) {
            return new self::$generatorClass();
        }

        $generator = \call_user_func(self::$generatorFunction, $options);

        if (null === $generator) {
            throw new \RuntimeException(sprintf('The X509GeneratorFactory::generatorFunction must returns a instance of "%s", none returned', X509GeneratorInterface::class));
        }

        if (!$generator instanceof X509GeneratorInterface) {
            throw new \RuntimeException(sprintf('The class "%s" must implements %s', \get_class($generator), X509GeneratorInterface::class));
        }

        return $generator;
    }
}

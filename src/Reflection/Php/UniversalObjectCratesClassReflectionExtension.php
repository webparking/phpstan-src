<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;

class UniversalObjectCratesClassReflectionExtension
	implements \PHPStan\Reflection\PropertiesClassReflectionExtension, \PHPStan\Reflection\BrokerAwareExtension
{

	/** @var string[] */
	private $classes;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/**
	 * @param string[] $classes
	 */
	public function __construct(array $classes)
	{
		$this->classes = $classes;
	}

	public function setBroker(Broker $broker): void
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return self::isUniversalObjectCrate(
			$this->broker,
			$this->classes,
			$classReflection
		);
	}

	/**
	 * @param \PHPStan\Reflection\ReflectionProvider $reflectionProvider
	 * @param string[] $classes
	 * @param \PHPStan\Reflection\ClassReflection $classReflection
	 * @return bool
	 */
	public static function isUniversalObjectCrate(
		ReflectionProvider $reflectionProvider,
		array $classes,
		ClassReflection $classReflection
	): bool
	{
		foreach ($classes as $className) {
			if (!$reflectionProvider->hasClass($className)) {
				continue;
			}

			if (
				$classReflection->getName() === $className
				|| $classReflection->isSubclassOf($className)
			) {
				return true;
			}
		}

		return false;
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		if ($classReflection->hasNativeMethod('__get')) {
			$readableType = ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('__get')->getVariants())->getReturnType();
		} else {
			$readableType = new MixedType();
		}

		if ($classReflection->hasNativeMethod('__set')) {
			$writableType = ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('__set')->getVariants())->getParameters()[1]->getType();
		} else {
			$writableType = new MixedType();
		}

		return new UniversalObjectCrateProperty($classReflection, $readableType, $writableType);
	}

}

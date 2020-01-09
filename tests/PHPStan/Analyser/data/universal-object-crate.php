<?php

namespace UniversalObjectCrate;

/**
 * @property Baz $baz
 */
class Foo extends \stdClass
{

	/** @var string */
	private $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function doFoo(): void
	{
		$this->doBar($this->name);
		$this->doBaz($this->name); // reported - string passed to int
		$this->doQur($this->baz);
	}

	public function doBar(string $name): void
	{

	}

	public function doBaz(int $i): void
	{

	}

	public function doQur(Baz $baz): void
	{

	}

	public function __get(string $name): Bar
	{
		if ('baz' === $name) {
			return new Baz();
		}

		return new Bar();
	}
}

class Bar extends \stdClass
{

}

class Baz extends Bar
{

}

function () {
	$foo = new Foo('foo');
	$foo->doBaz($foo->name); // reported, private property
};

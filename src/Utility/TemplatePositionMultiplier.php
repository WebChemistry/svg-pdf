<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Utility;

final class TemplatePositionMultiplier
{

	private int $multiplier = 0;

	private int $additional = 0;

	public function __construct(private int $base, private int $multiplyBy, private bool $auto = true)
	{
	}

	public function additional(int $plus): int
	{
		$this->additional += $plus;

		return $this->get();
	}

	public function current(): int
	{
		return $this->getValue();
	}

	public function get(): int
	{
		return $this->auto ? $this->postIncrement() : $this->getValue();
	}

	public function increment(): int
	{
		$this->multiplier++;

		return $this->getValue();
	}

	public function postIncrement(): int
	{
		$value = $this->getValue();
		$this->multiplier++;

		return $value;
	}

	protected function getValue(): int
	{
		return $this->base + $this->getCalculated();
	}

	protected function getCalculated(): int
	{
		return ($this->multiplyBy * $this->multiplier) + $this->additional;
	}

	public function copy(): self
	{
		$self = clone $this;
		$self->multiplier = 0;
		$self->additional = 0;

		return $self;
	}

	public function centerPosition(int $adjustment = 0): int
	{
		return (int) ($adjustment + $this->base + ($this->getCalculated() / 2));
	}

	public function __toString(): string
	{
		return (string) $this->get();
	}

}

<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Utility;

use Contributte\Invoice\Data\IOrder;
use WebChemistry\SvgPdf\Pdf\Color;

final class TemplateUtility
{

	public static function createMoneyFormatter(IOrder $order): callable
	{
		return fn (?string $money) => $money ? $order->getCurrency()->toString($money) : '';
	}

	public static function escape(mixed $string): string
	{
		return $string ? htmlspecialchars((string) $string, ENT_QUOTES) : (string) $string;
	}
	
	public static function multiplier(int $base, int $multiplyBy, bool $auto = true): object
	{
		return new class($base, $multiplyBy, $auto) {

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
				return $this->base + ($this->multiplyBy * $this->multiplier) + $this->additional;
			}

			public function __toString(): string
			{
				return (string) $this->get();
			}

		};
	}

	public static function color(string $color): Color
	{
		return Color::fromString($color);
	}

}

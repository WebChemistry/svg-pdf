<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Pdf;

use InvalidArgumentException;

final class Color
{

	public function __construct(
		private int $red,
		private int $green,
		private int $blue,
	)
	{
	}

	public function getRed(): int
	{
		return $this->red;
	}

	public function getGreen(): int
	{
		return $this->green;
	}

	public function getBlue(): int
	{
		return $this->blue;
	}

	public function greyscale(): Color
	{
		$color = 0.3 * $this->red + 0.59 * $this->green + 0.11 * $this->blue;
		$color = max(min((int) $color, 255), 0);

		return new Color($color, $color, $color);
	}

	protected function adjustColor(int $dimension): int
	{
		return max(0, min(255, $dimension));
	}

	protected function lightenDarken(int $percentage): Color
	{
		$percentage = round($percentage / 100, 2);

		return new Color(
			$this->adjustColor((int) ($this->red - ($this->red * $percentage))),
			$this->adjustColor((int) ($this->green - ($this->green * $percentage))),
			$this->adjustColor((int) ($this->blue - ($this->blue * $percentage)))
		);
	}

	public function lighten(int $percentage): Color
	{
		$percentage = max(0, min(100, $percentage));

		return $this->lightenDarken(-$percentage);
	}

	public function darken(int $percentage): Color
	{
		$percentage = max(0, min(100, $percentage));

		return $this->lightenDarken($percentage);
	}

	public static function black(): Color
	{
		return new Color(0, 0, 0);
	}

	public static function white(): Color
	{
		return new Color(255, 255, 255);
	}

	public static function fromString(string $color): Color
	{
		if (preg_match('@#((?:[0-9A-Fa-f]{3}){1,2})@', $color, $matches)) {
			$hex = strlen($matches[1]) === 3 ? str_repeat($matches[1], 2) : $matches[1];

			return new Color(...array_map('hexdec', str_split($hex, 2)));
		}

		if (preg_match('@rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)@', $color, $matches)) {
			return new Color(...array_map('intval', array_slice($matches, 1)));
		}

		throw new InvalidArgumentException(sprintf('Color %s is not valid. Only hex and rgb colors are supported.', $color));
	}

	public function __toString(): string
	{
		return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
	}

}

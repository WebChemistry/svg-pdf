<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Utility;

use Contributte\Invoice\Data\IOrder;
use WebChemistry\SvgPdf\Pdf\Color;
use WebChemistry\SvgPdf\PdfSvg;

final class TemplateUtility
{

	public static function createMaxWidthCatcher(PdfSvg $pdfSvg, int|float $documentWidth): TemplateMaxWidthCatcher
	{
		return new TemplateMaxWidthCatcher($pdfSvg, $documentWidth);
	}

	public static function createMoneyFormatter(IOrder $order): callable
	{
		return fn (?string $money) => $money ? $order->getCurrency()->toString($money) : '';
	}

	public static function escape(mixed $string): string
	{
		return $string ? htmlspecialchars((string) $string, ENT_QUOTES) : (string) $string;
	}

	public static function multiplier(int $base, int $multiplyBy, bool $auto = true): TemplatePositionMultiplier
	{
		return new TemplatePositionMultiplier($base, $multiplyBy, $auto);
	}

	public static function color(string $color): Color
	{
		return Color::fromString($color);
	}

}

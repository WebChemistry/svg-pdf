<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Utility;

use WebChemistry\SvgPdf\Element\TextElement;
use WebChemistry\SvgPdf\PdfSvg;

final class TemplateMaxWidthCatcher
{

	private string $content;

	public function __construct(
		private PdfSvg $pdfSvg,
		private int|float $documentWidth)
	{
	}

	public function start(): void
	{
		ob_start();
	}

	public function endAndGetContent(): string
	{
		$this->content = ob_get_clean();
		
		return $this->content;
	}

	public function getWidth(bool $wrapDocument = false): float
	{
		$content = $this->content;
		if ($wrapDocument) {
			$content = sprintf('<svg xmlns="http://www.w3.org/2000/svg">%s</svg>', $content);
		}

		$width = 0.0;
		$collection = $this->pdfSvg->createElementCollectionFromString($content);
		/** @var TextElement $element */
		foreach ($collection->instanceOf(TextElement::class) as $element) {
			$current = $element->width($this->pdfSvg->createRenderer($this->documentWidth));
			if ($current > $width) {
				$width = $current;
			}
		}

		return $width;
	}

}

<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Element;

use WebChemistry\SvgPdf\Pdf\Color;
use WebChemistry\SvgPdf\Pdf\Pdf;

final class TextElement extends Element
{

	private int $fontSize;
	private int $x;
	private int $y;
	private ?int $width;
	private ?int $lineHeight;
	private ?int $border;
	private ?string $textAnchor;
	private ?string $fontWeight;
	private ?Color $color;

	protected function initialize(): void
	{
		$this->fontSize = $this->attrInt('font-size', required: true);
		$this->x = $this->attrInt('x', required: true);
		$this->y = $this->attrInt('y', required: true);
		$this->width = $this->attrInt('data-pdf-width');
		$this->lineHeight = $this->attrInt('data-pdf-lineHeight');
		$this->border = $this->attrInt('data-pdf-border', 0);

		$this->fontWeight = $this->attrString('font-weight', 'normal');
		$this->textAnchor = $this->attrString('text-anchor', 'start');

		$color = $this->attrString('fill');
		$this->color = $color ? Color::fromString($color) : null;
	}

	public function render(Pdf $pdf, array $options = []): void
	{
		$pdf->text(
			$this->x,
			$this->y,
			(string) $this->element,
			$this->color,
			$this->textAnchor,
			null,
			$this->fontWeight,
			$this->fontSize,
			$this->width,
			$this->lineHeight,
			$this->border,
		);
	}

	public function width(Pdf $pdf): float
	{
		return $pdf->textWidth(
			(string) $this->element,
			$this->fontSize,
			null, 
			$this->fontWeight,
		);
	}

}

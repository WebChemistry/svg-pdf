<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Element;

use WebChemistry\SvgPdf\Pdf\Color;
use WebChemistry\SvgPdf\Pdf\Pdf;

final class RectElement extends Element
{

	private int $x;
	private int $y;
	private int $width;
	private int $height;
	private ?Color $fill;
	private ?Color $stroke;
	
	protected function initialize(): void
	{
		$this->x = $this->attrInt('x', required: true);
		$this->y = $this->attrInt('y', required: true);
		$this->width = $this->attrInt('width', required: true);
		$this->height = $this->attrInt('height', required: true);
		
		$fill = $this->attrString('fill');
		$stroke = $this->attrString('stroke');
		
		$this->fill = $fill ? Color::fromString($fill) : null;
		$this->stroke = $stroke ? Color::fromString($stroke) : null;
	}

	public function render(Pdf $pdf, array $options = []): void
	{
		$pdf->rect(
			$this->x,
			$this->y,
			$this->width,
			$this->height,
			$this->stroke,
			$this->fill,
		);
	}

}

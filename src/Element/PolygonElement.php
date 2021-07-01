<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Element;

use WebChemistry\SvgPdf\Pdf\Color;
use WebChemistry\SvgPdf\Pdf\Pdf;

final class PolygonElement extends Element
{

	/** @var float[] */
	private array $points;

	private ?Color $fill;
	private ?Color $stroke;

	protected function initialize(): void
	{
		$points = $this->attrString('points', required: true);
		$fill = $this->attrString('fill');
		$stroke = $this->attrString('stroke');

		$this->points = array_map(
			fn (string $point) => (float) $point,
			explode(',', preg_replace('#\s+#', ',', $points)),
		);
		$this->fill = $fill ? Color::fromString($fill) : null;
		$this->stroke = $stroke ? Color::fromString($stroke) : null;
	}

	public function render(Pdf $pdf, array $options = []): void
	{
		$pdf->polygon(
			$this->points,
			$this->stroke,
			$this->fill,
		);
	}

}

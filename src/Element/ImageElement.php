<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Element;

use LogicException;
use SimpleXMLElement;
use WebChemistry\SvgPdf\Pdf\Pdf;

final class ImageElement extends Element
{

	private ?int $x;

	private ?int $y;

	private ?int $width;

	private ?int $height;

	private string $src;

	public function __construct(
		SimpleXMLElement $element,
		private string $imagePath,
	)
	{
		parent::__construct($element);
	}

	protected function initialize(): void
	{
		$this->x = $this->attrInt('x');
		$this->y = $this->attrInt('y');
		$this->width = $this->attrInt('width');
		$this->height = $this->attrInt('height');
		$this->src = $this->imagePath . '/' . ltrim($this->attrString('href'), './');

		if (!file_exists($this->src)) {
			throw new LogicException(sprintf('Image %s not exists.', $this->src));
		}
	}

	public function render(Pdf $pdf, array $options = []): void
	{
		$pdf->image($this->src, $this->x, $this->y, $this->width, $this->height);
	}

}

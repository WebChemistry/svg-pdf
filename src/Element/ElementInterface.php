<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Element;

use WebChemistry\SvgPdf\Pdf\Pdf;

interface ElementInterface
{

	public function render(Pdf $pdf, array $options = []): void;

}

<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Decorator;

use FPDF;

final class PdfDecorator extends FPDF
{

	public function setFontPath(string $path): void
	{
		$this->fontpath = $path;
	}

}

<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Decorator;

use FPDF;

final class PdfDecorator extends FPDF
{

	public function setFontPath(string $path): void
	{
		$this->fontpath = $path;
	}

	public function getTopMargin(): float
	{
		return $this->tMargin;
	}

	/**
	 * @param array<int|float> $points
	 */
	public function polygon(array $points, string $style = 'D'): void
	{
		//Draw a polygon
		if ($style === 'F') {
			$op = 'f';
		} elseif ($style === 'FD' || $style === 'DF') {
			$op = 'b';
		} else {
			$op = 's';
		}

		$h = $this->h;
		$k = $this->k;

		$points_string = '';
		for ($i = 0; $i < count($points); $i += 2) {
			$points_string .= sprintf('%.2F %.2F', $points[$i] * $k, ($h - $points[$i + 1]) * $k);
			if ($i == 0) {
				$points_string .= ' m ';
			} else {
				$points_string .= ' l ';
			}
		}
		$this->_out($points_string . $op);
	}

}

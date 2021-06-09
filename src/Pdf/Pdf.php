<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Pdf;

use FPDF;
use InvalidArgumentException;
use WebChemistry\SvgPdf\Decorator\PdfDecorator;

final class Pdf
{

	private PdfDecorator $pdf;

	private Color $defaultTextColor;

	private bool $greyscale = false;

	public function __construct(
		private string $defaultFontFamily,
		?Color $defaultTextColor = null,
	)
	{
		$this->pdf = new PdfDecorator('P', 'pt', 'A4');
		$this->defaultTextColor = $defaultTextColor ?? Color::black();
	}

	public function getSource(): FPDF
	{
		return $this->pdf;
	}

	public function addFont(string $family, string $file, string $style = ''): self
	{
		$pos = strrpos($file, '/');
		if ($pos === false) {
			throw new InvalidArgumentException('File must be absolute path.');
		}

		$path = substr($file, 0, $pos + 1);
		$file = substr($file, $pos + 1);

		$this->pdf->setFontPath($path);
		$this->pdf->AddFont($family, $style, $file);

		return $this;
	}

	public function rect(float $x, float $y, float $width, float $height, ?Color $stroke = null, ?Color $fill = null): void
	{
		if ($this->greyscale) {
			$stroke = $stroke?->greyscale();
			$fill = $fill?->greyscale();
		}

		$mode = match (true) {
			$stroke && $fill => 'FD',
			(bool) $fill => 'F',
			default => '',
		};

		if ($stroke) {
			$this->pdf->SetDrawColor($stroke->getRed(), $stroke->getGreen(), $stroke->getBlue());
		}

		if ($fill) {
			$this->pdf->SetFillColor($fill->getRed(), $fill->getGreen(), $fill->getBlue());
		}

		$this->pdf->Rect($x, $y, $width, $height, $mode);
	}

	public function image(string $file, float $x, float $y, float $width, float $height): void
	{
		$this->pdf->Image($file, $x, $y, $width, $height);
	}

	public function text(
		float $x,
		float $y,
		string $text,
		?Color $color = null,
		string $align = 'start',
		?string $fontFamily = null,
		string $fontStyle = 'normal',
		float $fontSize = 0,
		?float $width = null,
		?float $lineHeight = null,
		int $border = 0,
	): void
	{
		$color ??= $this->defaultTextColor;
		$fontFamily ??= $this->defaultFontFamily;

		if ($this->greyscale) {
			$color = $color?->greyscale();
		}

		if ($fontStyle === 'normal') {
			$fontStyle = '';
		}

		$this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);
		$this->pdf->SetTextColor($color->getRed(), $color->getGreen(), $color->getBlue());

		if ($width === null) {
			$adjust = match ($align) {
				'middle' => - ($this->pdf->GetStringWidth($text) / 2),
				'end' => - $this->pdf->GetStringWidth($text),
				default => 0,
			};

			$this->pdf->Text($x + $adjust, $y, $text);
		} else {
			$align = match ($align) {
				'middle' => 'C',
				'end' => 'R',
				default => 'L',
			};

			$lineHeight = $lineHeight ?: $fontSize + 2;
			$this->pdf->SetXY($x, $y);
			$this->pdf->MultiCell($width, $lineHeight, $text, $border, $align);
		}
	}

	public function sendBrowser(): void
	{
		header('Content-Type: application/pdf');
		echo $this->pdf->Output('S');
	}

	public function setGreyscale(bool $greyscale): self
	{
		$this->greyscale = $greyscale;

		return $this;
	}

}

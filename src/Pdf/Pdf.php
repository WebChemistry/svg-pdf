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

	private float $limitHeight;

	private float $adjustHeight;

	private ?float $scale = null;

	public function __construct(
		private string $defaultFontFamily,
		?Color $defaultTextColor = null,
		private bool $autoBreak = true,
	)
	{
		$this->pdf = new PdfDecorator('P', 'pt', 'A4');
		$this->pdf->AddPage('P', 'A4');
		$this->pdf->SetAutoPageBreak(false);

		$this->defaultTextColor = $defaultTextColor ?? Color::black();

		$this->updateLimitHeight();
	}

	public function setScale(?float $scale): void
	{
		$this->scale = $scale;
	}

	private function updateLimitHeight(): void
	{
		$this->adjustHeight = $this->limitHeight ?? 0;
		$this->limitHeight = $this->pdf->PageNo() * ($this->pdf->GetPageHeight() - $this->pdf->getTopMargin());
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
		// scale
		$x = $this->scale($x);
		$y = $this->scale($y);
		$width = $this->scale($width);
		$height = $this->scale($height);
		// end scale

		$y = $this->adjustY($y, $height);

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

	/**
	 * @param array<int|float> $points
	 */
	public function polygon(array $points, ?Color $stroke = null, ?Color $fill = null): void
	{
		foreach ($points as $i => $point) {
			$points[$i] = $this->scale($point);

			if ($i % 2 === 1) {
				$points[$i] = $this->adjustY($point);
			}
		}

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

		$this->pdf->polygon($points, $mode);
	}

	public function image(string $file, float $x, float $y, float $width, float $height): void
	{
		$y = $this->adjustY($y);

		$this->pdf->Image($file, $x, $y, $width, $height);
	}

	public function textWidth(
		string $text,
		float $fontSize = 0,
		?string $fontFamily = null,
		string $fontStyle = 'normal',
	): float
	{
		$fontSize = $this->scale($fontSize);
		$color ??= $this->defaultTextColor;
		$fontFamily ??= $this->defaultFontFamily;

		$this->pdf->SetFont($fontFamily, $fontStyle, $fontSize);

		return $this->pdf->GetStringWidth($text);
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
		// scale
		$x = $this->scale($x);
		$y = $this->scale($y);
		$fontSize = $this->scale($fontSize);
		$width = $this->scale($width);
		$lineHeight = $this->scale($lineHeight);
		// end scale

		$text = iconv('utf-8', 'cp1250//translit', $text);
		$color ??= $this->defaultTextColor;
		$fontFamily ??= $this->defaultFontFamily;

		$y = $this->adjustY($y);

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
		echo $this->pdf->Output('S', isUTF8: true);
	}

	public function toString(): string
	{
		return $this->pdf->Output('S', isUTF8: true);
	}

	public function setGreyscale(bool $greyscale): self
	{
		$this->greyscale = $greyscale;

		return $this;
	}

	private function adjustY(float $y, float $additional = 0): ?float
	{
		if (($y + $additional) > $this->limitHeight) {
			$this->pdf->AddPage('P', 'A4');

			$this->updateLimitHeight();
		}

		if ($this->pdf->PageNo() > 1) {
			$y = $y - $this->adjustHeight;
		}

		return $y;
	}

	private function scale(float|int|null $value): ?int
	{
		if ($this->scale === null) {
			return $value;
		}

		if ($value === null) {
			return null;
		}

		if ($this->scale === 1) {
			return $value;
		}

		return (int) round($value / $this->scale);
	}

}

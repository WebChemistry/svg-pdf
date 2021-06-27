<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf;

use LogicException;
use SimpleXMLElement;
use WebChemistry\SvgPdf\Pdf\Color;
use WebChemistry\SvgPdf\Pdf\Pdf;

final class PdfSvg
{

	private array $fonts = [];

	private float $scale;

	private Pdf $pdf;

	private bool $greyscale = false;

	private ?string $imagePath = null;

	public function enableGreyscale(): self
	{
		$this->greyscale = true;

		return $this;
	}

	public function addFont(string $family, string $file, string $style = ''): self
	{
		$this->fonts[] = [$family, $file, $style];
		return $this;
	}

	public function setImagePath(?string $imagePath): self
	{
		$this->imagePath = $imagePath;

		return $this;
	}

	public function toPdf(string $content): Pdf
	{
		$document = simplexml_load_string($this->purgeXml($content));
		$this->pdf = $pdf = new Pdf($this->fonts[array_key_first($this->fonts)][0]);
		$pdf->setGreyscale($this->greyscale);
		foreach ($this->fonts as $font) {
			$pdf->addFont(...$font);
		}

		$this->scale = $this->attrInt($document, 'width', required: true) / $this->pdf->getSource()->GetPageWidth();
		foreach ($document as $element) {
			$method = 'element' . ucfirst($element->getName());
			if (!method_exists($this, $method)) {
				throw new LogicException('Method no exists');
			}

			$this->$method($element);
		}

		return $pdf;
	}

	protected function elementImage(SimpleXMLElement $element): void
	{
		if (!$this->imagePath) {
			throw new LogicException('Image path must be set for <image>.');
		}

		$x = $this->attrInt($element, 'x');
		$y = $this->attrInt($element, 'y');
		$width = $this->attrInt($element, 'width');
		$height = $this->attrInt($element, 'height');
		$src = $this->imagePath . '/' . ltrim($this->attrString($element, 'href'), './');

		if (!file_exists($src)) {
			throw new LogicException(sprintf('Image %s not exists.', $src));
		}

		$this->pdf->image($src, $x, $y, $width, $height);
	}

	protected function elementSwitch(SimpleXMLElement $element): void
	{
		foreach ($element as $el) {
			$method = 'element' . ucfirst($el->getName());
			if (!method_exists($this, $method)) {
				continue;
			}

			$this->$method($el);
		}
	}

	protected function elementText(SimpleXMLElement $element): void
	{
		$fontSize = $this->attrInt($element, 'font-size', required: true);
		$x = $this->attrInt($element, 'x', required: true);
		$y = $this->attrInt($element, 'y', required: true);
		$color = $this->attrString($element, 'fill');
		$width = $this->attrInt($element, 'data-pdf-width');
		$lineHeight = $this->attrInt($element, 'data-pdf-lineHeight');
		$border = $this->attrInt($element, 'data-pdf-border', 0, scale: false);

		$this->pdf->text(
			$x,
			$y,
			(string) $element,
			$color ? Color::fromString($color) : null,
			$this->attrString($element, 'text-anchor', 'start'),
			null,
			$this->attrString($element, 'font-weight', 'normal'),
			$fontSize,
			$width,
			$lineHeight,
			$border,
		);
	}

	protected function elementRect(SimpleXMLElement $element): void
	{
		$x = $this->attrInt($element, 'x', required: true);
		$y = $this->attrInt($element, 'y', required: true);
		$width = $this->attrInt($element, 'width', required: true);
		$height = $this->attrInt($element, 'height', required: true);
		$fill = $this->attrString($element, 'fill');
		$stroke = $this->attrString($element, 'stroke');

		$this->pdf->rect(
			$x,
			$y,
			$width,
			$height,
			$stroke ? Color::fromString($stroke) : null,
			$fill ? Color::fromString($fill) : null,
		);
	}

	protected function attrString(
		SimpleXMLElement $element,
		string $name,
		?string $default = null,
		bool $required = false
	): ?string
	{
		$attrs = $element->attributes();
		if (!$attrs) {
			return null;
		}
		$value = $attrs[$name];
		$value = $value === null ? null : (string) $value;

		if ($value === null) {
			if ($required) {
				throw new LogicException(sprintf('Element %s must have attribute %s', $element->getName(), $name));
			}

			$value = $default;
		}

		return $value;
	}

	protected function attrInt(
		SimpleXMLElement $element,
		string $name,
		?int $default = null,
		bool $required = false,
		bool $scale = true,
	): ?int
	{
		$value = $this->attrString($element, $name, null, $required);

		if ($value === null) {
			return $default;
		}

		if (!$scale) {
			return (int) $value;
		}

		return isset($this->scale) ? (int) round($value / $this->scale) : (int) $value;
	}

	private function purgeXml(string $content): string
	{
		// remove comments
		$content = preg_replace('#<!--(.*?)-->#', '', $content);

		// remove <style>
		$content = preg_replace('#<style>(.*?)</style>#s', '', $content);

		// &nbsp; -> space
		return preg_replace('#&nbsp;#', ' ', $content);
	}

}

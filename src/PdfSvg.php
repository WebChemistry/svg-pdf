<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf;

use LogicException;
use SimpleXMLElement;
use WebChemistry\SvgPdf\Element\ElementCollection;
use WebChemistry\SvgPdf\Element\ImageElement;
use WebChemistry\SvgPdf\Element\PolygonElement;
use WebChemistry\SvgPdf\Element\RectElement;
use WebChemistry\SvgPdf\Element\TextElement;
use WebChemistry\SvgPdf\Pdf\Pdf;
use WebChemistry\SvgPdf\Utility\XmlUtility;

final class PdfSvg
{

	private array $fonts = [];

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

	public function createElementCollectionFromString(string $content): ElementCollection
	{
		$document = simplexml_load_string(XmlUtility::purgeXml($content));

		return $this->createElementCollectionFromDocument($document);
	}

	protected function createElementCollectionFromDocument(SimpleXMLElement $document): ElementCollection
	{
		$collection = new ElementCollection($document);

		foreach ($document->children() as $element) {
			$name = strtolower($element->getName());

			$object = match($name) {
				'text' => $this->createElementText($element),
				'rect' => $this->createElementRect($element),
				'polygon' => $this->createElementPolygon($element),
				'image' => $this->createElementImage($element),
				'switch' => $this->createElementCollectionFromDocument($element),
				default => throw new LogicException(sprintf('Element %s not currently supported.', $name))
			};

			$collection->add($object);
		}

		return $collection;
	}

	public function renderFromString(string $content): Pdf
	{
		$document = simplexml_load_string(XmlUtility::purgeXml($content));

		return $this->renderFromElementCollection($this->createElementCollectionFromDocument($document));
	}

	public function renderFromElementCollection(ElementCollection $collection): Pdf
	{
		$pdf = $this->createRenderer(XmlUtility::attrInt($collection->getDocument(), 'width', required: true));
		foreach ($collection->getElements() as $element) {
			$element->render($pdf);
		}

		return $pdf;
	}

	public function createRenderer(int|float $documentWidth): Pdf
	{
		$pdf = new Pdf($this->fonts[array_key_first($this->fonts)][0]);
		$pdf->setGreyscale($this->greyscale);
		foreach ($this->fonts as $font) {
			$pdf->addFont(...$font);
		}

		$scale = $documentWidth / $pdf->getSource()->GetPageWidth();
		$pdf->setScale($scale);

		return $pdf;
	}

	public function toPdf(string $content): Pdf
	{
		return $this->renderFromString($content);
	}

	protected function createElementImage(SimpleXMLElement $element): ImageElement
	{
		if (!$this->imagePath) {
			throw new LogicException('Image path must be set for <image>.');
		}

		return new ImageElement($element, $this->imagePath);
	}

	protected function createElementText(SimpleXMLElement $element): TextElement
	{
		return new TextElement($element);
	}

	protected function createElementRect(SimpleXMLElement $element): RectElement
	{
		return new RectElement($element);
	}

	protected function createElementPolygon(SimpleXMLElement $element): PolygonElement
	{
		return new PolygonElement($element);
	}

}

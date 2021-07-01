<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Element;

use SimpleXMLElement;

final class ElementCollection
{

	/**
	 * @param ElementInterface[] $elements
	 */
	public function __construct(
		private SimpleXMLElement $document,
		private array $elements = [],
	)
	{
	}

	public function getDocument(): SimpleXMLElement
	{
		return $this->document;
	}

	public function add(ElementInterface|ElementCollection $element): void
	{
		if ($element instanceof ElementCollection) {
			foreach ($element->getElements() as $element) {
				$this->elements[] = $element;
			}

			return;
		}

		$this->elements[] = $element;
	}

	/**
	 * @return ElementInterface[]
	 */
	public function getElements(): array
	{
		return $this->elements;
	}

	/**
	 * @return ElementInterface[]
	 */
	public function instanceOf(string $instanceOf): array
	{
		return array_filter(
			$this->elements,
			fn (ElementInterface $element) => $element instanceof $instanceOf,
		);
	}

}

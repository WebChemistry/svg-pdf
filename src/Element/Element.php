<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Element;

use SimpleXMLElement;
use WebChemistry\SvgPdf\Utility\XmlUtility;

abstract class Element implements ElementInterface
{

	public function __construct(
		protected SimpleXMLElement $element,
	)
	{
		$this->initialize();
	}

	abstract protected function initialize(): void;

	protected function attrString(
		string $name,
		?string $default = null,
		bool $required = false
	): ?string
	{
		return XmlUtility::attrString($this->element, $name, $default, $required);
	}

	protected function attrInt(
		string $name,
		?int $default = null,
		bool $required = false,
	): ?int
	{
		return XmlUtility::attrInt($this->element, $name, $default, $required);
	}

}

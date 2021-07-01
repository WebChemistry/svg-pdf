<?php declare(strict_types = 1);

namespace WebChemistry\SvgPdf\Utility;

use LogicException;
use SimpleXMLElement;

final class XmlUtility
{

	public static function attrString(
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

	public static function attrInt(
		SimpleXMLElement $element,
		string $name,
		?int $default = null,
		bool $required = false,
	): ?int
	{
		$value = self::attrString($element, $name, null, $required);

		if ($value === null) {
			return $default;
		}

		return (int) $value;
	}

	public static function purgeXml(string $content): string
	{
		// remove comments
		$content = preg_replace('#<!--(.*?)-->#', '', $content);

		// remove <style>
		$content = preg_replace('#<style>(.*?)</style>#s', '', $content);

		// &nbsp; -> space
		return preg_replace('#&nbsp;#', ' ', $content);
	}

}

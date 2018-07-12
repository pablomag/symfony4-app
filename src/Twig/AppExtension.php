<?php

namespace App\Twig;

use App\Entity\LikeNotification;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
	/**
	 * @var string
	 */
	private $locale;

	public function __construct(string $locale)
	{
		$this->locale = $locale;
	}

	public function getFilters()
	{
		return
		[
			new TwigFilter('price', [$this, 'priceFilter']),
			new TwigFilter('toHex', [$this, 'toHexFilter'])
		];
	}

	public function getGlobals()
	{
		return
		[
			'locale' => $this->locale
		];
	}

	public function priceFilter($number)
	{
		return '$ '.number_format($number, 2, '.', ',');
	}

	public function getTests()
	{
		return [
			new \Twig_SimpleTest('like', function($obj)
			{
				return $obj instanceof LikeNotification;
			})
		];
	}

	public function toHexFilter($string)
	{
		$h1 = bin2hex(substr($string, 0, 1));
		$h2 = bin2hex(substr($string, 1, 1));

		$hex = '';

		switch ($h1)
		{
			case $h1 > 44:
				$hex .= substr($h1, 1, 1).substr($h1, 0, 1);
				$hex .= substr($h2, 1, 1).substr($h2, 0, 1);
				$hex .= "c".substr($h2, 1, 1);
				break;
			case $h1 > 38:
				$hex .= "f".substr($h1, 0, 1);
				$hex .= "9".substr($h2, 1, 1);
				$hex .= substr($h1, 1, 1).substr($h2, 1, 1);
				break;
			case $h1 > 28:
				$hex .= substr($h2, 0, 1).substr($h1, 0, 1);
				$hex .= substr($h1, 0, 1).substr($h2, 1, 1);
				$hex .= "a".substr($h2, 1, 1);
				break;
			case $h1 > 18:
				$hex .= substr($h1, 1, 1).substr($h1, 0, 1);
				$hex .= "c".substr($h2, 1, 1);
				$hex .= substr($h2, 0, 1).substr($h2, 1, 1);
				break;
			default:
				$hex .= substr($h1, 1, 1).substr($h1, 0, 1);
				$hex .= substr($h1, 0, 1).substr($h2, 1, 1);
				$hex .= substr($h2, 0, 1).substr($h2, 1, 1);
				break;
		}

		return $hex;
	}
}

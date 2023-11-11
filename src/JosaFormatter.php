<?php

/**
 *    __                    __      _                 _
 *    \ \  ___  ___  __ _  /__\_  _| |_ ___ _ __  ___(_) ___  _ __
 *     \ \/ _ \/ __|/ _` |/_\ \ \/ / __/ _ \ '_ \/ __| |/ _ \| '_ \
 *  /\_/ / (_) \__ \ (_| //__  >  <| ||  __/ | | \__ \ | (_) | | | |
 *  \___/ \___/|___/\__,_\__/ /_/\_\__\___|_| |_|___/_|\___/|_| |_|
 *
 * MIT License
 *
 * Copyright (c) 2023 - present alvin0319, b1uec0in and contributors.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 */

declare(strict_types=1);

namespace alvin0319\JosaExtension;

use alvin0319\JosaExtension\detector\EnglishCapitalJongSungDetector;
use alvin0319\JosaExtension\detector\EnglishJongSungDetector;
use alvin0319\JosaExtension\detector\EnglishNumberKoreanStyleJongSungDetector;
use alvin0319\JosaExtension\detector\HangulJongSungDetector;
use alvin0319\JosaExtension\detector\JapaneseJongSungDetector;
use alvin0319\JosaExtension\detector\JongSungDetector;
use alvin0319\JosaExtension\detector\NumberJongSungDetector;
use alvin0319\JosaExtension\util\FormattedString;
use alvin0319\JosaExtension\util\StringBuilder;
use function array_map;
use function count;
use function ctype_space;
use function gettype;
use function is_null;
use function is_object;
use function is_scalar;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function method_exists;
use function preg_match;
use function str_ends_with;
use function str_replace;
use function strlen;
use function substr;
use function substr_replace;
use function vsprintf;
use const PREG_OFFSET_CAPTURE;

final class JosaFormatter{

	private static ?JosaFormatter $formatter = null;

	public static function getDefaultFormatter() : JosaFormatter{
		return self::$formatter ??= new JosaFormatter();
	}

	public static function setDefaultFormatter(JosaFormatter $formatter) : void{
		self::$formatter = $formatter;
	}

	public static function formatString(string $format, mixed ...$args) : string{
		$formatter = self::getDefaultFormatter();
		return $formatter->format($format, ...$args);
	}

	/** @phpstan-var list<array{0: string, 1: string}> first => second */
	private array $readingRules = [
		["아이폰", "iPhone"],
		["갤럭시", "Galaxy"],
		["넘버", "Number"]
	];

	/** @phpstan-var list<array{0: string, 1: string}> */
	private static array $josaPairs = [
		["은", "는"],
		["이", "가"],
		["을", "를"],
		["과", "와"],
		["으로", "로"]
	];

	/** @phpstan-var list<JongSungDetector> */
	private array $detectors = [];

	/** @phpstan-param list<JongSungDetector> $detectors */
	public function __construct(array $detectors = []){
		(function(JongSungDetector ...$detector) : void{ })(...$detectors); // runtime type validation
		if(count($detectors) === 0){
			$this->detectors = [
				new HangulJongSungDetector(),
				new EnglishCapitalJongSungDetector(),
				new EnglishJongSungDetector(),
				// new EnglishNumberJongSungDetector
				new EnglishNumberKoreanStyleJongSungDetector(),
				new NumberJongSungDetector(),
				new JapaneseJongSungDetector()
			];
		}
	}

	/** @phpstan-return list<JongSungDetector> */
	public function getDetectors() : array{
		return $this->detectors;
	}

	public function addReadingRule(string $first, string $second) : void{
		$this->readingRules[] = [$first, $second];
	}

	/** @phpstan-return list<FormattedString> */
	private static function parseFormat(string $format, string ...$args) : array{
		$formattedStrings = [];
		$simpleFormatRegex = '/%(\\d+\\$?\\<?)?([-#+ 0,(]*)?(\\d+)?(\\.\\d+)?([bBhHsScCdoxXeEfgGaAtT%])/';

		$prevMatcherEnd = 0;
		$argIndex = 0;
		$lastArgIndex = -1;

		while(preg_match($simpleFormatRegex, $format, $matches, PREG_OFFSET_CAPTURE, $prevMatcherEnd)){
			$start = $matches[0][1];

			if($start > $prevMatcherEnd){
				$prevText = substr($format, $prevMatcherEnd, $start - $prevMatcherEnd);
				$formattedStrings[] = new FormattedString($prevText, false);
			}

			$singleFormat = $matches[0][0];
			$indexString = $matches[1][0];
			$conversion = $matches[5][0];

			if($conversion == '%'){
				$formattedStrings[] = new FormattedString($singleFormat, false);
			}else{
				$index = -1;

				if(!empty($indexString)){
					if($indexString == '<'){
						$index = $lastArgIndex;
					}elseif(str_ends_with($indexString, '$')){
						try{
							$index = (int) substr($indexString, 0, -1) - 1;
						}catch(\Exception $e){
							$index = 0;
						}
						$lastArgIndex = $index;
					}

					$singleFormat = substr_replace($format, '', $matches[1][1], strlen($matches[1][0])) .
						substr_replace($format, '', $matches[2][1], strlen($matches[2][0]));
				}else{
					$index = $argIndex++;
					$lastArgIndex = $index;
				}

				$singleFormattedString = vsprintf($singleFormat, [$args[$index]]);
				$formattedStrings[] = new FormattedString($singleFormattedString, true);
			}

			$prevMatcherEnd = $matches[0][1] + strlen($matches[0][0]);
		}

		if(strlen($format) > $prevMatcherEnd){
			$prevText = substr($format, $prevMatcherEnd);
			$formattedStrings[] = new FormattedString($prevText, false);
		}

		return $formattedStrings;
	}

	public function format(string $format, mixed ...$args) : string{
		$formattedStrings = self::parseFormat($format, ...array_map(function(mixed $value) : string{
			if(is_scalar($value) || is_null($value) || (is_object($value) && method_exists($value, '__toString'))){
				return (string) $value;
			}
			throw new \InvalidArgumentException("Cannot cast " . gettype($value) . " to string");
		}, $args));

		$count = count($formattedStrings);

		$sb = new StringBuilder($formattedStrings[0]->toString());

		if($count === 1){
			return $sb->toString();
		}

		for($i = 1; $i < $count; ++$i){
			$formattedString = $formattedStrings[$i];

			if(!$formattedString->isFormatted()){
				$str = $this->getJosaModifiedString($formattedStrings[$i - 1]->toString(), $formattedString->toString());
			}else{
				$str = $formattedString->toString();
			}

			$sb->append($str);
		}

		return $sb->toString();
	}

	private static function indexOfJosa(string $str, string $josa) : int{
		$index = -1;
		$searchFromIndex = 0;
		$strLength = mb_strlen($str);
		$josaLength = mb_strlen($josa);

		do{
			$index = mb_strpos($str, $josa, $searchFromIndex);

			if($index !== false){
				$josaNext = $index + $josaLength;

				// 조사로 끝나거나 뒤에 공백이 있어야 함.
				if($josaNext < $strLength){
					if(ctype_space(mb_substr($str, $josaNext, 1))){
						return $index;
					}
				}else{
					return $index;
				}
			}else{
				return -1;
			}

			$searchFromIndex = $index + $josaLength;

		}while($searchFromIndex < $strLength);

		return -1;
	}

	public function getJosaModifiedString(string $previous, string $str) : string{
		if(mb_strlen($previous) == 0){
			return $str;
		}

		$matchedJosaPair = null;
		$josaIndex = -1;

		$searchStr = null;

		foreach(self::$josaPairs as $josaPair){
			$firstIndex = $this->indexOfJosa($str, $josaPair[0]);
			$secondIndex = $this->indexOfJosa($str, $josaPair[1]);

			if($firstIndex >= 0 && $secondIndex >= 0){
				if($firstIndex < $secondIndex){
					$josaIndex = $firstIndex;
					$searchStr = $josaPair[0];
				}else{
					$josaIndex = $secondIndex;
					$searchStr = $josaPair[1];
				}
			}elseif($firstIndex >= 0){
				$josaIndex = $firstIndex;
				$searchStr = $josaPair[0];
			}elseif($secondIndex >= 0){
				$josaIndex = $secondIndex;
				$searchStr = $josaPair[1];
			}

			if($josaIndex >= 0 && $this->isEndSkipText($str, 0, $josaIndex)){
				$matchedJosaPair = $josaPair;
				break;
			}
		}

		if($matchedJosaPair !== null){
			$readText = $this->getReadText($previous);

			foreach($this->detectors as $jongSungDetector){
				if($jongSungDetector->canHandle($readText)){
					return $this->replaceStringByJongSung($str, $matchedJosaPair, $jongSungDetector->getJongSungType($readText));
				}
			}

			$replaceStr = $matchedJosaPair[0] . "(" . $matchedJosaPair[1] . ")";
			if($searchStr === null){
				throw new \AssertionError("\$searchStr shouldn't be null here (This is a BUG)");
			}
			return mb_substr($str, 0, $josaIndex) . $replaceStr . mb_substr($str, $josaIndex + mb_strlen($searchStr));
		}

		return $str;
	}

	public function isEndSkipText(string $str, int $begin, int $end) : bool{
		for($i = $begin; $i < $end; ++$i){
			if(!$this->isEndSkipChar(mb_substr($str, $i, 1))){
				return false;
			}
		}
		return true;
	}

	public function isEndSkipChar(string $char) : bool{
		if(mb_strlen($char) !== 1){
			throw new \InvalidArgumentException("char must be a single character");
		}
		$skipChars = "\"')]}>";
		return mb_strpos($skipChars, $char) !== false;
	}

	/** @phpstan-param array{0: string, 1: string} $josaPair */
	public function replaceStringByJongSung(string $str, ?array $josaPair, int $jongSungType) : string{
		if($josaPair != null){
			if($josaPair[0] == "으로"){
				$useFirst = $jongSungType == 1;
			}else{
				$useFirst = $jongSungType > 0;
			}

			$searchStr = $useFirst ? $josaPair[1] : $josaPair[0];
			$replaceStr = $useFirst ? $josaPair[0] : $josaPair[1];
			$josaIndex = mb_strpos($str, $searchStr);
			if($josaIndex !== false && $josaIndex >= 0 && $this->isEndSkipText($str, 0, $josaIndex)){
				return mb_substr($str, 0, $josaIndex) . $replaceStr . mb_substr($str, $josaIndex + mb_strlen($searchStr));
			}
		}

		return $str;
	}

	public function getReadText(string $str) : string{
		foreach($this->readingRules as $readingRule){
			$str = str_replace($readingRule[0], $readingRule[1], $str);
		}
		$skipCount = 0;
		for($i = mb_strlen($str) - 1; $i >= 0; --$i){
			$ch = mb_substr($str, $i, 1);

			if(!$this->isEndSkipChar($ch)){
				break;
			}
		}

		return mb_substr($str, 0, $i + 1);
	}
}

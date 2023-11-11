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

namespace alvin0319\JosaExtension\util;

use function mb_ord;
use function mb_strlen;
use function mb_substr;

final class CharUtil{

	private function __construct(){
		// NOOP
	}

	public static function isAlpha(string $char) : bool{
		return self::isAlphaLowercase($char) || self::isAlphaUppercase($char);
	}

	public static function isAlphaLowercase(string $char) : bool{
		self::assertChar($char);
		return $char >= 'a' && $char <= 'z';
	}

	public static function isAlphaUppercase(string $char) : bool{
		self::assertChar($char);
		return $char >= 'A' && $char <= 'Z';
	}

	public static function isNumber(string $char) : bool{
		self::assertChar($char);
		return $char >= '0' && $char <= '9';
	}

	public static function isHangulSyllables(string $char) : bool{
		self::assertChar($char);
		return mb_ord($char) >= 0xac00 && mb_ord($char) <= 0xd7af;
	}

	public static function isHiragana(string $char) : bool{
		self::assertChar($char);
		return mb_ord($char) >= 0x3040 && mb_ord($char) <= 0x309F;
	}

	public static function isKatakana(string $char) : bool{
		self::assertChar($char);
		return mb_ord($char) >= 0x30A0 && mb_ord($char) <= 0x30FF;
	}

	public static function isJapanese(string $char) : bool{
		return self::isHiragana($char) || self::isKatakana($char);
	}

	public static function lastChar(string $str) : string{
		$length = mb_strlen($str);
		if($length === 0){
			return "\0";
		}
		return mb_substr($str, $length - 1);
	}

	public static function getHangulJongSungType(string $char) : int{
		$result = 0;
		if(self::isHangulSyllables($char)){
			$code = (mb_ord($char) - 0xac00) % 28;
			if($code > 0){
				++$result;
			}
			if($code === 8){
				++$result;
			}
		}
		return $result;
	}

	private static function assertChar(string $char) : void{
		if(mb_strlen($char) !== 1){
			throw new \InvalidArgumentException("Argument must be a single character");
		}
	}
}

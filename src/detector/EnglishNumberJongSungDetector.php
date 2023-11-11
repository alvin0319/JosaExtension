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

namespace alvin0319\JosaExtension\detector;

use alvin0319\JosaExtension\util\CharUtil;
use alvin0319\JosaExtension\util\ParseResult;
use function ord;

// 영문+숫자를 미국식으로 읽기 ex) MP3, iPhone4, iOS8.3 (iOS eight point three), Office2003 (Office two thousand three)
// 일반적으로 영문+숫자라도 11 이상은 그냥 한글로 읽는 경우가 많아서 적합하지 않을 수 있음.
final class EnglishNumberJongSungDetector implements JongSungDetector{

	public function canHandle(string $str) : bool{
		$result = ParseResult::parse($str);

		return $result->isNumberFound && $result->isEnglishFound;
	}

	public function getJongSungType(string $str) : int{
		$result = ParseResult::parse($str);
		if(!$result->isFloat){
			$number = (int) $result->number;

			if($number === 0){
				return 0;
			}

			$twoDigit = $number % 100;

			if($twoDigit !== 12 && $twoDigit >= 10 && $twoDigit <= 19){
				return 1;
			}

			if($number % 100000 === 0){
				return 1;
			}
		}

		$oneDigit = ord(CharUtil::lastChar($result->numberPart)) - ord("0");

		return match ($oneDigit) {
			1, 7, 8, 9 => 1,
			default => 0,
		};

	}
}

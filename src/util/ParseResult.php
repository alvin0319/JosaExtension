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

use function mb_strlen;
use function mb_substr;

final class ParseResult{
	public bool $isNumberFound = false;

	// valid only if isNumberFound
	public bool $isEnglishFound = false;
	public float $number = -1;
	public bool $isFloat = false;
	public string $prefixPart = "";
	public string $numberPart = "";

	public static function parse(string $str) : ParseResult{
		$result = new ParseResult();

		$isSpaceFound = false;
		$numberPartBeginIndex = 0;
		$isNumberCompleted = false;

		for($i = mb_strlen($str) - 1; $i >= 0; --$i){
			$ch = $str[$i];

			if(!$isNumberCompleted && !$isSpaceFound && CharUtil::isNumber($ch)){
				$result->isNumberFound = true;
				$numberPartBeginIndex = $i;
				continue;
			}

			if($ch === ","){
				continue;
			}

			if(!$isNumberCompleted && $result->isNumberFound && !$result->isFloat && $ch === "."){
				$result->isFloat = true;
				continue;
			}

			if(!$isNumberCompleted && $result->isNumberFound && !$isSpaceFound && $ch === " "){
				$isSpaceFound = true;
				$isNumberCompleted = true;
				continue;
			}

			if(!$isNumberCompleted && $result->isNumberFound && $ch === "-"){
				$isNumberCompleted = true;
				continue;
			}

			if($result->isNumberFound && CharUtil::isAlpha($ch)){
				$result->isEnglishFound = true;
				$isNumberCompleted = true;
				break;
			}

			break;
		}

		if($result->isNumberFound){
			$result->numberPart = mb_substr($str, $numberPartBeginIndex);
			$result->prefixPart = mb_substr($str, 0, $numberPartBeginIndex);

			try{
				$result->number = (float) $result->numberPart;
			}catch(\Exception $e){
			}
		}

		return $result;
	}
}

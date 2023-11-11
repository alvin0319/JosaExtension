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
use function is_int;
use function mb_strlen;
use function mb_strtolower;
use function str_contains;
use function str_ends_with;

final class EnglishJongSungDetector implements JongSungDetector, CustomizableRule{

	/** @phpstan-var list<array{0: string, 1: int}> */
	private array $customRules = [
		["app", 1],
		["god", 1],
		["good", 1],
		["pod", 1],
		["bag", 1],
		["big", 1],
		["gig", 1],
		["chocolate", 1],
		["root", 0],
		["boot", 0],
		["check", 0]
	];

	public function canHandle(string $str) : bool{
		$lastChar = CharUtil::lastChar($str);
		if(str_contains($lastChar, "q") || str_contains($lastChar, "j")){
			return false;
		}
		return CharUtil::isAlpha($lastChar);
	}

	public function addCustomRule(string $rule, mixed $value) : void{
		if(!is_int($value) || $value < 0 || $value > 2){
			throw new \InvalidArgumentException("Value must be integer between 0 and 2");
		}
		$this->customRules[] = [$rule, $value];
	}

	public function getJongSungType(string $str) : int{
		$str = mb_strtolower($str);

		foreach($this->customRules as $rule){
			[$first, $second] = $rule;
			if(str_ends_with($str, $first)){
				return $second;
			}
		}

		$length = mb_strlen($str);
		$lastChar1 = $str[$length - 1];

		// 3자 이상인 경우만 마지막 2자만 suffix로 간주.
		$suffix = null;
		$lastChar2 = "\0";
		if($length >= 3){
			$lastChar2 = $str[$length - 2];
			$lastChar3 = $str[$length - 3];

			if(CharUtil::isAlpha($lastChar2) && CharUtil::isAlpha($lastChar3)){
				$suffix = $lastChar2 . $lastChar1;
			}
		}

		if($suffix !== null){
			// 끝나는 문자들로 종성 여부를 확인할 때 qj를 제외한 알파벳 22자를 기준으로 분류하면 아래와 같다.
			$rieuljongSungChars = "l"; // 1. 항상 받침 'ㄹ'로 읽음
			$jongSungChars = "mn"; // 2. 항상 받침으로 읽음
			$notJongSungChars = "afhiorsuvwxyz"; // 3. 항상 받침으로 읽지 않음
			$jongSungCandidateChars = "bckpt"; // 4. 대체로 받침으로 읽음
			$notJongSungCandidateChars = "deg";  // 5. 대체로 받침으로 읽지 않음

			if(str_contains($rieuljongSungChars, $lastChar1)){
				// 마지막 1문자 l은 항상 'ㄹ'으로 읽음
				return 2;
			}elseif(str_contains($jongSungChars, $lastChar1)){
				// 마지막 1문자 mn은 항상 받침으로 읽음
				return 1;
			}elseif(str_contains($notJongSungChars, $lastChar1)){
				// 마지막 1문자 afhiorsuvwxyz는 항상 받침으로 읽지 않음
				return 0;
			}

			if(str_contains($jongSungCandidateChars, $lastChar1)){
				// 예외 처리
				switch($suffix){
					case "ck":
					case "mb": // b 묵음
						return 1;
				}

				// 마지막 1문자 bckpt는 모음 뒤에서는 받침으로 읽는다.
				$vowelChars = "aeiou";
				return str_contains($vowelChars, $lastChar2) ? 1 : 0;
			}elseif(str_contains($notJongSungCandidateChars, $lastChar1)){
				// 마지막 1문자 deg는 대체로 받침으로 읽지 않지만, 아래의 경우는 받침으로 읽음.
				return match ($suffix) {
					"le" => 2,
					"me", "ne", "ng" => 1,
					default => 0,
				};
			}
		}else{
			// 1자, 2자는 약자로 간주하고 알파벳 그대로 읽음. (엘엠엔알)만 종성이 있음.
			if(str_contains("lr", $lastChar1)){
				return 2;
			}
			if(str_contains("mn", $lastChar1)){
				return 2;
			}
		}
		return 0;
	}
}

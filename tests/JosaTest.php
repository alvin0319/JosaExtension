<?php

declare(strict_types=1);

namespace alvin0319\JosaExtension;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

final class JosaTest extends TestCase{

	public function test() : void{
		self::assertEquals("안녕하세요, 갤럭시는 정말 좋습니다.", JosaFormatter::formatString("안녕하세요, %s은 정말 좋습니다.", "갤럭시"));

		$usedNickname = "%s는 사용중인 닉네임입니다.";
		assertEquals("alvin은 사용중인 닉네임입니다.", JosaFormatter::formatString($usedNickname, "alvin"));
		assertEquals("민재는 사용중인 닉네임입니다.", JosaFormatter::formatString($usedNickname, "민재"));

		assertEquals("삼을", JosaFormatter::formatString("%s을", "삼"));
		assertEquals("삼을", JosaFormatter::formatString("%s를", "삼"));
		assertEquals("사를", JosaFormatter::formatString("%s을", "사"));
		assertEquals("사를", JosaFormatter::formatString("%s를", "사"));
		assertEquals("말로", JosaFormatter::formatString("%s로", "말"));

		assertEquals("FBI는 이미 사용중입니다.", JosaFormatter::formatString("%s는 이미 사용중입니다.", "FBI"));
		assertEquals("FBI는 이미 사용중입니다.", JosaFormatter::formatString("%s은 이미 사용중입니다.", "FBI"));

		assertEquals("IBM은 이미 사용중입니다.", JosaFormatter::formatString("%s는 이미 사용중입니다.", "IBM"));
		assertEquals("IBM은 이미 사용중입니다.", JosaFormatter::formatString("%s은 이미 사용중입니다.", "IBM"));

		assertEquals("gradle은", JosaFormatter::formatString("%s는", "gradle"));
		assertEquals("glide는", JosaFormatter::formatString("%s는", "glide"));
		assertEquals("first는", JosaFormatter::formatString("%s는", "first"));
		assertEquals("unit은", JosaFormatter::formatString("%s는", "unit"));
		assertEquals("p는", JosaFormatter::formatString("%s는", "p"));
		assertEquals("r로", JosaFormatter::formatString("%s로", "r"));
		assertEquals("gear로", JosaFormatter::formatString("%s로", "gear"));
		assertEquals("app은", JosaFormatter::formatString("%s는", "app"));
		assertEquals("method는", JosaFormatter::formatString("%s는", "method"));
		assertEquals("title로", JosaFormatter::formatString("%s로", "title"));
		assertEquals("cook으로", JosaFormatter::formatString("%s로", "cook"));
		assertEquals("cool로", JosaFormatter::formatString("%s로", "cool"));
		assertEquals("quick으로", JosaFormatter::formatString("%s로", "quick"));
		assertEquals("shiny가", JosaFormatter::formatString("%s가", "shiny"));

		assertEquals("MP3는 이미 사용중입니다.", JosaFormatter::formatString("%s는 이미 사용중입니다.", "MP3"));
		assertEquals("MP3는 이미 사용중입니다.", JosaFormatter::formatString("%s은 이미 사용중입니다.", "MP3"));

		assertEquals("OS10은 이미 사용중입니다.", JosaFormatter::formatString("%s은 이미 사용중입니다.", "OS10"));
		assertEquals("Office2000은 이미 사용중입니다.", JosaFormatter::formatString("%s은 이미 사용중입니다.", "Office2000"));
		assertEquals("Office2010은 이미 사용중입니다.", JosaFormatter::formatString("%s는 이미 사용중입니다.", "Office2010"));
		assertEquals("WD-40은 이미 사용중입니다.", JosaFormatter::formatString("%s는 이미 사용중입니다.", "WD-40"));

		assertEquals("iOS8.3은 이미 사용중입니다.", JosaFormatter::formatString("%s는 이미 사용중입니다.", "iOS8.3"));
		assertEquals("GS25로 목적지를 설정하시겠습니까?", JosaFormatter::formatString("%s로 목적지를 설정하시겠습니까?", "GS25"));

		assertEquals("3과 4를 비교", JosaFormatter::formatString("%s와 %s를 비교", 3, 4));
//		assertEquals("112와 4.0을 비교", JosaFormatter::formatString("%s와 %s를 비교", 112, 4.0));
		assertEquals("8을 7로 나누면", JosaFormatter::formatString("%s을 %s로 나누면", 8, 7));
		assertEquals("6으로 나누고 7로 나누고 100으로 나누고 9로 나누고", JosaFormatter::formatString("%s로 나누고 %s로 나누고 %s로 나누고 %s로 나누고", 6, 7, 100, 9));

		assertEquals("たくあん은", JosaFormatter::formatString("%s는", "たくあん")); // 타쿠앙
		assertEquals("あゆみは", JosaFormatter::formatString("%sは", "あゆみ")); // 아유미
		assertEquals("マリゾンは", JosaFormatter::formatString("%sは", "マリゾン")); // 마리존

		assertEquals("(폰)을", JosaFormatter::formatString("%s를", "(폰)"));
		assertEquals("(폰)을", JosaFormatter::formatString("(%s)를", "폰"));

//		assertEquals("갤럭시를 아이폰으로", JosaFormatter::formatString("%1\$s을 %2\$s으로", "갤럭시", "아이폰"));
//		assertEquals("iPhone을 Galaxy로", JosaFormatter::formatString("%2\$s을 %1\$s으로", "Galaxy", "iPhone"));
		assertEquals("아이폰을 Galaxy로 변경할까요?", JosaFormatter::formatString("%s을 %s으로 변경할까요?", "아이폰", "Galaxy"));

		assertEquals("???을(를) 찾을 수 없습니다.", JosaFormatter::formatString("%s를 찾을 수 없습니다.", "???"));

		assertEquals("서울에서", JosaFormatter::formatString("%s에서", "서울"));

//		assertEquals("아이폰3를 갤럭시6으로", JosaFormatter::formatString("%2\$s을 %1\$s으로", "갤럭시6", "아이폰3"));

		$formatter = JosaFormatter::getDefaultFormatter();
		$formatter->addReadingRule("베타", "beta");

		assertEquals("베타3를", $formatter->format("%s을", "베타3"));
	}
}
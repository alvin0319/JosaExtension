# JosaExtension

받침에 따라 조사(은, 는, 이, 가, 을, 를 등)를 교정할 수 있는 sprintf와 유사한 함수를 제공합니다.

[원작자](https://github.com/b1uec0in)

[Java 버전 레포](https://github.com/b1uec0in/JosaFormatter/)

# 설치

### Requirements

* PHP: 8.2 이상
* ext-mbstring: *

다음 명령어를 사용하여 [Composer](https://getcomposer.org)를 통해 라이브러리를 설치합니다.

```bash
composer require alvin0319/josa-extension
```

# 사용법

```php
use alvin0319\JosaExtension\JosaFormatter;

echo JosaFormatter::formatString("%s을 %s로 바꿔볼게요!", "PHP", "Java") // Output: PHP를 Java로 바꿔볼게요!
```

#### 종성 감지기의 우선 순위

* 한글 (HangulJongSungDetector)
  : '홍길동'은

* 영문 대문자 약어 (EnglishCapitalJongSungDetector)
  : 'IBM'이(아이비엠이)

* 일반 영문 (EnglishJongSungDetector)
  : 'Google'을(구글을)

* 영문+숫자 (EnglishNumberJongSungDetector)
  : 'WD40'는(더블유디포티는) - 이렇게 읽는 경우는 드물어 기본으로는 등록되어 있지 않습니다. (예외 처리 참고)

* 영문+10이하 숫자 (EnglishNumberKorStyleJongSungDetector)
  : 'MP3'는(엠피쓰리는), 'WD40'은(더블유디사십은)

* 숫자 (NumberJongSungDetector)
  : '1'과 '2'는(일과 이는)

* 일본어 JapaneseJongSungDetector
  : 'あゆみ'는(아유미는)

#### 예외 처리

* 영문+숫자'는 경우 10 이하만 영어로 읽도록 되어 있습니다.
  보통 'MP3'는 '엠피쓰리'로 읽지만, 'Office 2000'은 '오피스 이천'으로 읽습니다.
  만약 '영문+숫자'를 항상 영어로 읽도록 하기 위해서는 직접 EnglishNumberJongSungDetector 를 등록해야 합니다.

```php
use alvin0319\JosaExtension\JosaFormatter;

$formatter = new JosaFormatter([
    new HangulJongSungDetector(),
	new EnglishCapitalJongSungDetector(),
	new EnglishJongSungDetector(),
	new EnglishNumberJongSungDetector(),
	new EnglishNumberKoreanStyleJongSungDetector(),
	new NumberJongSungDetector(),
	new JapaneseJongSungDetector()
]);
JosaFormatter::setDefaultFormatter($formatter); // Optional
```

#### 읽기 규칙 추가하기

* 읽기 규칙은 `JosaFormatter::addReadingRule()`을 통해 추가할 수 있습니다.
* '한글+숫자'인 경우 숫자는 한글로 읽도록 되어 있습니다.
  하지만, 영어를 한글로 쓴 경우 숫자도 영어로 읽어야 해서 오동작하는 경우가 있습니다. 현재는 읽는 규칙을 직접 추가해줘서 영어로 간주하도록 할 수 있습니다.

```php
use alvin0319\JosaExtension\JosaFormatter;

$formatter = JosaFormatter::getDefaultFormatter();
$formatter->addReadingRule('베타', 'beta');
$result = $formatter->format("%s을 구매하시겠습니까?", "베가 베타 3");
// 베가 베타 3를 구매하시겠습니까?
```

# 참고 자료

* [Java 버전](https://github.com/b1uec0in/JosaFormatter/)
* 한국어 속 영어 읽기

  http://blog.naver.com/b1uec0in/221025080633

* 한글 받침에 따라 '을/를' 구분

http://gun0912.tistory.com/65

* 한글, 영어 받침 처리 (iOS)

https://github.com/trilliwon/JNaturalKorean

* 한자를 한글로 변환

http://kangwoo.tistory.com/33

* suffix로 영어 단어 찾기

http://www.litscape.com/word_tools/ends_with.php
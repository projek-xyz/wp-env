<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use Blank_Option\Html_Element;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit tests for the blank's `includes/class-option.php`.
 */
#[RunClassInSeparateProcess]
class HtmlElementTest extends TestCase
{
    public static function htmlOpenTags(): array
    {
        return [
            'void-elm-without-atts' => [
                [
                    ['img', []],
                ],
                ['<img />'],
            ],
            'void-elm-with-atts' => [
                [
                    ['img', ['src' => 'path/to/image.jpg']],
                ],
                ['<img src="path/to/image.jpg" />'],
            ],
            'non-p-with-atts' => [
                [
                    ['h1', ['class' => 'foo']],
                ],
                ['<h1 class="foo">'],
            ],
            'non-p-with-unclosed-sibling' => [
                [
                    ['h1', ['class' => 'foo']],
                    ['h1', ['class' => 'bar']],
                ],
                ['<h1 class="foo">', '</h1> <!-- .foo -->', '<h1 class="bar">'],
            ],
        ];
    }

    public static function htmlAttributes(): array
    {
        return [
            'to-lowercase' => [
                ['FIRST' => 'one', 'SeCond' => 'two'],
                'first="one" second="two"',
            ],
            'numeric-value' => [
                ['FIRST' => 1, 'SeCond' => 2],
                'first="1" second="2"',
            ],
            'array-value' => [
                ['class' => ['one', ' two', 'three ']],
                'class="one two three"',
            ],
            'boolean-true-value' => [
                ['checked' => true],
                'checked="checked"',
            ],
            'boolean-false-value' => [
                ['checked' => false],
                '',
            ],
        ];
    }

    public static function htmlTagAttributes(): array
    {
        return [
            'handle-invalid' => [
                'invalid html line',
                ['', []],
            ],
            'tag-without-atts' => [
                '<img>',
                ['img', []],
            ],
            'tag-with-atts' => [
                '<img src="path/to/image.jpg" class="image">',
                ['img', ['src' => 'path/to/image.jpg', 'class' => 'image']],
            ],
            'tag-and-atts-contains-colon' => [
                '<x:image data:src="path/to/image.jpg" class="image">',
                ['x:image', ['data:src' => 'path/to/image.jpg', 'class' => 'image']],
            ],
            'tag-and-atts-contains-dash' => [
                '<x-image data-src="path/to/image.jpg" class="image">',
                ['x-image', ['data-src' => 'path/to/image.jpg', 'class' => 'image']],
            ],
            'tag-and-atts-contains-dot' => [
                '<x.image data.src="path/to/image.jpg" class="image">',
                ['x.image', ['data.src' => 'path/to/image.jpg', 'class' => 'image']],
            ],
            'tag-and-atts-contains-underscore' => [
                '<x_image data_src="path/to/image.jpg" class="image">',
                ['x_image', ['data_src' => 'path/to/image.jpg', 'class' => 'image']],
            ],
        ];
    }

    #[Test]
    #[Group('open_tag')]
    #[DataProvider('htmlOpenTags')]
    public function properlyFormatingForVoidElementsWithAttributes(array $structures, array $expected)
    {
        $elm = new Html_Element();

        foreach ($structures as [$tag, $atts]) {
            $elm->open_tag($tag, $atts);
        }

        $this->assertSame(implode("\n", $expected), (string) $elm);
    }

    #[Test]
    #[Group('open_tag')]
    #[Group('close_tag')]
    public function properlyAppendClosingCommentWhenIdAndClassAttsPresent()
    {
        $elm = new Html_Element();

        $elm->open_tag('h1', ['class' => 'foo', 'id' => 'bar']);
        $elm->close_tag('h1', ['class' => 'foo', 'id' => 'bar']);

        $this->assertStringContainsString(
            implode("\n", [
                '<h1 class="foo" id="bar">',
                '</h1> <!-- #bar.foo -->',
            ]),
            (string) $elm
        );
    }

    #[Test]
    #[Group('open_tag')]
    #[Group('close_tag')]
    public function properlyAppendClosingCommentWhenOnlyIdAttsPresent()
    {
        $elm = new Html_Element();

        $elm->open_tag('h1', ['id' => 'bar']);
        $elm->close_tag('h1', ['id' => 'bar']);

        $this->assertStringContainsString(
            implode("\n", [
                '<h1 id="bar">',
                '</h1> <!-- #bar -->',
            ]),
            (string) $elm
        );
    }

    #[Test]
    #[Group('open_tag')]
    #[Group('close_tag')]
    public function properlyAppendClosingCommentWhenOnlyClassAttsPresent()
    {
        $elm = new Html_Element();

        $elm->open_tag('h1', ['class' => 'foo']);
        $elm->close_tag('h1', ['class' => 'foo']);

        $this->assertStringContainsString(
            implode("\n", [
                '<h1 class="foo">',
                '</h1> <!-- .foo -->',
            ]),
            (string) $elm
        );
    }

    #[Test]
    #[Group('append_text')]
    public function normalizesMultiNewLineTextWhenItsNotChildOfParagraphTag()
    {
        $elm = new Html_Element();

        $elm->open_tag('h4', ['class' => 'foo', 'id' => 'bar']);
        $elm->append_text("line one\n\nline two\n\nline three");
        $elm->close_tag('h4');

        $this->assertStringContainsString(
            '<h4 class="foo" id="bar">line one<br />line two<br />line three</h4>',
            (string) $elm
        );
    }

    #[Test]
    #[Group('append_text')]
    public function normalizesMultiNewLineTextWhenItsChildOfParagraphTag()
    {
        $elm = new Html_Element();

        $elm->open_tag('p', ['class' => 'foo', 'id' => 'bar']);
        $elm->append_text("line one\n\nline two\n\nline three");
        $elm->close_tag('p');

        $this->assertStringContainsString(
            implode("\n", [
                '<p class="foo" id="bar">line one</p> <!-- #bar.foo -->',
                '<p class="foo" id="bar">line two</p> <!-- #bar.foo -->',
                '<p class="foo" id="bar">line three</p>',
            ]),
            (string) $elm
        );
    }

    #[Test]
    #[Group('append_text')]
    public function directlyAppendTextWhenItsChildOfPreTag()
    {
        $elm = new Html_Element();

        $elm->open_tag('pre');
        $elm->append_text('content');
        $elm->close_tag('pre');

        $this->assertStringContainsString('<pre>content</pre>', (string) $elm);
    }

    #[Test]
    #[Group('build_attributes')]
    #[DataProvider('htmlAttributes')]
    public function shouldNormalizesAttributeName(array $atts, string $expected)
    {
        $elm = new Html_Element();

        $this->assertSame($expected, $elm->build_attributes($atts));
    }

    #[Test]
    #[Group('extract_tag_attributes')]
    #[DataProvider('htmlTagAttributes')]
    public function shouldExtractTagAndAttributeFromHtmlLine(string $line, array $expected)
    {
        $elm = new Html_Element();

        $this->assertSame($expected, $elm->extract_tag_attributes($line));
    }

    #[Test]
    #[Group('call')]
    public function shouldAppendsEchoedContentUsingCall()
    {
        $elm = new Html_Element();

        $elm->call(static function () {
            echo 'the content';
        });

        $this->assertSame('the content', (string) $elm);
    }

    #[Test]
    #[Group('dump')]
    public function shouldNotDumpDebugInfoToContentOnProdEnv()
    {
        $elm = new Html_Element();

        $elm->dump(static function () {
            echo 'the content';
        });

        $this->assertSame('', (string) $elm);
    }

    #[Test]
    #[Group('dump')]
    #[RunInSeparateProcess]
    public function shouldDumpDebugInfoToContentOnDevEnv()
    {
        define('WP_DEBUG', true);
        $elm = new Html_Element();

        $elm->dump(static function () {
            echo 'the content';
        });

        $output = (string) $elm;

        $this->assertStringStartsWith("<div class=\"blank-debug\">\n<pre>", $output);
        $this->assertStringEndsWith("</pre>\n</div> <!-- .blank-debug -->", $output);
        $this->assertStringContainsString('class Closure', $output);
    }
}

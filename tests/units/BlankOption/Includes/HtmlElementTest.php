<?php

declare(strict_types=1);

namespace UnitTests\BlankOption\Includes;

use BadMethodCallException;
use Blank_Option\Html_Element;
use Brain\Monkey\Functions;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use TypeError;

/**
 * Unit tests for the blank's `includes/class-html-element.php`.
 */
class HtmlElementTest extends TestCase
{
    public static function htmlOpenTags(): array
    {
        return [
            'void-elm-without-atts' => [
                ['<img />'],
                ['img', []],
            ],
            'void-elm-with-atts' => [
                ['<img src="path/to/image.jpg" />'],
                ['img', ['src' => 'path/to/image.jpg']],
            ],
            'non-p-with-atts' => [
                ['<h1 class="foo">'],
                ['h1', ['class' => 'foo']],
            ],
            'basic-alpine-event-atts-with-at' => [
                [
                    '<input type="button" @click="foo_fn" />',
                    '<input type="button" @click.prevent="foo_fn" />',
                ],
                ['input', ['type' => 'button', '@click' => 'foo_fn']],
                ['input', ['type' => 'button', '@click.prevent' => 'foo_fn']],
            ],
            'basic-alpine-event-atts-with-colon' => [
                [
                    '<input type="button" x-on:click="foo_fn" />',
                    '<input type="button" x-on:click.prevent="foo_fn" />',
                ],
                ['input', ['type' => 'button', 'x-on:click' => 'foo_fn']],
                ['input', ['type' => 'button', 'x-on:click.prevent' => 'foo_fn']],
            ],
            'basic-web-component' => [
                ['<my-web_component class="foo">'],
                ['my-web_component', ['class' => 'foo']],
            ],
            'namespaced-web-component-with-underscores' => [
                ['<my:other_web_component class="foo">'],
                ['my:other_web_component', ['class' => 'foo']],
            ],
            'namespaced-web-component-with-dots' => [
                ['<my:other.web_component class="foo">'],
                ['my:other.web_component', ['class' => 'foo']],
            ],
            'non-p-with-unclosed-sibling' => [
                ['<h1 class="foo"></h1> <!-- .foo -->', '<h1 class="bar">'],
                ['h1', ['class' => 'foo']],
                ['h1', ['class' => 'bar']],
            ],
            'nomalizes-atts-names-and-values' => [
                [
                    '<input class="foo" id="bar" value="1" />',
                    '<input class="one two three" />',
                ],
                ['input', ['Class' => 'foo', 'ID' => 'bar', 'value' => 1]],
                ['input', ['class' => ['one', 'two', 'three']]],
            ],
            'nomalizes-atts-with-boolean-value' => [
                [
                    '<input type="checkbox" checked="checked" />',
                    '<input type="checkbox" />',
                ],
                ['input', ['type' => 'checkbox', 'checked' => true]],
                ['input', ['type' => 'checkbox', 'checked' => false]],
            ],
            'skip-invalid-atts' => [
                [
                    '<input class="invalid" />',
                ],
                ['input', ['class' => 'invalid', '$invalid' => 'value']],
            ],
        ];
    }

    public static function htmlCloseTags(): array
    {
        return [
            'only-has-class' => [
                ['span', ['class' => 'foo']],
                [
                    '<span class="foo"></span> <!-- .foo -->',
                ],
            ],
            'only-has-id' => [
                ['span', ['id' => 'foo']],
                [
                    '<span id="foo"></span> <!-- #foo -->',
                ],
            ],
            'either-id-and-class' => [
                ['span', ['id' => 'foo', 'class' => 'bar']],
                [
                    '<span id="foo" class="bar"></span> <!-- #foo.bar -->',
                ],
            ],
            'contains-other-then-id-and-class' => [
                ['span', ['id' => 'foo', 'class' => 'bar', 'data-id' => 'foo-bar']],
                [
                    '<span id="foo" class="bar" data-id="foo-bar"></span> <!-- #foo.bar -->',
                ],
            ],
        ];
    }

    public static function htmlContent(): array
    {
        return [
            'p-with-double-line-break-content' => [
                ['p', ['class' => 'foo'], "line one\n\nline two\n\nline three"],
                [
                    '<p class="foo">line one</p> <!-- .foo -->',
                    '<p class="foo">line two</p> <!-- .foo -->',
                    '<p class="foo">line three</p> <!-- .foo -->',
                ],
            ],
            'p-with-single-line-break-content' => [
                ['p', ['class' => 'foo'], "line one\nline two\nline three"],
                [
                    '<p class="foo">line one<br />line two<br />line three</p> <!-- .foo -->',
                ],
            ],
            'h4-with-double-line-break-content' => [
                ['h4', ['class' => 'foo'], "line one\n\nline two\n\nline three"],
                [
                    '<h4 class="foo">line one<br />line two<br />line three</h4> <!-- .foo -->',
                ],
            ],
            'pre-with-single-line-break-content' => [
                ['pre', ['class' => 'foo'], "line one\nline two\nline three"],
                [
                    '<pre class="foo">line one',
                    'line two',
                    'line three</pre> <!-- .foo -->'
                ],
            ],
            'pre-with-double-line-break-content' => [
                ['pre', ['class' => 'foo'], "line one\n\nline two\n\nline three"],
                [
                    '<pre class="foo">line one',
                    '',
                    'line two',
                    '',
                    'line three</pre> <!-- .foo -->'
                ],
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
            'alpine-attributes' => [
                ['class' => 'foo', '@click.prevent' => 'doSomething()'],
                'class="foo" @click.prevent="doSomething()"',
            ],
            'invalid-name' => [
                ['class' => 'valid-name', '$invalid' => 'foo'],
                'class="valid-name"',
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
    public function properlyFormatsHtmlBasedOnGivenStructures(array $expected, array ...$structures)
    {
        $elm = new Html_Element();

        foreach ($structures as [$tag, $atts]) {
            $elm->open_tag($tag, $atts);
        }

        $this->assertSame(implode("\n", $expected), (string) $elm);
    }

    #[Test]
    #[Group('open_tag')]
    public function throwsInvalidArgumentExceptionWhenOpenTagHasInvalidName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid tag name ($$invalid-one) is specified.');

        $elm = new Html_Element();
        $elm->open_tag('$$invalid-one', []);
    }

    #[Test]
    #[Group('close_tag')]
    public function ignoreClosingTagWhenTheresNoOpeningTag()
    {
        $elm = new Html_Element();

        $elm->close_tag('h1', ['id' => 'bar']);

        $this->assertSame('', (string) $elm);
    }

    #[Test]
    #[Group('close_tag')]
    #[DataProvider('htmlCloseTags')]
    public function properlyAppendClosingCommentWhenIdAndClassAttsPresent(array $structures, array $expected)
    {
        $elm = new Html_Element();

        [$tag, $atts] = $structures;

        $elm->open_tag($tag, $atts);
        $elm->close_tag($tag, $atts);

        $this->assertSame(implode("\n", $expected), (string) $elm);
    }

    #[Test]
    #[Group('append_text')]
    #[DataProvider('htmlContent')]
    public function normalizesTextContentInsideTag(array $structures, array $expected)
    {
        $elm = new Html_Element();

        [$tag, $atts, $text] = $structures;

        $elm->open_tag($tag, $atts);
        $elm->append_text($text);
        $elm->close_tag($tag, $atts);

        $this->assertSame(implode("\n", $expected), (string) $elm);
    }

    #[Test]
    #[Group('append_text')]
    #[Group('edge-cases')]
    public function appendingMultipleTextAsNewLinesWhenTheresNoParentTagsAvailable()
    {
        $elm = new Html_Element();

        $elm->append_text('first content');
        $elm->append_text('second content');

        $this->assertSame(
            implode("\n", ['first content', 'second content']),
            (string) $elm,
        );
    }

    #[Test]
    #[Group('append_text')]
    #[Group('edge-cases')]
    public function appendingTextToNearestUnclosedParentTag()
    {
        $elm = new Html_Element();

        $elm->open_tag('div', ['class' => 'parent']);

        $elm->open_tag('span', ['class' => 'child-1']);
        $elm->append_text('first span');
        $elm->close_tag('span', ['class' => 'child-1']);

        $elm->open_tag('div', ['class' => 'child-2']);

        $elm->open_tag('span', ['class' => 'child-2-1']);
        $elm->append_text('second span');
        $elm->close_tag('span', ['class' => 'child-2-1']);

        $elm->append_text('first content inside .child-2');

        $elm->close_tag('div', ['class' => 'child-2']);

        $elm->append_text('second content inside #parent');

        $elm->close_tag('div', ['class' => 'parent']);

        $this->assertSame(
            implode("\n", [
                '<div class="parent">',
                '<span class="child-1">first span</span> <!-- .child-1 -->',
                '<div class="child-2">',
                implode('', [
                    '<span class="child-2-1">second span</span> <!-- .child-2-1 -->',
                    'first content inside .child-2</div> <!-- .child-2 -->',
                    'second content inside #parent</div> <!-- .parent -->'
                ]),
            ]),
            (string) $elm,
        );
    }

    #[Test]
    #[Group('__call')]
    public function dinamicallyCreatesTags()
    {
        $elm = new Html_Element();

        $elm->div(
            ['class' => 'wrap'],
            static fn ($elm) => $elm
                ->img(['src' => 'path/to/image.png'])
                ->p(['class' => 'description'], 'Image description')
                ->span(['class' => 'no-content']),
        );

        $this->assertSame(
            implode("\n", [
                '<div class="wrap">',
                '<img src="path/to/image.png" />',
                '<p class="description">Image description</p> <!-- .description -->',
                '<span class="no-content"></span> <!-- .no-content -->',
                '</div> <!-- .wrap -->',
            ]),
            (string) $elm,
        );
    }

    #[Test]
    #[Group('__call')]
    public function shouldNotBeALegalCallsButWorks()
    {
        $elm = new Html_Element();

        $elm->{'my:web-comp'}(['class' => 'ilegal']);
        $elm->{'my:web.comp'}(['class' => 'ilegal']);

        $this->assertSame(
            implode("\n", [
                '<my:web-comp class="ilegal">',
                '</my:web-comp> <!-- .ilegal -->',
                '<my:web.comp class="ilegal">',
                '</my:web.comp> <!-- .ilegal -->',
            ]),
            (string) $elm,
        );
    }

    #[Test]
    #[Group('__call')]
    #[TestWith(['_my_tag'])]
    #[TestWith(['tag#1'])]
    #[TestWith(['123tag'])]
    public function actualIlegalCalls(string $method)
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Call to undefined method Blank_Option\\Html_Element::{$method}()");

        $elm = new Html_Element();

        $elm->{$method}(['class' => 'ilegal']);
    }

    #[Test]
    #[Group('__call')]
    public function shouldRethrowTypeErrorOnInvalidAtts()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Blank_Option\Html_Element::div(): Argument #1 ($atts) must be of type array, string given'
        );

        $elm = new Html_Element();

        $elm->div('');
    }

    #[Test]
    #[Group('__call')]
    public function shouldRethrowTypeErrorOnInvalidChild()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Blank_Option\Html_Element::div(): Argument #2 ($child) must be of type Closure|string, array given'
        );

        $elm = new Html_Element();

        $elm->div(['class' => 'test'], ['span']);
    }

    #[Test]
    #[Group('__call')]
    public function properlyReturnsAllowedHtmlTagsAndAtts()
    {
        $elm = new Html_Element();

        $elm->form(
            ['action' => '/the/url', 'class' => 'wrap', 'x-data' => '{}', '@submit.prevent' => 'some_fn'],
            static fn ($elm) => $elm
                ->div(
                    ['class' => 'first-input'],
                    static fn ($elm) => $elm
                        ->label(['for' => 'first-text'], 'First Text')
                        ->input(['id' => 'first-text'])
                )
                ->br(['x-ref' => 'devider'])
                ->button(['type' => 'submit'], 'Submit')
        );

        $this->assertSame(
            implode("\n", [
                '<form action="/the/url" class="wrap" x-data="{}" @submit.prevent="some_fn">',
                '<div class="first-input">',
                '<label for="first-text">First Text</label>',
                '<input id="first-text" />',
                '</div> <!-- .first-input -->',
                '<br x-ref="devider" />',
                '<button type="submit">Submit</button>',
                '</form> <!-- .wrap -->',
            ]),
            (string) $elm,
        );

        $allowedTags = $elm->allowed_tags();
        $tagKeys = ['form', 'div', 'label', 'input', 'br', 'button'];

        $this->assertEquals($tagKeys, array_keys($allowedTags));

        foreach ($tagKeys as $key) {
            // Every tag should always allow alpine.js attributes.
            $this->assertArrayHasKey('x-data', $allowedTags[$key]);
        }

        // that `@submit.prevent` should only present in `form` tag.
        $this->assertArrayHasKey('@submit.prevent', $allowedTags['form']);
        $this->assertArrayNotHasKey('@submit.prevent', $allowedTags['div']);
        $this->assertArrayNotHasKey('@submit.prevent', $allowedTags['label']);
        $this->assertArrayNotHasKey('@submit.prevent', $allowedTags['input']);
        $this->assertArrayNotHasKey('@submit.prevent', $allowedTags['br']);
        $this->assertArrayNotHasKey('@submit.prevent', $allowedTags['button']);
    }

    #[Test]
    #[Group('__callStatic')]
    public function echoedTheHtmlStructureWhenCalledStatically()
    {
        $this->expectOutputString('<p class="test">Content of a P</p> <!-- .test -->');

        Functions\expect('wp_kses')->once()->andReturnFirstArg();

        Html_Element::p(['class' => 'test'], 'Content of a P');
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
    #[Group('clear')]
    #[Group('positive-value')]
    public function clearShouldAppendsBrTagByDefault()
    {
        $elm = new Html_Element();

        $elm->clear();

        $this->assertSame('<br class="clear" />', (string) $elm);
    }

    #[Test]
    #[Group('clear')]
    #[Group('positive-value')]
    public function clearShouldAutoCloseNonBrMode()
    {
        $elm = new Html_Element();

        $elm->clear('span');

        $this->assertSame('<span class="clear"></span>', (string) $elm);
    }

    #[Test]
    #[Group('clear')]
    #[Group('negative-value')]
    public function clearShouldThrowExceptionForUnsupportedMode()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Blank_Option\Html_Element::clear(): Argument #1 ($mode) must be one of "br", "div", or "span", p given'
        );

        $elm = new Html_Element();

        $elm->clear('p');
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

        $elm->dump('a string');

        $output = (string) $elm;

        $this->assertStringStartsWith("<div class=\"blank-debug\">\n<pre>", $output);
        $this->assertStringEndsWith("</pre>\n</div> <!-- .blank-debug -->", $output);
        $this->assertStringContainsString('a string', $output);
    }
}

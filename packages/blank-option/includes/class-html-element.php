<?php
/**
 * Html utility class.
 *
 * @package projek-xyz/wp-blank-option
 * @copyright Copyright (c) 2026 Fery Wardiyanto <https://feryardiant.id>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 */

declare( strict_types = 1 );

namespace Blank_Option;

use Closure;
use Stringable;

defined( 'ABSPATH' ) || exit;

/**
 * Class Html.
 *
 * @credit Contact Form 7's `WPCF7_HTMLFormatter` class.
 *
 * // Grouping & Text
 * @method static self div(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self p(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self span(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self br(array $atts = [], string|array ...$args)
 * @method static self wbr(array $atts = [], string|array ...$args)
 * @method static self hr(array $atts = [], string|array ...$args)
 *
 * // Sectioning
 * @method static self article(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self section(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self nav(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self aside(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self header(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self footer(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self main(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self address(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self h1(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self h2(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self h3(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self h4(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self h5(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self h6(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self hgroup(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 *
 * // Lists
 * @method static self ul(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self ol(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self menu(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self li(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self dl(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self dt(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self dd(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 *
 * // Tables
 * @method static self table(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self caption(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self colgroup(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self col(array $atts = [], string|array ...$args)
 * @method static self thead(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self tbody(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self tfoot(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self tr(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self th(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self td(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 *
 * // Forms
 * @method static self form(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self fieldset(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self legend(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self label(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self input(array $atts = [], string|array ...$args)
 * @method static self button(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self select(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self optgroup(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self option(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self textarea(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self datalist(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self output(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self progress(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self meter(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 *
 * // Inline Formatting
 * @method static self a(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self strong(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self b(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self em(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self i(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self u(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self s(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self small(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self mark(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self sub(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self sup(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self abbr(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self dfn(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self cite(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self q(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self ruby(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self rt(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self rp(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 *
 * // Inline Tech & Data
 * @method static self data(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self time(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self code(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self kbd(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self samp(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self var(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self bdi(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self bdo(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self ins(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self del(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 *
 * // Figures & Interactive
 * @method static self figure(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self figcaption(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self details(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self summary(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self dialog(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 *
 * // Media & Embedded
 * @method static self img(array $atts = [], string|array ...$args)
 * @method static self picture(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self video(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self audio(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self source(array $atts = [], string|array ...$args)
 * @method static self track(array $atts = [], string|array ...$args)
 * @method static self iframe(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self canvas(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self map(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self area(array $atts = [], string|array ...$args)
 * @method static self object(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self param(array $atts = [], string|array ...$args)
 * @method static self embed(array $atts = [], string|array ...$args)
 *
 * // Miscellaneous
 * @method static self pre(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self blockquote(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self noscript(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self template(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self slot(array $atts = [], Closure(self)|string|self $child = null, string|array ...$args)
 * @method static self base(array $atts = [], string|array ...$args)
 */
class Html_Element implements Stringable {
	/**
	 * Regular expression pattern for valid tag names.
	 *
	 * Allows multiple hyphens, underscores, colons, and periods in tag names.
	 * e.g., `<my-web-component>`, `<my:web-component>`, `<my:web.component>`
	 */
	public const VALID_TAG_NAME = '[a-zA-Z][0-9a-zA-Z]*(?:[_:.-][0-9a-zA-Z]+)*';

	/**
	 * Regular expression pattern for valid attribute names.
	 *
	 * Allows `@` for Alpine & Vue (e.g., `@click=`, `x-on:click=`) with
	 * multiple hyphens, underscores, colons, and periods in attribute names.
	 * e.g., `x-on:click.prevent`, `@click.prevent`
	 */
	public const VALID_ATTRIBUTE_NAME = '[@:a-zA-Z_:.][@:a-zA-Z0-9_:.-]*';

	/**
	 * List of void elements.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Glossary/Void_element
	 * @var string[]
	 */
	private const VOID_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'param', 'source', 'track', 'wbr',
	); // phpcs:enable

	/**
	 * List of nestable elements.
	 *
	 * @var string[]
	 */
	private const NESTABLE_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'article', 'aside', 'blockquote', 'div', 'fieldset', 'section', 'span',
	); // phpcs:enable

	/**
	 * HTML elements that can contain flow content.
	 *
	 * @var string[]
	 */
	private const P_PARENT_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'address', 'article', 'aside', 'blockquote', 'caption', 'div', 'dd', 'dt', 'li', 'td', 'th',
		'details', 'dialog', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'header', 'main',
		'nav', 'section',
	); // phpcs:enable

	/**
	 * HTML elements that can contain phrasing content.
	 *
	 * @var string[]
	 */
	private const BR_PARENT_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'b', 'i', 'em', 's', 'u', 'strong', 'small', 'del',
		'sub', 'sup', 'dd', 'dt', 'bdi', 'bdo', 'abbr', 'code', 'time', 'mark', 'ins', 'dfn', 'kbd',
		'span', 'p', 'ruby', 'rp', 'rt', 'address', 'data', 'li', 'td', 'th', 'var', 'samp', 'cite',
		'q', 'summary', 'caption', 'figcaption', 'legend', 'dialog', 'meter', 'progress', 'output',
	); // phpcs:enable

	/**
	 * List of HTML elements to be banned (not allowed to be generated).
	 *
	 * @var string[]
	 */
	private array $banned_tags = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'html', 'head', 'title', 'link', 'meta', 'body', 'script', 'style', 'keygen',
	); // phpcs:enable

	/**
	 * Stack of open tags.
	 *
	 * @var string[]
	 */
	private array $tags_stack = array();

	/**
	 * Registered tags with its alpine atts.
	 *
	 * @var array<string, array<string, bool>>
	 */
	private array $registered_tags = array();

	/**
	 * Final output buffer.
	 *
	 * @var string[]
	 */
	private array $output = array();

	/**
	 * Whether to add a new line before the output.
	 *
	 * @var bool
	 */
	private bool $new_line = true;

	/**
	 * Returns debug information for the HTML instance.
	 *
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array {
		return array(
			'stacks' => $this->tags_stack,
			'output' => array_map( 'esc_html', $this->output ),
		);
	}

	/**
	 * Returns the current output as a string.
	 *
	 * @return string
	 */
	public function __toString(): string {
		$this->close_all_tags();

		$output       = $this->output;
		$this->output = array();

		return implode( "\n", $output );
	}

	/**
	 * Magic method __call to handle HTML element generation.
	 *
	 * @param string $method The HTML element name.
	 * @param array  $args   Arguments (attributes and child content).
	 * @throws \BadMethodCallException If the method name is not a valid HTML tag.
	 * @throws \TypeError              If arguments are invalid.
	 */
	public static function __callStatic( string $method, array $args = array() ): void {
		$elm = new self();

		$elm->{$method}( ...$args );

		echo \wp_kses( (string) $elm, $elm->allowed_tags() );
	}

	/**
	 * Magic method __call to handle HTML element generation.
	 *
	 * @param string $method The HTML element name.
	 * @param array  $args   Arguments (attributes and child content).
	 * @throws \BadMethodCallException If the method name is not a valid HTML tag.
	 * @throws \TypeError              If arguments are invalid.
	 * @throws \Throwable              Any exception thrown by `open_tag()`.
	 */
	public function __call( string $method, array $args = array() ): self {
		$atts = $args[0] ?? $args['atts'] ?? array();

		if ( ( is_array( $atts ) && empty( $atts ) ) && count( $args ) > 0 ) {
			foreach ( $args as $name => $value ) {
				if ( 'child' === $name ) {
					continue;
				}

				$name = str_replace( '_', '-', $name );

				$atts[ $name ] = $value;
			}
		}

		try {
			$this->open_tag( $method, $atts );
		} catch ( \InvalidArgumentException $err ) {
			$message = sprintf( 'Call to undefined method %s::%s()', __CLASS__, \esc_attr( $method ) );

			throw new \BadMethodCallException( $message, $err->getCode(), $err ); // phpcs:ignore
		} catch ( \Throwable $err ) {
			$err_class = $err::class;
			$message   = sprintf(
				'%s::%s(): Argument #1 ($atts) must be of type array, %s given',
				__CLASS__,
				\esc_attr( $method ),
				\esc_attr( gettype( $atts ) )
			);

			throw new $err_class( $message, $err->getCode(), $err ); // phpcs:ignore
		}

		if ( in_array( $method, self::VOID_TAGS, true ) ) {
			return $this;
		}

		$child = $args[1] ?? $args['child'] ?? null;

		if ( $child instanceof Closure ) {
			$child_callback = new \ReflectionFunction( $child );

			$child = $child_callback->invoke( $this );
		}

		if ( $child instanceof Html_Element ) {
			return $child->close_tag( $method, $atts );
		}

		if ( is_string( $child ) && '' !== $child ) {
			return $this->append_text( $child )->close_tag( $method, $atts );
		}

		if ( empty( $child ) ) {
			return $this->close_tag( $method, $atts );
		}

		throw new \TypeError(
			sprintf(
				'%s::%s(): Argument #2 ($child) must be of type Closure|string, %s given',
				__CLASS__,
				\esc_attr( $method ),
				\esc_attr( gettype( $child ) )
			)
		);
	}

	/**
	 * Appends a tag to the current output.
	 *
	 * @param string                $tag  The tag name.
	 * @param array<string, string> $atts The tag attributes.
	 */
	public function open_tag( string $tag, array $atts = array() ): static {
		$tag = $this->validate_tag( $tag );

		$this->new_line = true;

		if ( in_array( $tag, self::VOID_TAGS, true ) ) {
			$content = ! empty( $atts )
				? sprintf( '<%s %s />', $tag, $this->build_attributes( ...$atts ) )
				: sprintf( '<%s />', $tag );

			$this->allow_tag( $tag, $atts );

			return $this->append_content( $content );
		}

		if (
			$this->has_unclosed_siblings( $tag ) &&
			! in_array( $tag, self::NESTABLE_TAGS, true )
		) {
			list( $_, $prev_atts ) = $this->previous_tag( $tag );

			// Close the previous tag if it matches the current tag.
			$this->close_tag( $tag, $prev_atts );
		}

		array_unshift( $this->tags_stack, $tag );

		$content = ! empty( $atts )
			? sprintf( '<%s %s>', $tag, $this->build_attributes( ...$atts ) )
			: sprintf( '<%s>', $tag );

		$this->allow_tag( $tag, $atts );

		return $this->append_content( $content );
	}

	/**
	 * Closes a tag.
	 *
	 * @param string                $tag The tag name.
	 * @param array<string, string> $atts The tag attributes.
	 */
	public function close_tag( string $tag, array $atts = array() ): static {
		$tag = $this->validate_tag( $tag );

		$tag_pos = array_search( $tag, $this->tags_stack, true );

		if ( false === $tag_pos ) {
			return $this;
		}

		$atts_mark  = '';
		$attr_marks = array(
			'id'    => '#',
			'class' => '.',
		);

		foreach ( $attr_marks as $attr => $mark ) {
			if ( isset( $atts[ $attr ] ) && ! empty( $atts[ $attr ] ) ) {
				$atts_mark .= $mark . $atts[ $attr ];
			}
		}

		$new_line = in_array( $tag, self::BR_PARENT_TAGS, true ) ? false : $this->new_line;

		while ( $elm = array_shift( $this->tags_stack ) ) {
			$content = ! empty( $atts_mark )
				? sprintf( '</%s> <!-- %s -->', $elm, $atts_mark )
				: sprintf( '</%s>', $elm );

			$this->append_content( $content, $new_line );

			if ( $elm === $tag ) {
				break;
			}
		}

		$this->new_line = true;

		return $this;
	}

	/**
	 * Closes all open tags.
	 */
	public function close_all_tags() {
		while ( $elm = array_shift( $this->tags_stack ) ) {
			$this->close_tag( $elm );
		}
	}

	/**
	 * Appends text content to the current output.
	 *
	 * @param string $text The text content to append.
	 */
	public function append_text( string $text ): static {
		if ( $this->is_child_of( 'pre', 'template' ) ) {
			return $this->append_content( $text, false );
		}

		list( $tag, $atts ) = $this->previous_tag();
		$no_previous_tag    = empty( $tag );

		if ( ! $this->has_unclosed_siblings( 'p', ...self::P_PARENT_TAGS ) ) {
			return $this->append_content(
				$this->normalize_paragraph( $text ),
				$no_previous_tag,
			);
		}

		// Split up the contents into paragraphs, separated by double line breaks.
		$paragraphs = array_filter(
			preg_split( '/\s*\n\s*\n\s*/', $text ),
			static function ( $paragraph ) {
				return '' !== trim( $paragraph );
			}
		);

		$last_p = count( $paragraphs ) - 1;

		foreach ( array_values( $paragraphs ) as $p => $paragraph ) {
			$this->append_content(
				$this->normalize_paragraph( $paragraph ),
				$no_previous_tag,
			);

			if ( ! $no_previous_tag && $p < $last_p ) {
				$this->close_tag( $tag, $atts );
				$this->open_tag( $tag, $atts );
			}
		}

		return $this;
	}

	/**
	 * Appends content to the current output.
	 *
	 * @param string $content The content to append.
	 * @param bool   $new_line Whether to append as new `output` item, false: append as previous `output` suffix.
	 */
	public function append_content( string $content, bool $new_line = true ): static {
		if ( $new_line ) {
			$this->output[] = trim( $content );
		} else {
			$prev_key = array_key_last( $this->output );

			$this->output[ $prev_key ] .= trim( $content );
		}

		$this->new_line = $new_line;

		return $this;
	}

	/**
	 * Append a whitespace character.
	 *
	 * @codeCoverageIgnore
	 * @return self
	 */
	public function whitespace(): self {
		$this->append_content( ' ' );

		return $this;
	}

	/**
	 * Append a clearing element.
	 *
	 * @param 'br'|'div'|'span' $mode The clearing element type.
	 * @throws \InvalidArgumentException If an invalid mode is provided.
	 * @return self
	 */
	public function clear( string $mode = 'br' ): self {
		if ( ! in_array( $mode, array( 'br', 'div', 'span' ), true ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'%s::clear(): Argument #1 ($mode) must be one of "br", "div", or "span", %s given',
					__CLASS__,
					\esc_html( $mode )
				)
			);
		}

		$this->open_tag( $mode, array( 'class' => 'clear' ) );

		if ( 'br' !== $mode ) {
			$this->close_tag( $mode );
		}

		return $this;
	}

	/**
	 * Appends the output of a callback to the current output.
	 *
	 * @template T of Closure(self, ...$args):void|self
	 *
	 * @param T     $callback The callback to execute.
	 * @param mixed ...$args  The arguments to pass to the callback.
	 */
	public function call( Closure $callback, mixed ...$args ): static { // phpcs:ignore -- Squiz.Commenting.FunctionComment.IncorrectTypeHint.
		ob_start();

		$return = $callback( $this, ...$args );

		$output = ob_get_clean();

		if ( $return instanceof self ) {
			return $return;
		}

		if ( false !== $output ) {
			$this->append_content( $output );
		}

		return $this;
	}

	/**
	 * Dump parameters for debugging (no-op if WP_DEBUG is false).
	 *
	 * @param mixed ...$params The parameters to dump.
	 * @return self
	 * @internal
	 */
	public function dump( mixed ...$params ): self {
		if ( ! Plugin::is_debug() ) {
			return $this; // No-op in production.
		}

		return $this->div(
			array( 'class' => 'blank-debug' ),
			static fn ( $elm ) => $elm
			->pre(
				child: static fn ( $elm ) => $elm
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
				->call( static fn() => var_dump( ...$params ) )
			)
		);
	}

	/**
	 * Normalizes a paragraph by replacing newlines with <br> tags and collapsing multiple spaces.
	 *
	 * @param string $paragraph The paragraph to normalize.
	 */
	public function normalize_paragraph( string $paragraph ): string {
		$paragraph = preg_replace( '/\s*\n\s*/', '<br />', $paragraph );

		return preg_replace( '/\s+/', ' ', trim( $paragraph ) );
	}

	/**
	 * Returns lowercase of $tag if the specified tag name is valid.
	 *
	 * @param string $tag The tag name to validate.
	 * @throws \InvalidArgumentException If the tag name is invalid or banned.
	 */
	public function validate_tag( string $tag ): string {
		if ( in_array( $tag, $this->banned_tags, true ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Banned tag name (%s) is specified.', \esc_attr( $tag ) )
			);
		}

		if ( 1 === preg_match( '/^' . self::VALID_TAG_NAME . '$/', $tag ) ) {
			return strtolower( $tag );
		}

		throw new \InvalidArgumentException(
			sprintf( 'Invalid tag name (%s) is specified.', \esc_attr( $tag ) ),
		);
	}

	/**
	 * Extracts the tag and its attributes from a line of HTML.
	 *
	 * @param string $line The line of HTML to extract from.
	 * @return array{string, array<string, string>} The tag and its attributes.
	 */
	public function extract_tag_attributes( string $line ): array {
		preg_match( '/<(' . self::VALID_TAG_NAME . ')\s*(.*?)>/', $line, $matches );

		$tag  = $matches[1] ?? '';
		$atts = array();

		if ( ! empty( $matches[2] ) ) {
			preg_match_all(
				'/(' . self::VALID_ATTRIBUTE_NAME . ')="([^"]*)"/',
				$matches[2],
				$attr_matches
			);

			foreach ( $attr_matches[1] as $key => $attr ) {
				$atts[ $attr ] = $attr_matches[2][ $key ];
			}
		}

		return array( $tag, $atts );
	}

	/**
	 * Returns the dynamically built allow-list for wp_kses.
	 *
	 * This combines WordPress 'post' defaults, Contact Form 7 form tags,
	 * and specifically registered Alpine.js attributes for the current instance.
	 *
	 * @return array<string, array<string, bool>> The allowed HTML tags and their attributes.
	 */
	public function allowed_tags(): array {
		$allowed_tags = array();

		/**
		 * Borrowed from Contact Form 7
		 *
		 * @link https://github.com/rocklobster-in/contact-form-7/blob/v6.1/includes/formatting.php#L387-L473
		 */
		$additional_tags_for_form = array( // phpcs:disable WordPress.Arrays
			'form'     => array(
				'action'   => true, 'accept'  => true, 'accept-charset' => true, 'disabled' => true,
				'enctype'  => true, 'method'  => true, 'name'           => true, 'target'   => true,
			),
			'button'   => array( 'disabled' => true, 'name' => true, 'type' => true, 'value' => true ),
			'datalist' => array(),
			'fieldset' => array( 'disabled' => true, 'name' => true ),
			'input'    => array(
				'accept'    => true, 'alt'      => true, 'autocomplete' => true, 'capture'  => true,
				'checked'   => true, 'disabled' => true, 'list'         => true, 'max'      => true,
				'maxlength' => true, 'min'      => true, 'minlength'    => true, 'multiple' => true,
				'name'      => true, 'pattern'  => true, 'placeholder'  => true, 'readonly' => true,
				'required'  => true, 'size'     => true, 'step'         => true, 'type'     => true,
				'value'     => true,
			),
			'label'    => array( 'for' => true ),
			'legend'   => array(),
			'meter'    => array(
				'value' => true, 'min'  => true, 'max'     => true,
				'low'   => true, 'high' => true, 'optimum' => true,
			),
			'optgroup' => array( 'disabled' => true, 'label' => true ),
			'option'   => array( 'disabled' => true, 'label' => true, 'selected' => true, 'value' => true ),
			'output'   => array( 'for'      => true, 'name'  => true ),
			'progress' => array( 'max'      => true, 'value' => true ),
			'select'   => array(
				'autocomplete' => true, 'disabled'  => true, 'multiple' => true,
				'name'         => true, 'required'  => true, 'size'     => true,
			),
			'textarea' => array(
				'autocomplete' => true, 'cols'      => true, 'disabled'    => true, 'maxlength'    => true,
				'minlength'    => true, 'name'      => true, 'placeholder' => true, 'readonly'     => true,
				'required'     => true, 'rows'      => true, 'wrap'        => true,
			),
		);

		$alpine_atts = array(
			'x-data'       => true, 'x-init'       => true, 'x-show'       => true,
			'x-bind'       => true, 'x-text'       => true, 'x-html'       => true,
			'x-model'      => true, 'x-modelable'  => true, 'x-for'        => true,
			'x-transition' => true, 'x-effect'     => true, 'x-ignore'     => true,
			'x-ref'        => true, 'x-cloak'      => true, 'x-teleport'   => true,
			'x-if'         => true, 'x-id'         => true,
		); // phpcs:enable

		$base_allowances = \wp_kses_allowed_html( 'post' );

		if ( isset( $this->registered_tags['table'] ) ) {
			// In case of displaying `WP_List_Table` within our `call` method.
			// So, we just need to call `allow_tag('table')` to make sure
			// the table is rendered properly.
			$this->allow_tag( array( 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'input', 'span', 'a', 'p' ) );
		}

		if ( isset( $this->registered_tags['form'] ) ) {
			// In case of we need to render external html form file within our
			// `call` method such as using `require` or `include`, we can
			// just call `allow_tag('form')` to make sure all inputs
			// are rendered properly.
			$this->allow_tag( array_keys( $additional_tags_for_form ) );
		}

		foreach ( $this->registered_tags as $tag => $alpine_events ) {
			$allowed_tag = array();

			if ( isset( $base_allowances[ $tag ] ) ) {
				$allowed_tag = $base_allowances[ $tag ];
			} elseif ( isset( $additional_tags_for_form[ $tag ] ) ) {
				$allowed_tag = array_merge(
					$additional_tags_for_form[ $tag ],
					$base_allowances['div'],
				);
			} else {
				$allowed_tag = $base_allowances['div'];
			}

			$allowed_tags[ $tag ] = array_merge(
				$allowed_tag,
				$alpine_atts,
				$alpine_events,
			);
		}

		return $allowed_tags;
	}

	/**
	 * Registers an Alpine.js event attribute for a given tag.
	 *
	 * @param string|array $tag  The tag(s) to register.
	 * @param array        $atts The attributes to register.
	 */
	public function allow_tag( string|array $tag, array $atts = array() ): static {
		if ( is_array( $tag ) ) {
			foreach ( $tag as $elm ) {
				$this->allow_tag( $elm );
			}

			return $this;
		}

		$alpine_events = array_filter(
			$atts,
			static function ( $key ) {
				return str_starts_with( $key, 'x-on:' )
					|| str_starts_with( $key, '@' );
			},
			ARRAY_FILTER_USE_KEY
		);

		$alpine_event_atts = array();
		$alpine_events     = array_merge(
			$this->registered_tags[ $tag ] ?? array(),
			$alpine_events,
		);

		foreach ( array_keys( $alpine_events ) as $attr ) {
			$alpine_event_atts[ $attr ] = true;
		}

		$this->registered_tags[ $tag ] = $alpine_event_atts;

		return $this;
	}

	/**
	 * Builds an HTML attribute string from an array of attributes.
	 *
	 * @param array|bool|int|string ...$atts The attributes to build.
	 */
	private function build_attributes( array|bool|int|string ...$atts ): string {
		static $boolean_attributes = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
			'checked', 'disabled', 'inert', 'multiple', 'readonly', 'required', 'selected',
		); // phpcs:enable

		$results = array();

		foreach ( $atts as $name => $value ) {
			/**
			 * The $name will always be a string.
			 *
			 * @var string $name
			 */
			$name = strtolower( trim( $name ) );

			// Fix alpine.js event declaration when it was defined from a named-argument.
			if ( str_starts_with( $name, 'x-on-' ) ) {
				$name = str_replace( 'x-on-', 'x-on:', $name );
			}

			if ( ! preg_match( '/^' . self::VALID_ATTRIBUTE_NAME . '$/', $name ) ) {
				continue;
			}

			if ( is_numeric( $value ) ) {
				$value = (string) $value;
			}

			if ( is_array( $value ) ) {
				$value = array_filter(
					array_map( 'trim', $value ),
					static fn( $v ) => ! empty( $v )
				);

				$value = implode( ' ', $value );
			}

			if ( in_array( $name, $boolean_attributes, true ) ) {
				$value = ! empty( $value );
			}

			if ( true === $value ) {
				$results[] = sprintf( '%1$s="%1$s"', $name ); // boolean attribute.
			} elseif ( is_string( $value ) ) {
				$results[] = sprintf( '%1$s="%2$s"', $name, \esc_attr( $value ) );
			}
		}

		return implode( ' ', $results );
	}

	/**
	 * Returns the structure of the last appended tag.
	 *
	 * @param string|null $tag Optional. If set, searches back for the most recent
	 *                         opening of this specific tag name.
	 * @return array{string, array<string, string>} The tag name and its attributes.
	 */
	private function previous_tag( ?string $tag = null ): array {
		/** @var positive-int $out_count */ // phpcs:ignore
		$out_count = count( $this->output );

		$prefix = $tag ? "<$tag" : '<';

		for ( $o = $out_count - 1; $o >= 0; $o-- ) {
			$line = $this->output[ $o ];

			if ( ! str_starts_with( $line, $prefix ) ) {
				continue;
			}

			list( $prev_tag, $atts ) = $this->extract_tag_attributes( $line );

			if ( $tag && $prev_tag !== $tag ) {
				continue;  // @codeCoverageIgnore
			}

			return array( $prev_tag, $atts );
		}

		return array( '', array() );
	}

	/**
	 * Checks if the current position is nested inside any of the specified tags.
	 *
	 * @param string ...$tags One or more tag names to check against the stack.
	 * @return bool True if any of the tags are currently open in the stack.
	 */
	private function is_child_of( string ...$tags ): bool {
		foreach ( $this->tags_stack as $tag ) {
			if ( in_array( $tag, $tags, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if the previously registered tag is one of the specified tags
	 * and is remain unclosed.
	 *
	 * @param string ...$tags A tag name or an array of tag names.
	 */
	private function has_unclosed_siblings( string ...$tags ): bool {
		$parent = reset( $this->tags_stack );

		if ( false === $parent ) {
			return false;
		}

		return in_array( $parent, $tags, true );
	}
}

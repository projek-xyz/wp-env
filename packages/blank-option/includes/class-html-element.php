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
 * @method static self div(array $atts = [], Closure(self)|string $child = null)
 * @method static self p(array $atts = [], Closure(self)|string $child = null)
 * @method static self span(array $atts = [], Closure(self)|string $child = null)
 * @method static self br(array $atts = [])
 * @method static self wbr(array $atts = [])
 * @method static self hr(array $atts = [])
 *
 * // Sectioning
 * @method static self article(array $atts = [], Closure(self)|string $child = null)
 * @method static self section(array $atts = [], Closure(self)|string $child = null)
 * @method static self nav(array $atts = [], Closure(self)|string $child = null)
 * @method static self aside(array $atts = [], Closure(self)|string $child = null)
 * @method static self header(array $atts = [], Closure(self)|string $child = null)
 * @method static self footer(array $atts = [], Closure(self)|string $child = null)
 * @method static self main(array $atts = [], Closure(self)|string $child = null)
 * @method static self address(array $atts = [], Closure(self)|string $child = null)
 * @method static self h1(array $atts = [], Closure(self)|string $child = null)
 * @method static self h2(array $atts = [], Closure(self)|string $child = null)
 * @method static self h3(array $atts = [], Closure(self)|string $child = null)
 * @method static self h4(array $atts = [], Closure(self)|string $child = null)
 * @method static self h5(array $atts = [], Closure(self)|string $child = null)
 * @method static self h6(array $atts = [], Closure(self)|string $child = null)
 * @method static self hgroup(array $atts = [], Closure(self)|string $child = null)
 *
 * // Lists
 * @method static self ul(array $atts = [], Closure(self)|string $child = null)
 * @method static self ol(array $atts = [], Closure(self)|string $child = null)
 * @method static self menu(array $atts = [], Closure(self)|string $child = null)
 * @method static self li(array $atts = [], Closure(self)|string $child = null)
 * @method static self dl(array $atts = [], Closure(self)|string $child = null)
 * @method static self dt(array $atts = [], Closure(self)|string $child = null)
 * @method static self dd(array $atts = [], Closure(self)|string $child = null)
 *
 * // Tables
 * @method static self table(array $atts = [], Closure(self)|string $child = null)
 * @method static self caption(array $atts = [], Closure(self)|string $child = null)
 * @method static self colgroup(array $atts = [], Closure(self)|string $child = null)
 * @method static self col(array $atts = [])
 * @method static self thead(array $atts = [], Closure(self)|string $child = null)
 * @method static self tbody(array $atts = [], Closure(self)|string $child = null)
 * @method static self tfoot(array $atts = [], Closure(self)|string $child = null)
 * @method static self tr(array $atts = [], Closure(self)|string $child = null)
 * @method static self th(array $atts = [], Closure(self)|string $child = null)
 * @method static self td(array $atts = [], Closure(self)|string $child = null)
 *
 * // Forms
 * @method static self form(array $atts = [], Closure(self)|string $child = null)
 * @method static self fieldset(array $atts = [], Closure(self)|string $child = null)
 * @method static self legend(array $atts = [], Closure(self)|string $child = null)
 * @method static self label(array $atts = [], Closure(self)|string $child = null)
 * @method static self input(array $atts = [])
 * @method static self button(array $atts = [], Closure(self)|string $child = null)
 * @method static self select(array $atts = [], Closure(self)|string $child = null)
 * @method static self optgroup(array $atts = [], Closure(self)|string $child = null)
 * @method static self option(array $atts = [], Closure(self)|string $child = null)
 * @method static self textarea(array $atts = [], Closure(self)|string $child = null)
 * @method static self datalist(array $atts = [], Closure(self)|string $child = null)
 * @method static self output(array $atts = [], Closure(self)|string $child = null)
 * @method static self progress(array $atts = [], Closure(self)|string $child = null)
 * @method static self meter(array $atts = [], Closure(self)|string $child = null)
 *
 * // Inline Formatting
 * @method static self a(array $atts = [], Closure(self)|string $child = null)
 * @method static self strong(array $atts = [], Closure(self)|string $child = null)
 * @method static self b(array $atts = [], Closure(self)|string $child = null)
 * @method static self em(array $atts = [], Closure(self)|string $child = null)
 * @method static self i(array $atts = [], Closure(self)|string $child = null)
 * @method static self u(array $atts = [], Closure(self)|string $child = null)
 * @method static self s(array $atts = [], Closure(self)|string $child = null)
 * @method static self small(array $atts = [], Closure(self)|string $child = null)
 * @method static self mark(array $atts = [], Closure(self)|string $child = null)
 * @method static self sub(array $atts = [], Closure(self)|string $child = null)
 * @method static self sup(array $atts = [], Closure(self)|string $child = null)
 * @method static self abbr(array $atts = [], Closure(self)|string $child = null)
 * @method static self dfn(array $atts = [], Closure(self)|string $child = null)
 * @method static self cite(array $atts = [], Closure(self)|string $child = null)
 * @method static self q(array $atts = [], Closure(self)|string $child = null)
 * @method static self ruby(array $atts = [], Closure(self)|string $child = null)
 * @method static self rt(array $atts = [], Closure(self)|string $child = null)
 * @method static self rp(array $atts = [], Closure(self)|string $child = null)
 *
 * // Inline Tech & Data
 * @method static self data(array $atts = [], Closure(self)|string $child = null)
 * @method static self time(array $atts = [], Closure(self)|string $child = null)
 * @method static self code(array $atts = [], Closure(self)|string $child = null)
 * @method static self kbd(array $atts = [], Closure(self)|string $child = null)
 * @method static self samp(array $atts = [], Closure(self)|string $child = null)
 * @method static self var(array $atts = [], Closure(self)|string $child = null)
 * @method static self bdi(array $atts = [], Closure(self)|string $child = null)
 * @method static self bdo(array $atts = [], Closure(self)|string $child = null)
 * @method static self ins(array $atts = [], Closure(self)|string $child = null)
 * @method static self del(array $atts = [], Closure(self)|string $child = null)
 *
 * // Figures & Interactive
 * @method static self figure(array $atts = [], Closure(self)|string $child = null)
 * @method static self figcaption(array $atts = [], Closure(self)|string $child = null)
 * @method static self details(array $atts = [], Closure(self)|string $child = null)
 * @method static self summary(array $atts = [], Closure(self)|string $child = null)
 * @method static self dialog(array $atts = [], Closure(self)|string $child = null)
 *
 * // Media & Embedded
 * @method static self img(array $atts = [])
 * @method static self picture(array $atts = [], Closure(self)|string $child = null)
 * @method static self video(array $atts = [], Closure(self)|string $child = null)
 * @method static self audio(array $atts = [], Closure(self)|string $child = null)
 * @method static self source(array $atts = [])
 * @method static self track(array $atts = [])
 * @method static self iframe(array $atts = [], Closure(self)|string $child = null)
 * @method static self canvas(array $atts = [], Closure(self)|string $child = null)
 * @method static self map(array $atts = [], Closure(self)|string $child = null)
 * @method static self area(array $atts = [])
 * @method static self object(array $atts = [], Closure(self)|string $child = null)
 * @method static self param(array $atts = [])
 * @method static self embed(array $atts = [])
 *
 * // Miscellaneous
 * @method static self pre(array $atts = [], Closure(self)|string $child = null)
 * @method static self blockquote(array $atts = [], Closure(self)|string $child = null)
 * @method static self noscript(array $atts = [], Closure(self)|string $child = null)
 * @method static self template(array $atts = [], Closure(self)|string $child = null)
 * @method static self slot(array $atts = [], Closure(self)|string $child = null)
 * @method static self base(array $atts = [])
 *
 * @internal
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
	 * @var array<string>
	 */
	private const VOID_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'param', 'source', 'track', 'wbr',
	); // phpcs:enable

	/**
	 * List of nestable elements.
	 *
	 * @var array<string>
	 */
	private const NESTABLE_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'article', 'aside', 'blockquote', 'div', 'fieldset', 'section', 'span',
	); // phpcs:enable

	/**
	 * HTML elements that can contain flow content.
	 *
	 * @var array<string>
	 */
	private const P_PARENT_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'address', 'article', 'aside', 'blockquote', 'caption', 'div', 'dd', 'dt', 'li', 'td', 'th',
		'details', 'dialog', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'header', 'main',
		'nav', 'section',
	); // phpcs:enable

	/**
	 * HTML elements that can be neither the parent nor a child of a paragraph element.
	 *
	 * @var array<string>
	 */
	private const P_NONPARENT_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'colgroup', 'hgroup', 'legend', 'dl', 'ul', 'ol', 'pre',
		'table', 'tbody', 'tfoot', 'thead', 'tr', 'summary', 'menu', 'template',
	); // phpcs:enable

	/**
	 * HTML elements in the phrasing content category, plus non-phrasing
	 * content elements that can be grandchildren of a paragraph element.
	 *
	 * @var array<string>
	 */
	private const P_CHILD_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'a', 'abbr', 'b', 's', 'strong', 'u', 'br', 'em', 'i', 'ins', 'kbd', 'sub', 'sup', 'small',
		'span', 'code', 'var', 'dfn', 'mark', 'time', 'del', 'bdi', 'bdo', 'ruby', 'rp', 'rt', 'q',
		'cite', 'img', 'noscript', 'output', 'picture', 'samp', 'slot', 'wbr', 'label', 'button',
		'audio', 'video', 'object', 'canvas', 'embed', 'map', 'area', 'data', 'datalist', 'meter',
		'input', 'textarea', 'progress', 'select', 'optgroup', 'option',
	); // phpcs:enable

	/**
	 * HTML elements that can contain phrasing content.
	 *
	 * @var array<string>
	 */
	private const BR_PARENT_TAGS = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'b', 'i', 'em', 's', 'u', 'strong', 'small', 'del',
		'sub', 'sup', 'dd', 'dt', 'bdi', 'bdo', 'abbr', 'code', 'time', 'mark', 'ins', 'dfn', 'kbd',
		'span', 'p', 'ruby', 'rp', 'rt', 'address', 'data', 'li', 'td', 'th', 'var', 'samp', 'cite',
		'q', 'summary', 'caption', 'figcaption', 'legend', 'dialog', 'meter', 'progress', 'output',
	); // phpcs:enable

	/**
	 * List of HTML elements to be ignored (not allowed to be generated).
	 *
	 * @var array<string>
	 */
	private array $ignored_tags = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
		'html', 'head', 'title', 'link', 'meta', 'body', 'script', 'style', 'keygen',
	); // phpcs:enable

	/**
	 * Unique list of known HTML tags.
	 *
	 * @var array<string>
	 */
	private readonly array $known_tags;

	/**
	 * Stack of open tags.
	 *
	 * @var array<string>
	 */
	private array $tags_stack = array();

	/**
	 * Registered tags with its alpine atts.
	 *
	 * @var array<string, list<string>>
	 */
	private array $registered_tags = array();

	/**
	 * Final output buffer.
	 *
	 * @var array
	 */
	private array $output = array();

	/**
	 * Whether to add a new line before the output.
	 *
	 * @var bool
	 */
	private bool $new_line = true;

	/**
	 * Initializes the HTML instance.
	 */
	public function __construct() {
		$known_tags = array_filter(
			array_merge(
				self::VOID_TAGS,
				self::P_PARENT_TAGS,
				self::P_NONPARENT_TAGS,
				self::P_CHILD_TAGS,
				self::BR_PARENT_TAGS,
			),
			fn ( string $tag ) => ! in_array( $tag, $this->ignored_tags, true )
		);

		$this->known_tags = array_unique( $known_tags );
	}

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
	 * @param string                                  $method The HTML element name.
	 * @param array<array-key|string, string|Closure> $args   Arguments (attributes and child content).
	 * @throws \BadMethodCallException If the element is not known or allowed.
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
	 * @param string                                 $method The HTML element name.
	 * @param array{atts:array,child:string|Closure} $args   Arguments (attributes and child content).
	 * @throws \BadMethodCallException If the element is not known or allowed.
	 * @throws \TypeError              If arguments are invalid.
	 * @throws \Throwable              Any exception thrown by `open_tag()`.
	 */
	public function __call( string $method, array $args = array() ): self {
		$atts = $args[0] ?? $args['atts'] ?? array();

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

		if ( empty( $child ) ) {
			return $this->close_tag( $method, $atts );
		}

		if ( $child instanceof Closure ) {
			$child_callback = new \ReflectionFunction( $child );

			$child = $child_callback->invoke( $this );
		}

		if ( is_string( $child ) ) {
			return $this->append_text( $child )->close_tag( $method, $atts );
		}

		if ( $child instanceof Html_Element ) {
			return $child->close_tag( $method, $atts );
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
				? sprintf( '<%s %s />', $tag, $this->build_attributes( $atts ) )
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
			? sprintf( '<%s %s>', $tag, $this->build_attributes( $atts ) )
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
	 * Appends the output of a callback to the current output.
	 *
	 * @template T of Closure(self, ...$args):void
	 *
	 * @param T     $callback The callback to execute.
	 * @param mixed ...$args  The arguments to pass to the callback.
	 */
	public function call( Closure $callback, mixed ...$args ): static { // phpcs:ignore -- Squiz.Commenting.FunctionComment.IncorrectTypeHint.
		ob_start();

		$callback( $this, ...$args );

		$output = ob_get_clean();

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
	 * @throws \InvalidArgumentException If the tag name is invalid.
	 */
	public function validate_tag( string $tag ): string {
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
			preg_match_all( '/(' . self::VALID_ATTRIBUTE_NAME . ')="([^"]*)"/', $matches[2], $attr_matches );

			foreach ( $attr_matches[1] as $key => $attr ) {
				$atts[ $attr ] = $attr_matches[2][ $key ];
			}
		}

		return array( $tag, $atts );
	}

	/**
	 * Returns the allowed HTML tags and their attributes.
	 *
	 * @return array<string, array<string, bool>> The allowed HTML tags and their attributes.
	 */
	public function allowed_tags(): array {
		$allowed_tags = array();

		return $allowed_tags;
	}

	/**
	 * Builds an HTML attribute string from an array of attributes.
	 *
	 * @param array<string, mixed> $atts The attributes to build.
	 */
	private function build_attributes( array $atts ): string {
		static $boolean_attributes = array( // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
			'checked', 'disabled', 'inert', 'multiple', 'readonly', 'required', 'selected',
		); // phpcs:enable

		$results = array();

		foreach ( $atts as $name => $value ) {
			$name = strtolower( trim( $name ) );

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
	 * Registers an Alpine.js event attribute for a given tag.
	 *
	 * @param string $tag The tag to register.
	 * @param array  $atts The attributes to register.
	 */
	private function allow_tag( string $tag, array $atts = array() ): void {
		// .
	}

	/**
	 * Returns the structure of previously registered tag.
	 *
	 * @param string|null $tag The previous tag to search for.
	 * @return array{string, array<string, string>} The tag and its attributes.
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

			return array( $prev_tag, $atts );
		}

		return array( '', array() );
	}

	/**
	 * Returns true if the current node is a child of one of the specified tags.
	 *
	 * @param string ...$tags A tag name or an array of tag names.
	 */
	private function is_child_of( ...$tags ): bool {
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
	private function has_unclosed_siblings( ...$tags ): bool {
		$parent = reset( $this->tags_stack );

		if ( false === $parent ) {
			return false;
		}

		return in_array( $parent, $tags, true );
	}
}

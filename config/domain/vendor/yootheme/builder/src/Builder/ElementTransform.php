<?php

namespace YOOtheme\Builder;

use YOOtheme\Builder;
use YOOtheme\View;

class ElementTransform
{
    /**
     * @var View
     */
    protected $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Transform callback.
     *
     * @param object $node
     * @param array  $params
     */
    public function __invoke($node, array $params)
    {
        /**
         * @var $type
         */
        extract($params);

        if ($node->type === 'layout' || !($type->element || $type->container)) {
            return;
        }

        $node->attrs += [
            'id' => !empty($node->props['id']) ? $node->props['id'] : null,
            'class' => !empty($node->props['class']) ? [$node->props['class']] : [],
        ];

        $this->parallax($node);
        $this->visibility($node, $params);
        $this->position($node, $params);
        $this->margin($node);
        $this->maxWidth($node);
        $this->textAlign($node);
        $this->customCSS($node, $params);

        if ($type->element) {
            $this->animation($node, $params);
            $this->containerPadding($node, $params);
        }
    }

    /**
     * @param object $node
     * @param array  $params
     */
    public function animation($node, array $params)
    {
        /**
         * @var Builder $builder
         * @var array   $path
         */
        extract($params);

        if (!empty($node->props['image_svg_inline']) && !empty($node->props['image_svg_animate'])) {
            $node->props['image_svg_inline'] = ['stroke-animation: true; attributes: uk-scrollspy-class:uk-animation-stroke'];
        }

        if ($builder->parent($path, 'section', 'animation')) {
            $attr = 'uk-scrollspy-class';

            $value = @$node->props['item_animation'] ?: @$node->props['animation'];
            $value = in_array($value, ['none', 'parallax']) ? false : (!empty($value)
                ? ['uk-animation-{0}' => $value]
                : true);

        } elseif (!empty($node->props['image_svg_inline'])) {
            $attr = 'uk-scrollspy';
            $value = ['target: [uk-scrollspy-class];'];
        }

        if (!empty($value)) {
            foreach (isset($node->props['item_animation']) ? $node->children : [$node] as $child) {
                $child->attrs[$attr] = $value;
            }
        }

    }

    /**
     * @param object $node
     */
    public function parallax($node)
    {
        if ((!empty($node->props['animation']) && $node->props['animation'] === 'parallax')
            || (!empty($node->props['item_animation']) && $node->props['item_animation'] === 'parallax')
        ) {
            $node->attrs['class'][] = 'uk-position-z-index uk-position-relative {@parallax_zindex}';
            $node->attrs['uk-parallax'] = array_merge($this->view->parallaxOptions($node->props), [
                'easing: {parallax_easing};',
                'target: !.uk-section; {@parallax_target}',
                'media: @{parallax_breakpoint};',
                'viewport: {parallax_viewport};',
            ]);
        }
    }

    /**
     * @param object $node
     * @param array  $params
     */
    public function visibility($node, array $params)
    {
        $node->attrs['class'][] = 'uk-visible@{visibility}';

        /**
         * @var $parent
         */
        extract($params);

        if (empty($parent)) {
            return;
        }

        $visibilities = ['', 's', 'm', 'l', 'xl'];

        $parent->props['visibility'] = $visibilities[min(
            array_search(isset($node->props['visibility']) ? $node->props['visibility'] : '', $visibilities),
            array_search(isset($parent->props['visibility']) ? $parent->props['visibility'] : 'xl', $visibilities)
        )];

    }

    /**
     * @param object $node
     * @param array  $params
     */
    public function position($node, array $params)
    {
        if (!empty($node->props['position'])) {

            foreach(['left', 'right', 'top', 'bottom'] as $pos) {
                if (isset($node->props["position_{$pos}"]) && is_numeric($node->props["position_{$pos}"])) {
                    $node->props["position_{$pos}"] .= 'px';
                }
            }

            $node->attrs['class'][] = 'uk-position-{position} [uk-width-1-1 {@position: absolute}]';
            $node->attrs['style'][] = 'left: {position_left}; {@!position_right}';
            $node->attrs['style'][] = 'right: {position_right}; {@!position_left}';
            $node->attrs['style'][] = 'top: {position_top}; {@!position_bottom}';
            $node->attrs['style'][] = 'bottom: {position_bottom}; {@!position_top}';
            $node->attrs['style'][] = 'z-index: {position_z_index};';

            if ($node->props['position'] == 'absolute') {

                /**
                 * @var $parent
                 */
                extract($params);

                $parent->props['element_absolute'] = true;

            }

        }
    }

    /**
     * @param object $node
     */
    public function margin($node)
    {
        if (empty($node->props['position']) || $node->props['position'] !== 'absolute') {
            if ($node->type !== 'row') {
                $node->attrs['class'][] = 'uk-margin {@margin: default}';
                $node->attrs['class'][] = 'uk-margin-{!margin: |default}';
            }

            if (empty($node->props['margin']) || $node->props['margin'] !== 'remove-vertical') {
                $node->attrs['class'][] = 'uk-margin-remove-top {@margin_remove_top}';
                $node->attrs['class'][] = 'uk-margin-remove-bottom {@margin_remove_bottom}';
            }
        }
    }

    /**
     * @param object $node
     */
    public function maxWidth($node)
    {
        if (!empty($node->props['maxwidth'])) {

            $node->attrs['class'][] = 'uk-width-{maxwidth}[@{maxwidth_breakpoint}]';

            if (empty($node->props['position']) || $node->props['position'] !== 'absolute') {
                // Left
                $node->attrs['class'][] = 'uk-margin-auto-right{@!block_align}{@block_align_fallback}[@{block_align_breakpoint}]';
                $node->attrs['class'][] = 'uk-margin-remove-left{@!block_align}{@block_align_fallback}@{block_align_breakpoint}';
                // Right
                $node->attrs['class'][] = 'uk-margin-auto-left{@block_align: right}[@{block_align_breakpoint}]';
                $node->attrs['class'][] = 'uk-margin-remove-right{@block_align: right}{@block_align_fallback: center}@{block_align_breakpoint}';
                // Center
                $node->attrs['class'][] = 'uk-margin-auto{@block_align: center}[@{block_align_breakpoint}]';

                // Fallback
                $node->attrs['class'][] = 'uk-margin-auto-left{@block_align_fallback: right} {@block_align_breakpoint}';
                $node->attrs['class'][] = 'uk-margin-auto{@block_align_fallback: center} {@block_align_breakpoint}';
            }
        }
    }

    /**
     * @param object $node
     */
    public function textAlign($node)
    {
        if (!empty($node->props['text_align'])) {
            $node->attrs['class'][] = ($node->props['text_align'] === 'justify') ? 'uk-text-{text_align}' : 'uk-text-{text_align}[@{text_align_breakpoint} [uk-text-{text_align_fallback}]]';
        }
    }

    /**
     * @param object $node
     * @param array  $params
     */
    public function customCSS($node, array $params)
    {
        if (empty($node->props['css'])) {
            return;
        }

        if (empty($node->attrs['id'])) {
            $node->attrs['id'] = $node->id;
        }

        // Put all comma separations in one line to prevent faulty prefixing
        $css = static::prefixCSS("{$node->props['css']}\n", '#' . str_replace('#', '\#', $node->attrs['id']));

        /**
         * @var $path
         */
        extract($params);

        $layout = end($path);
        $layout->props['css'] = (!empty($layout->props['css']) ? $layout->props['css'] : '') . $css;
    }

    /**
     * @param object $node
     * @param array  $params
     */
    public function containerPadding($node, array $params)
    {
        if (empty($node->props['container_padding_remove']) || (!empty($node->props['position']) && $node->props['position'] === 'absolute')) {
            return;
        }

        /**
         * @var Builder   $builder
         * @var array     $path
         * @var \stdClass $parent
         */
        extract($params);

        // Container Padding Remove
        $row = $builder->parent($path, 'row');
        $length = count($row->children);
        $index = (array_search($parent, $row->children) + (bool) $row->props['order_last']) % $length;

        $first = $index === 0;
        $last = $index + 1 === $length;

        foreach (['row', 'section'] as $type) {

            if (!in_array($builder->parent($path, $type, 'width'), ['default', 'small', 'large', 'xlarge'])
                || !$dir = $builder->parent($path, $type, 'width_expand')
            ) {
                continue;
            }

            $node->attrs['class']['uk-container-item-padding-remove-left'] = $first && $dir === 'left';
            $node->attrs['class']['uk-container-item-padding-remove-right'] = $last && $dir === 'right';

            break;
        }
    }

    /**
     * Prefix CSS classes.
     *
     * @param string $css
     * @param string $prefix
     *
     * @return string
     */
    protected static function prefixCSS($css, $prefix = '')
    {
        $pattern = '/([@#:\.\w\[\]][\\\\@#:,>~="\'\+\-\.\(\)\w\s\[\]\*]*)({(?:[^{}]+|(?R))*})/s';

        if (preg_match_all($pattern, $css, $matches, PREG_SET_ORDER)) {

            $keys = [];

            foreach ($matches as $match) {

                list($match, $selector, $content) = $match;

                if (in_array($key = sha1($match), $keys)) {
                    continue;
                }

                if ($selector[0] != '@') {
                    $selector = preg_replace('/(^|,)\s*/', "$0{$prefix} ", $selector);
                    $selector = preg_replace('/\s\.el-(element|section|column)/', '', $selector);
                }

                $css = str_replace($match, $selector . static::prefixCSS($content, $prefix), $css); $keys[] = $key;
            }
        }

        return $css;
    }
}

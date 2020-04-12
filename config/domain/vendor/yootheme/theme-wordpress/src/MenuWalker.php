<?php

namespace YOOtheme\Theme\Wordpress;

use YOOtheme\Config;
use YOOtheme\View;

class MenuWalker extends \Walker_Nav_Menu
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \WP_Post
     */
    protected $item;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $parents = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Constructor.
     *
     * @param View   $view
     * @param Config $config
     * @param array  $arguments
     */
    public function __construct(View $view, Config $config, array $arguments = [])
    {
        $this->view = $view;
        $this->config = $config;
        $this->arguments = $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function start_lvl(&$output, $depth = 0, $args = [])
    {
        $this->item->children = [];
        $this->parents[] = $this->item;
    }

    /**
     * {@inheritdoc}
     */
    public function end_lvl(&$output, $depth = 0, $args = [])
    {
        array_splice($this->parents, -1);
    }

    /**
     * {@inheritdoc}
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? [] : (array) $item->classes;

        // normalize menu item
        $item->id = $item->ID;
        $item->level = $depth + 1;
        $item->class = implode(' ', $classes);
        $item->anchor_title = $item->attr_title;
        $item->anchor_rel = $item->xfn;
        $item->divider = $item->type === 'custom' && $item->url === '#' && preg_match('/---+/i', $item->title);
        $item->type = $item->type === 'custom' && $item->url === '#' ? 'header' : $item->type;

        // set parent
        if (count($this->parents)) {
            $this->parents[count($this->parents) - 1]->children[] = $item;
        } else {
            $this->items[] = $item;
        }

        // set current
        $item->active = isset($item->active) && $item->active
            || in_array('current-menu-item', $classes)
            || in_array('current_page_item', $classes)
            || in_array('current_page_parent', $classes)
            || $item->url == 'index.php' && (is_home() || is_front_page())
            || is_page() && in_array($item->object_id, get_post_ancestors(get_the_ID()));

        $this->item = $item;
    }

    public function end_el(&$output, $object, $depth = 0, $args = [])
    {
        if (!isset($object->children)) {
            return;
        }

        foreach ($object->children as $child) {
            if ($child->active) {
                $object->active = true;
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function walk($elements, $max_depth, ...$args)
    {
        parent::walk($elements, $max_depth, ...$args);

        // set menu config
        $this->config->set('~menu', $this->arguments);

        echo $this->view->render('~theme/templates/menu/menu', ['items' => $this->items]);
    }
}

<?php

namespace YOOtheme\Theme;

use function YOOtheme\app;
use YOOtheme\Theme\Wordpress\Breadcrumbs;
use YOOtheme\View;

class BreadcrumbsWidget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct('breadcrumbs', 'Breadcrumbs', [
            'description' => __('Display your sites breadcrumb navigation.', 'yootheme'),
        ]);
    }

    public function widget($args, $instance)
    {
        $view = app(View::class);

        $output = [$args['before_widget']];

        if ($instance['title']) {
            $output[] = $args['before_title'] . $instance['title'] . $args['after_title'];
        }

        $output[] = $view->render('~theme/templates/breadcrumbs', ['items' => Breadcrumbs::getItems()]);
        $output[] = $args['after_widget'];

        echo implode("\n", $output);
    }

    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, ['title' => '']);
        ?>
        <p>
            <label for="<?= $this->get_field_id('title') ?>"><?php _e('Title:', 'yootheme') ?></label>
            <input type="text" name="<?= $this->get_field_name('title') ?>" value="<?= esc_attr($instance['title']) ?>" class="widefat" id="<?= $this->get_field_id('title') ?>">
        </p>
        <?php
    }
}

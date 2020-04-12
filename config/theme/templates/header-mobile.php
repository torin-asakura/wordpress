<?php

// Config
$config->addAlias('~logo', '~theme.logo');
$config->addAlias('~mobile', '~theme.mobile');

// Attrs
$attrs_menu = [];
$attrs_sticky = [];
$attrs_navbar = ['uk-navbar' => true];
$attrs_navbar_container = ['class' => 'uk-navbar-container'];

// Sticky
if ($sticky = $config('~mobile.sticky')) {
    $attrs_sticky = array_filter([
        'uk-sticky' => true,
        'show-on-up' => $sticky == 2,
        'animation' => $sticky == 2 ? 'uk-animation-slide-top' : '',
        'cls-active' => 'uk-navbar-sticky',
        'sel-target' => '.uk-navbar-container',
    ]);
}

// Logo
$logo_el = __($config('~logo.text', 'yootheme'));
$logo_img = function ($image, $width, $height, array $attrs = []) use ($config) {

    $attrs['alt'] = __($config('~logo.text', 'yootheme'));
    $attrs['uk-gif'] = $this->isImage($image) === 'gif';

    if ($this->isImage($image) === 'svg') {
        $logo = $this->image($image, array_merge($attrs, compact('width', 'height')));
    } else {
        $logo = $this->image([$image, 'thumbnail' => [$width, $height], 'srcset' => true], $attrs);
    }

    return $logo;
};

// Logo Image
if ($config('~logo.image_mobile')) {
    $logo_el = $logo_img($config('~logo.image_mobile'), $config('~logo.image_mobile_width'), $config('~logo.image_mobile_height'));
} elseif ($config('~logo.image')) {
    $logo_el = $logo_img($config('~logo.image'), $config('~logo.image_width'), $config('~logo.image_height'));
}

if (!$logo_el) {
    $config->set('~mobile.logo', false);
}

if (!is_active_sidebar('mobile')) {
    $config->set('~mobile.toggle', false);
}

$config->set('~mobile.search', false); // TODO

// Mobile Position
if (is_active_sidebar('mobile')) {

    $attrs_menu['class'][] = $config('~mobile.animation') == 'offcanvas' ? 'uk-offcanvas-bar' : '';
    $attrs_menu['class'][] = $config('~mobile.animation') == 'modal' ? 'uk-modal-dialog uk-modal-body' : '';
    $attrs_menu['class'][] = $config('~mobile.animation') == 'dropdown' ? 'uk-background-default uk-padding' : '';
    $attrs_menu['class'][] = $config('~mobile.menu_center') ? 'uk-text-center' : '';
    $attrs_menu['class'][] = $config('~mobile.animation') != 'dropdown' && $config('~mobile.menu_center_vertical') ? 'uk-flex' : '';

    $config->set('~mobile.offcanvas.overlay', true);

} else {

    $config->set('~mobile.animation', false);

}

?>

<?php if ($sticky) : ?>
<div<?= $this->attrs($attrs_sticky) ?>>
<?php endif ?>

    <div<?= $this->attrs($attrs_navbar_container) ?>>
        <nav<?= $this->attrs($attrs_navbar) ?>>

            <?php if ($config('~mobile.logo') == 'left' || $config('~mobile.toggle') == 'left' || $config('~mobile.search') == 'left') : ?>
            <div class="uk-navbar-left">

                <?php if ($config('~mobile.logo') == 'left') : ?>
                <a class="uk-navbar-item uk-logo<?= $config('~mobile.logo_padding_remove') ? ' uk-padding-remove-left' : '' ?>" href="<?= $config('~theme.site_url') ?>">
                    <?= $logo_el ?>
                </a>
                <?php endif ?>

                <?php if ($config('~mobile.toggle') == 'left') : ?>
                <a class="uk-navbar-toggle" href="#tm-mobile" uk-toggle<?= ($config('~mobile.animation') == 'dropdown') ? '="animation: true"' : '' ?>>
                    <div uk-navbar-toggle-icon></div>
                    <?php if ($config('~mobile.toggle_text')) : ?>
                        <span class="uk-margin-small-left"><?= __('Menu', 'yootheme') ?></span>
                    <?php endif ?>
                </a>
                <?php endif ?>

                <?php if ($config('~mobile.search') == 'left') : ?>
                <a class="uk-navbar-item"><?= __('Search', 'yootheme') ?></a>
                <?php endif ?>

            </div>
            <?php endif ?>

            <?php if ($config('~mobile.logo') == 'center') : ?>
            <div class="uk-navbar-center">
                <a class="uk-navbar-item uk-logo" href="<?= $config('~theme.site_url') ?>">
                    <?= $logo_el ?>
                </a>
            </div>
            <?php endif ?>

            <?php if ($config('~mobile.logo') == 'right' || $config('~mobile.toggle') == 'right' || $config('~mobile.search') == 'right') : ?>
            <div class="uk-navbar-right">

                <?php if ($config('~mobile.search') == 'right') : ?>
                <a class="uk-navbar-item"><?= __('Search', 'yootheme') ?></a>
                <?php endif ?>

                <?php if ($config('~mobile.toggle') == 'right') : ?>
                <a class="uk-navbar-toggle" href="#tm-mobile" uk-toggle<?= $config('~mobile.animation') == 'dropdown' ? '="animation: true"' : '' ?>>
                    <?php if ($config('~mobile.toggle_text')) : ?>
                        <span class="uk-margin-small-right"><?= __('Menu', 'yootheme') ?></span>
                    <?php endif ?>
                    <div uk-navbar-toggle-icon></div>
                </a>
                <?php endif ?>

                <?php if ($config('~mobile.logo') == 'right') : ?>
                <a class="uk-navbar-item uk-logo<?= $config('~mobile.logo_padding_remove') ? ' uk-padding-remove-right' : '' ?>" href="<?= $config('~theme.site_url') ?>">
                    <?= $logo_el ?>
                </a>
                <?php endif ?>

            </div>
            <?php endif ?>

        </nav>
    </div>

    <?php if ($config('~mobile.animation') == 'dropdown') : ?>

        <?php if ($config('~mobile.dropdown') == 'slide') : ?>
        <div class="uk-position-relative tm-header-mobile-slide">
        <?php endif ?>

        <div id="tm-mobile" class="<?= $config('~mobile.dropdown') == 'slide' ? 'uk-position-top' : '' ?>" hidden>
            <div<?= $this->attrs($attrs_menu) ?>>

                <?php dynamic_sidebar("mobile:grid-stack") ?>

            </div>
        </div>

        <?php if ($config('~mobile.dropdown') == 'slide') : ?>
        </div>
        <?php endif ?>

    <?php endif ?>

<?php if ($sticky) : ?>
</div>
<?php endif ?>

<?php if ($config('~mobile.animation') == 'offcanvas') : ?>
<div id="tm-mobile" uk-offcanvas<?= $this->attrs($config('~mobile.offcanvas') ?: []) ?>>
    <div<?= $this->attrs($attrs_menu) ?>>

        <button class="uk-offcanvas-close" type="button" uk-close></button>

        <?php if ($config('~mobile.menu_center_vertical')) : ?>
        <div class="uk-margin-auto-vertical uk-width-1-1">
            <?php endif ?>

            <?php dynamic_sidebar("mobile:grid-stack") ?>

            <?php if ($config('~mobile.menu_center_vertical')) : ?>
        </div>
        <?php endif ?>

    </div>
</div>
<?php endif ?>

<?php if ($config('~mobile.animation') == 'modal') : ?>
<div id="tm-mobile" class="uk-modal-full" uk-modal>
    <div<?= $this->attrs($attrs_menu, ['class' => 'uk-height-viewport']) ?>>

        <button class="uk-modal-close-full" type="button" uk-close></button>

        <?php if ($config('~mobile.menu_center_vertical')) : ?>
        <div class="uk-margin-auto-vertical uk-width-1-1">
            <?php endif ?>

            <?php dynamic_sidebar("mobile:grid-stack") ?>

            <?php if ($config('~mobile.menu_center_vertical')) : ?>
        </div>
        <?php endif ?>

    </div>
</div>
<?php endif ?>

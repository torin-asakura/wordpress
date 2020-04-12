<?php

$el = $this->el('ul', [

    'class' => [
        'uk-list',
        'uk-list-{list_style}',
        'uk-list-large {@list_size}',
        'uk-margin-remove {position: absolute}',
    ],

]);

?>

<?= $el($props, $attrs) ?>
    <?php foreach ($children as $child) : ?>
    <li class="el-item"><?= $builder->render($child, ['element' => $props]) ?></li>
    <?php endforeach ?>
</ul>

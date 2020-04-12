<ul>
    <?php for ($i = 1; $i <= 5; $i++) : ?>
        <?php if ($props["link_{$i}"]) : ?>
        <li><a href="<?= $props["link_{$i}"] ?>"><?= $this->e($props["link_{$i}"], 'social') ?></a></li>
        <?php endif ?>
    <?php endfor ?>
</ul>

<?php

readonly class PageStartOptions
{
    /**
     * @param (string|Stringable)[] $styles
     */
    public function __construct(
        public string $title = '',
        public array $styles = [],
    ) {}
}

readonly class PageEndOptions
{
    /**
     * @param (string|Stringable)[] $scripts
     * @param string[]              $scriptFiles
     */
    public function __construct(
        public array $scripts = [],
        public array $scriptFiles = [],
    ) {}
}

function _style(string $content): void
{
    ?>
  <style><?= $content ?></style>
    <?php
}

function tag(string $tag, array $attributes = [], ?string $body = null): void
{
    $attrStr = implode(
        ' ',
        array_map(
            static fn ($k, $v) => sprintf('%s=\"%s\"', $k, htmlentities($v)),
            array_keys($attributes),
            $attributes,
        ),
    );
    ?>
  <<?= $tag ?> <?= $attrStr ?>>
    <?= $body ?? '' ?>
  </<?= $tag ?>>
    <?php
}

function page_start(?PageStartOptions $options = null): void
{
    ?>
  <!doctype html>
  <html lang="en">
  <head>
    <title><?= $options?->title ?? '' ?></title>
    <style>
        .enormous {
            font-size: 100px;
        }
    </style>
      <?php
      foreach ($options?->styles ?? [] as $style) {
          tag('style', body: $style);
      } ?>
  </head>
  <body>
    <?php
}

function page_end(?PageEndOptions $options = null): void
{
    ?>
  </body>
  </html>
    <?php
}

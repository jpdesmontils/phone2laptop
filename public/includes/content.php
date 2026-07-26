<?php

function selected_language(): string
{
    $requested = $_GET['lang'] ?? '';
    if (in_array($requested, ['fr', 'en'], true)) return $requested;
    $accepted = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fr');
    return strpos($accepted, 'en') === 0 ? 'en' : 'fr';
}

function page_content(string $language): array
{
    $path = dirname(__DIR__) . '/content/' . $language . '.json';
    $content = json_decode((string) file_get_contents($path), true);
    if (!is_array($content)) throw new RuntimeException('Invalid content file');
    return $content;
}

function mustache_render(string $template, array $context): string
{
    $lookup = function (array $data, string $key) {
        $value = $data;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) return '';
            $value = $value[$part];
        }
        return $value;
    };
    $render = function (string $source, array $data) use (&$render, $lookup): string {
        $source = preg_replace_callback('/{{#([\w.]+)}}([\s\S]*?){{\/\1}}/', function ($match) use (&$render, $data, $lookup) {
            $value = $lookup($data, $match[1]);
            if (!is_array($value)) return $value ? $render($match[2], $data) : '';
            $isList = array_keys($value) === range(0, count($value) - 1);
            if (!$isList) return $render($match[2], array_replace($data, $value));
            $result = '';
            foreach ($value as $item) $result .= $render($match[2], array_replace($data, is_array($item) ? $item : ['.' => $item]));
            return $result;
        }, $source);
        $source = preg_replace_callback('/{{{([\w.]+)}}}/', fn($m) => (string) $lookup($data, $m[1]), $source);
        return preg_replace_callback('/{{([\w.]+)}}/', fn($m) => htmlspecialchars((string) $lookup($data, $m[1]), ENT_QUOTES, 'UTF-8'), $source);
    };
    return $render($template, $context);
}

function render_template(string $name, array $context): void
{
    $template = file_get_contents(dirname(__DIR__) . '/templates/' . $name . '.mustache');
    if ($template === false) throw new RuntimeException('Missing template');
    echo mustache_render($template, $context);
}

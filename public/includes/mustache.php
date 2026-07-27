<?php

/**
 * Small, dependency-free Mustache renderer for scalar variables and sections.
 * Templates remain logicless; application and locale selection stay in PHP.
 */
function render_mustache(string $template, array $context): string
{
    $sectionPattern = '/{{([#^])\s*([\w.]+)\s*}}(.*?){{\/\s*\2\s*}}/s';
    while (preg_match($sectionPattern, $template)) {
        $template = preg_replace_callback($sectionPattern, function (array $match) use ($context): string {
            $value = mustache_value($context, $match[2]);
            $visible = !empty($value);
            if ($match[1] === '^') return $visible ? '' : render_mustache($match[3], $context);
            if (!$visible) return '';
            if (is_array($value) && mustache_is_list($value)) {
                return implode('', array_map(
                    function ($item) use ($match, $context) {
                        return render_mustache(
                            $match[3],
                            is_array($item) ? $item + $context : ['.' => $item] + $context
                        );
                    },
                    $value
                ));
            }
            return render_mustache($match[3], $context);
        }, $template);
    }

    $template = preg_replace_callback('/{{{\s*([\w.]+)\s*}}}/', function (array $match) use ($context) {
        return (string) mustache_value($context, $match[1]);
    }, $template);
    return preg_replace_callback('/{{\s*([\w.]+)\s*}}/', function (array $match) use ($context) {
        return htmlspecialchars(
            (string) mustache_value($context, $match[1]),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }, $template);
}

function mustache_is_list(array $value): bool
{
    if ($value === []) return true;
    return array_keys($value) === range(0, count($value) - 1);
}

function mustache_value(array $context, string $path)
{
    $value = $context;
    foreach (explode('.', $path) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) return '';
        $value = $value[$part];
    }
    return $value;
}

function render_template(string $name, array $context): string
{
    $path = dirname(__DIR__) . '/templates/' . $name . '.mustache';
    if (!is_file($path)) throw new RuntimeException("Template introuvable : $name");
    return render_mustache((string) file_get_contents($path), $context);
}

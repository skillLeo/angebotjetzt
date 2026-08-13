<?php

namespace App\Support;

/**
 * Scrubs the HTML that comes back from the legal-page editor before it is
 * stored and later rendered with v-html.
 *
 * Only administrators can reach this input, so this is defence in depth
 * rather than the primary control: it stops a stray paste from a compromised
 * source (or a hijacked admin session) from turning a legal page into a
 * script-delivery vector for every visitor.
 */
class RichTextSanitizer
{
    /** Tags the editor can produce, plus the inline markup a paste may carry. */
    private const ALLOWED_TAGS = '<h2><h3><h4><p><br><strong><b><em><i><u><s><ul><ol><li><a><blockquote><hr><code><pre><span>';

    public static function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        // Drop script/style/iframe/object bodies outright — strip_tags would
        // keep their text content, which is worse than removing it.
        $html = preg_replace('#<(script|style|iframe|object|embed|form)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button)\b[^>]*/?>#i', '', $html) ?? '';

        $html = strip_tags($html, self::ALLOWED_TAGS);

        // Event handlers (onclick=…) and javascript:/data: URLs.
        $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? '';
        $html = preg_replace('#\s(href|src)\s*=\s*("|\')?\s*(javascript|data|vbscript):[^"\'>]*("|\')?#i', '', $html) ?? '';

        return trim($html);
    }
}

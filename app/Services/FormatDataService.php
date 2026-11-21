<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Normalizer;

class FormatDataService
{
    private array $allowedStyles = [
        'text-align' => ['center', 'right'],
        'color' => [],
        'background-color' => [],
        'background' => [],
        'font-weight' => [],
        'margin' => [],
        'padding' => [],
        'border-radius' => [],
        'border-left' => [],
    ];

    public function clean(?string $html): ?string
    {
        if (! $html) return '';
        if (strip_tags($html) === $html) {
            $html = trim($html);
            $this->nfcContent($html);
            return $html;
        }
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        foreach ($dom->getElementsByTagName('*') as $i){
            /** @var DOMElement $i */
            $this->cleanStyle($i);
            $this->formatIframe($i);
            $this->cleanTable($i);
            $this->cleanAttribute($i);
        }
        $html = str_replace(['<?xml encoding="UTF-8">', '../../'], ['', '/'], html_entity_decode($dom->saveHTML()));
        $this->cleanBrTag($html);
        $this->minify($html);
        $this->cleanEncodeChar($html);
        $this->nfcContent($html);
        return $html;
    }

    private function cleanStyle($el): void
    {
        if (!$el->hasAttribute('style')) return;

        $styleStr = $el->getAttribute('style');
        $styles = array_filter(array_map('trim', explode(';', $styleStr)));
        $kept = [];

        foreach ($styles as $style) {
            if (!str_contains($style, ':')) continue;
            [$prop, $val] = array_map('trim', explode(':', $style, 2));
            $prop = strtolower($prop);
            $val = strtolower($val);

            if (isset($this->allowedStyles[$prop])) {
                if (empty($this->allowedStyles[$prop]) || in_array($val, $this->allowedStyles[$prop])){
                    $kept[] = "$prop:$val";
                }
            }
        }
        if ($kept) {
            $el->setAttribute('style', implode(';', $kept));
        } else {
            $el->removeAttribute('style');
        }
    }

    private function cleanTable(DOMElement $el): void
    {
        $tags = ['table', 'col', 'th', 'td'];
        if (! in_array(strtolower($el->nodeName), $tags)) return;
        $el->removeAttribute('border');
        $el->removeAttribute('cellspacing');
        $el->removeAttribute('cellpadding');
        $el->removeAttribute('width');
    }

    private function formatIframe(DOMElement $el): void
    {
        if (strtolower($el->nodeName) !== 'iframe') return;

        $src = $el->getAttribute('src');
        if ($src) {
            $src = preg_replace('/\x{00A0}|\s/u', '', $src);
            if (!str_starts_with($src, 'https://')) {
                $src = str_starts_with($src, 'http://')
                    ? preg_replace('/^http:\/\//', 'https://', $src)
                    : 'https://' . ltrim($src, '/');
            }
            $el->setAttribute('src', $src);
        }

        $el->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        $el->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        $el->setAttribute('allowfullscreen', '');
        $el->removeAttribute('width');
        $el->removeAttribute('height');
        $el->removeAttribute('sandbox');
        $el->removeAttribute('frameborder');

        // Đảm bảo không bị bọc bởi bất kỳ thẻ nào không hợp lệ
        $this->unwrapToBlockLevel($el);
    }

    private function cleanAttribute(DOMElement $el): void
    {
        $el->removeAttribute('dir');
        $el->removeAttribute('role');
        $el->removeAttribute('aria-level');
        $el->removeAttribute('class');
        $el->removeAttribute('id');
        foreach ($el->attributes as $attr) {
            if (str_starts_with($attr->name, 'data-')) {
                $el->removeAttribute($attr->name);
            }
        }
    }
    private function cleanBrTag(&$content): void
    {
        $content = str_replace('<br />', '<br>', $content);
        $content = str_replace('<br/>', '<br>', $content);
    }
    private function minify(&$content): void
    {
        $content = str_replace(["\r", "\n"], '', $content);
    }
    private function cleanEncodeChar(&$content): void
    {
        $content = str_replace("\xC2\xA0", ' ', $content); // UTF-8 NBSP → space
        $content = str_replace("\u{200B}", '', $content); // UTF-8 NBSP non space -> delete
    }
    private function unwrapToBlockLevel(DOMElement $el): void
    {
        $blockTags = ['body', 'div', 'section', 'article', 'main', 'aside'];

        while ($el->parentNode && $el->parentNode instanceof DOMElement) {
            $parent = $el->parentNode;
            if (in_array(strtolower($parent->nodeName), $blockTags)) break;

            $grand = $parent->parentNode;
            if ($grand) {
                $grand->insertBefore($el, $parent);
                if ($parent->childNodes->length === 0) {
                    $grand->removeChild($parent);
                }
            } else {
                break;
            }
        }
    }
    public function nfcContent(?string &$content): void
    {
        $content = Normalizer::normalize($content, Normalizer::FORM_C);
    }

    public function cleanUrl(?string $url): string
    {
        $baseUrl = config('app.url');
        if ($url) {
            if (str_starts_with($url, $baseUrl))
                $url = str_replace($baseUrl, '', $url);
            return $url;
        }
        return '';
    }
}

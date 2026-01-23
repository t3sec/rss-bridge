<?php

declare(strict_types=1);

final class JoeHirschBlogBridge extends BridgeAbstract
{
    const NAME = 'Joe Hirsch Blog';
    const URI = 'https://www.joehirsch.me/blog';
    const DESCRIPTION = 'Latest blog posts from Joe Hirsch';
    const MAINTAINER = 't3sec';
    const CACHE_TIMEOUT = 3600;

    public function collectData()
    {
        $html = getSimpleHTMLDOM($this->getURI());

        foreach ($html->find('div.collection-item-2') as $element) {
            $item = [];

            $titleElement = $element->find('h3.h4-black', 0);
            $linkElement = $element->find('a.btn-w', 0);
            $imageElement = $element->find('img.image-3', 0);

            if ($titleElement) {
                $item['title'] = trim($titleElement->plaintext);
            }

            if ($linkElement) {
                $item['uri'] = urljoin(self::URI, $linkElement->href);
            }

            if ($imageElement) {
                $item['enclosures'] = [$imageElement->src];
            }

            // Content from full article
            if (isset($item['uri'])) {
                $item['content'] = $this->fetchFullContent($item['uri']);
            }

            if (empty($item['content'])) {
                $summaryElement = $element->find('p.card-p', 0);
                if ($summaryElement && !empty(trim($summaryElement->plaintext))) {
                    $item['content'] = trim($summaryElement->plaintext);
                } else {
                    $item['content'] = $item['title'] ?? '';
                }
            }

            $this->items[] = $item;
        }
    }

    private function fetchFullContent(string $url): string
    {
        $html = getSimpleHTMLDOMCached($url);

        if (!$html) {
            return '';
        }

        $content = $html->find('div.blog-content', 0);
        if (!$content) {
            // Try another common selector if blog-content is not found directly
            $content = $html->find('div.w-richtext', 0);
        }

        if (!$content) {
            return '';
        }

        return defaultLinkTo($content->innertext, self::URI);
    }
}

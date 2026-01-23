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

            // The website doesn't seem to have the full content or a summary in the card
            // We could fetch the full article if needed, but let's start simple.
            // The card has a hidden card-p which is empty in the HTML I saw.
            $summaryElement = $element->find('p.card-p', 0);
            if ($summaryElement && !empty(trim($summaryElement->plaintext))) {
                $item['content'] = trim($summaryElement->plaintext);
            } else {
                $item['content'] = $item['title'] ?? '';
            }

            $this->items[] = $item;
        }
    }
}

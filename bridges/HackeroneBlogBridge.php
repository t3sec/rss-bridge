<?php

declare(strict_types=1);

class HackeroneBlogBridge extends BridgeAbstract
{
    const NAME = 'Hackerone Blog';
    const URI = 'https://www.hackerone.com/blog';
    const DESCRIPTION = 'Postings of Hackerone blog';
    const MAINTAINER = 't3sec';
    const CACHE_TIMEOUT = 3600; // 1 hour


    public function collectData()
    {
        $html = getSimpleHTMLDOM(self::URI);

        // Articles are contained within elements with the class "views-row"
        foreach ($html->find('.views-row') as $element) {
            $item = [];

            // Title and URI
            // The title is inside a span with class "field--name-title" inside an <a> tag
            $titleElement = $element->find('.field--name-title', 0);
            $linkElement = $element->find('a[rel="bookmark"]', 0);

            if ($titleElement && $linkElement) {
                $item['title'] = trim($titleElement->plaintext);
                $item['uri'] = urljoin(self::URI, $linkElement->href);
            } else {
                // Skip entries that don't look like blog posts (e.g. category filters)
                continue;
            }

            // Content / Summary
            // The summary is typically in the "body" field
            $contentElement = $element->find('.field--name-body', 0);
            if ($contentElement) {
                $item['content'] = trim($contentElement->plaintext);
            }

            // Timestamp
            // Found in the <time> element with a datetime attribute
            $timeElement = $element->find('time', 0);
            if ($timeElement && $timeElement->datetime) {
                $item['timestamp'] = $timeElement->datetime;
            }

            // Enclosure (Image)
            $imageElement = $element->find('img', 0);
            if ($imageElement && $imageElement->src) {
                $item['enclosures'] = [urljoin(self::URI, $imageElement->src)];
            }

            // Categories
            $item['categories'] = [];
            foreach ($element->find('.field--name-field-blog-topic a') as $topic) {
                $item['categories'][] = trim($topic->plaintext);
            }

            $this->items[] = $item;
        }
    }
}

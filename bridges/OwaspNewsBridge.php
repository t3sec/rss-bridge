<?php

declare(strict_types=1);

class OwaspNewsBridge extends BridgeAbstract
{
    const NAME = 'OWASP News';
    const URI = 'https://owasp.org';
    const DESCRIPTION = 'News feed of Open Web Application Security Project (OWASP)';
    const MAINTAINER = 't3sec';
    const CACHE_TIMEOUT = 0; // minutes
    const PARAMETERS = [
        [
            'limit' => self::LIMIT,
        ]
    ];

    public function getURI()
    {
        return sprintf('%s/news/', parent::getURI());
    }


    public function collectData()
    {
        $limit = $this->getInput('limit');
        $dom = getSimpleHTMLDOMCached($this->getURI(), self::CACHE_TIMEOUT);
        $section = $dom->find('section.homepage-blog', 0);

        if (!$section) {
            return;
        }

        foreach ($section->find('hr') as $hr) {
            $item = [];

            $h2 = $hr->find('h2', 0);

            $a = $h2->find('a', 0);
            if (!$a) {
                continue;
            }

            $item['title'] = $a->plaintext;
            $item['uri'] = urljoin(self::URI, $a->href);

            // The author is usually in the next <p class="author">
            $authorPart = $hr->find('p.author', 0);
            if ($authorPart) {
                $item['author'] = trim($authorPart->plaintext);
            }

            // The content and date are in the next <p> after author (or after h2 if no author)
            $contentPart = ($authorPart ? $authorPart->next_sibling() : $h2->next_sibling());

            if ($contentPart && $contentPart->tag === 'p') {
                // Attempt to extract timestamp from the beginning of the content
                // Format: "Tuesday, January 13, 2026"
                $inner = $contentPart->innertext;
                $markupPos = strpos($inner, '<');
                if ($markupPos !== false) {
                    $dateStr = trim(strip_tags(substr($inner, 0, $markupPos)));
                } else {
                    // Fallback to first line or similar
                    $dateStr = trim($contentPart->plaintext);
                    // Usually the date is followed by a comma or is just the first sentence-like part
                }

                if ($dateStr) {
                    $timestamp = strtotime($dateStr);
                    if ($timestamp !== false) {
                        $item['timestamp'] = $timestamp;
                    }
                }
            }

            $item['content'] = '';
            while ($contentPart && $contentPart->tag !== 'h2' && $contentPart->tag !== 'hr') {
                $contentPart = $contentPart->next_sibling();
                if ($contentPart && $contentPart->tag === 'p') {
                    $item['content'] .= $contentPart->plaintext;
                }
            }

            // Look for an image nearby for enclosure
            $img = $hr->find('img', 0);
            if ($img && isset($img->src)) {
                $item['enclosures'] = [urljoin(self::URI, $img->src)];
            }

            $this->items[] = $item;
            if (!is_null($limit) && $limit > 0 && count($this->items) >= $limit) {
                break;
            }
        }
    }
}
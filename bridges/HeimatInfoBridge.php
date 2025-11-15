<?php

declare(strict_types=1);

class HeimatInfoBridge extends BridgeAbstract
{
    const TABS      = [
        'Rathaus-Infos' => 'City_Hall',
        'Alle'   => 'All',
    ];
    const NAME = 'Heimat Info';
    const URI = 'https://www.heimat-info.de';
    const DESCRIPTION = 'News feed of Heimat Info Portal';
    const MAINTAINER = 't3sec';
    const CACHE_TIMEOUT = 0; // seconds
    const PARAMETERS    = [
        [
            'commune' => [
                'name' => 'Kommune',
                'required' => true,
                'exampleValue' => 'Tussenhausen',
            ],
            'tab' => [
                'name' => 'Heimat Info tab',
                'type' => 'list',
                'values' => self::TABS,
                'defaultValue' => 'All',
                'title' => 'Choose one of the tabs',
            ],
        ],
    ];


    public function getURI(): string
    {
        if (!is_null($this->getInput('tab')) && !is_null($this->getInput('commune'))) {
            return sprintf('%s/gemeinden/%s?tab=%s&page=1', static::URI, rawurlencode(strtolower($this->getInput('commune'))), $this->getInput('tab'));
        }

        return parent::getURI();
    }

    public function getDescription(): string
    {
        if (!is_null($this->getInput('tab'))) {
            return sprintf('%s - %s', static::DESCRIPTION, array_search($this->getInput('tab'), self::TABS));
        }

        return parent::getDescription();
    }

    public function getName(): string
    {
        if (!is_null($this->getInput('tab'))) {
            return sprintf('%s %s - %s', static::NAME, $this->getInput('commune'), array_search($this->getInput('tab'), self::TABS));
        }

        return parent::getName();
    }

    public function collectData()
    {
        $dom = getSimpleHTMLDOMCached($this->getURI(), self::CACHE_TIMEOUT);
        $newsGrid = $dom->find('.grid-cols-2', 1);

        foreach ($newsGrid->find('.rounded-large') as $li) {
            $author = $li->find('.break-words', 0);
            $title = $li->find('.font-header', 0);
            $time = $li->find('.items-end span', 0);
            $category = $li->find('.items-center .text-xs', 0);
            $content = $li->find('.break-words', 2);
            $uri = $li->find('.items-end a', 0);
            $this->items[] = [
                'title' => sprintf('%s: %s', $author->plaintext, $title->plaintext),
                'content' => $content->plaintext,
                'timestamp' => $time->plaintext,
                'categories' => [$category->plaintext],
                'uri' => self::URI . $uri->href,
            ];
        }
    }
}
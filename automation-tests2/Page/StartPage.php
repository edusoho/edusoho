<?php

namespace PhpWebDriverExamples\Github\Page;

class StartPage extends Page
{
    /**
     * Open the Github page
     */
    public function open()
    {
        $this->driver->get('https://github.com');
    }

    /**
     * @return SearchInputPage
     */
    public function getSearchInputPage()
    {
        return new SearchInputPage($this->driver);
    }

    /**
     * Click on the "Explore" link
     *
     * @return ExplorePage
     */
    public function clickExplore()
    {
        /** @var \WebDriverLocatable|\WebDriverElement $element */
        $element = $this->driver->findElement(\WebDriverBy::xpath('//a[@href="/explore"]'));
        $this->driver->getMouse()->click($element->getCoordinates());
        return new ExplorePage($this->driver);
    }
}

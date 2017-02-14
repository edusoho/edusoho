<?php

namespace PhpWebDriverExamples\Github\Page;

class SearchInputPage extends Page
{
    /**
     * Search for a Repository by typing into the search input
     *
     * @param string $searchQuery
     * @param string $repositoryName
     *
     * @return RepositoryPage
     */
    public function searchFor($searchQuery, $repositoryName)
    {
        $element = $this->driver->findElement(\WebDriverBy::name('q'));
        $this->driver->getMouse()->click($element->getCoordinates());
        $this->driver->getKeyboard()->sendKeys($searchQuery);
        $this->driver->getKeyboard()->sendKeys(\WebDriverKeys::ENTER);

        $element = $this->driver->findElement(\WebDriverBy::xpath('//a[@href="/'.$repositoryName.'"]'));
        $this->driver->getMouse()->click($element->getCoordinates());

        $repositoryPage = new RepositoryPage($this->driver);
        $repositoryPage->waitFor($repositoryName);

        return $repositoryPage;
    }
}

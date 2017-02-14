<?php

namespace PhpWebDriverExamples\Github\Page;

class RepositoryPage extends Page
{
    /**
     * @param string $repository
     */
    public function waitFor($repository)
    {
        $wait = new \WebDriverWait($this->driver, 5, 100);
        $wait->until(
            function () use ($repository) {
                return $this->driver->getTitle() == $repository . ' · GitHub';
            }
        );
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        $element = $this->driver->findElement(\WebDriverBy::xpath('//a[@rel="author"]/span'));
        return $element->getText();
    }
}
 
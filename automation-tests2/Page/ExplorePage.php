<?php

namespace PhpWebDriverExamples\Github\Page;

class ExplorePage extends Page
{
    /**
     * @return bool
     */
    public function isShowCasesLinkVisible()
    {
        try {
            $element = $this->driver->findElement(\WebDriverBy::xpath('//a[@href="/showcases"]'));
        } catch (\Exception $exception) {
            return false;
        }
        return $element->isDisplayed();
    }
}
 
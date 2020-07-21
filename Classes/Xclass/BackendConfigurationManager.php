<?php
namespace RKW\RkwMailer\Xclass;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * A general purpose configuration manager used in backend mode.
 */
class BackendConfigurationManager extends \TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager
{

    /**
     * Returns the page uid of the current page.
     * If no page is selected, we'll return the uid of the first root page.
     *
     * @return int current page id. If no page is selected current root page id is returned
     */
    protected function getCurrentPageId()
    {

        // make currentPageId flexible. This needed for simulated frontend context in backend mode
        if ($this->getCurrentPageIdFromGetPostData()) {
            $this->currentPageId = $this->getCurrentPageIdFromGetPostData();
        }

        return parent::getCurrentPageId();
    }

}

<?php

namespace RKW\RkwMailer\ViewHelpers\Widget;
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
 * Class UriViewHelper
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @toDo: write tests
 */
class UriViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Widget\UriViewHelper
{

    /**
     * initializeArguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
    }

    /**
     * Render the Uri.
     *
     * @return string The rendered link
     * @api
     */
    public function render()
    {
        $action = $this->arguments['action'];
        $arguments = $this->arguments['arguments'];
        $section = $this->arguments['section'];
        $format = $this->arguments['format'];
        $ajax = $this->arguments['ajax'];

        if ($ajax === true) {
            return $this->getAjaxUri();
        } else {
            return $this->getWidgetUri(
                ['timeFrame', 'mailType'],
                'tx_rkwmailer_tools_rkwmailermailadministration'
            );
        }
    }

    /**
     * Get the URI for a non-AJAX Request.
     *
     * Thanks to https://www.npostnik.de/typo3/pagination-widget-im-backend-anpassen/
     *
     * @param array  $argumentKeys
     * @param string $moduleKey
     *
     * @return string the Widget URI
     */
    protected function getWidgetUri($argumentKeys = [], $moduleKey = '')
    {
        $uriBuilder = $this->controllerContext->getUriBuilder();
        $argumentPrefix = $this->controllerContext->getRequest()->getArgumentPrefix();
        $arguments = $this->hasArgument('arguments') ? $this->arguments['arguments'] : [];
        if ($this->hasArgument('action')) {
            $arguments['action'] = $this->arguments['action'];
        }
        if ($this->hasArgument('format') && $this->arguments['format'] !== '') {
            $arguments['format'] = $this->arguments['format'];
        }
        $uriArguments = [$argumentPrefix => $arguments];
        if (isset($moduleKey)) {
            \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($uriArguments, $this->getFilterArguments($argumentKeys, $moduleKey));
        }

        return $uriBuilder->reset()
            ->setArguments($uriArguments)
            ->setSection($this->arguments['section'])
            ->setAddQueryString(true)
            ->setAddQueryStringMethod($this->arguments['addQueryStringMethod'])
            ->setArgumentsToBeExcludedFromQueryString([$argumentPrefix, 'cHash'])
            ->setFormat($this->arguments['format'])
            ->build();
    }

    /**
     * @param array  $argumentKeys
     * @param string $moduleKey
     *
     * @return array
     */
    protected function getFilterArguments($argumentKeys = [], $moduleKey = '')
    {
        $moduleArguments = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($moduleKey);

        if(!is_array($moduleArguments) && empty($moduleArguments)) {
            return [];
        }

        $arguments = [];
        foreach($argumentKeys as $key) {
            if ($key === 'mailType') {  //  @todo: $mailType = 0 corresponds to message, so it should not be tested on empty
                if(array_key_exists($key, $moduleArguments)) {
                    $arguments[$key] = $moduleArguments[$key];
                }
            } else {
                if(array_key_exists($key, $moduleArguments) && !empty($moduleArguments[$key])) {
                    $arguments[$key] = $moduleArguments[$key];
                }
            }
        }

        return [$moduleKey => $arguments];
    }
}
<?php

namespace RKW\RkwMailer\ViewHelpers\Frontend;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class TypolinkViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\TypolinkViewHelper
{

    /**
     * Render
     *
     * @param string $parameter stdWrap.typolink style parameter string
     * @param string $additionalParams
     * @param int $pageUid pageUid for FE-configuration (optional)
     * @return string
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/Functions/Typolink/Index.html#resource-references
     */
    public function render($parameter, $additionalParams = '', $pageUid = null)
    {
        if (!$pageUid) {
            $pageUid = 1;
        }

        // check for pid in parameters for getting correct domain
        if (preg_match('/^((t3:\/\/page\?uid=)?([0-9]+))/', $parameter, $matches)) {
            $pageUid = $matches[3];
        }

        $this->initTSFE($pageUid);

        return static::renderStatic(
            [
                'parameter'        => $parameter,
                'additionalParams' => $additionalParams,
            ],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * Except for "forceAbsoluteUrl" this is an exact copy of the parent-class
     *
     * @param array $arguments
     * @param callable $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $parameter = $arguments['parameter'];
        $additionalParams = $arguments['additionalParams'];

        $content = '';
        if ($parameter) {
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $content = $contentObject->typoLink_URL(
                [
                    'parameter'        => self::createTypolinkParameterArrayFromArguments($parameter, $additionalParams),
                    'forceAbsoluteUrl' => 1,
                    'target'           => '_blank',
                    'extTarget'        => '_blank',
                ]
            );
        }

        return $content;
        //===
    }


    /**
     * init frontend to render frontend links in task
     *
     * @param integer $id
     * @param integer $typeNum
     * @return void
     */
    protected function initTSFE($id = 1, $typeNum = 0)
    {

        // only if in BE-Mode!!! Otherwise FE will be crashed
        if (TYPO3_MODE == 'BE') {
            if (!is_object($GLOBALS['TT'])) {
                $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
                $GLOBALS['TT']->start();
            }

            if (
                (!$GLOBALS['TSFE'] instanceof \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController)
                || ($GLOBALS['TSFE']->id != $id)
                || ($GLOBALS['TSFE']->type != $typeNum)
            ) {

                $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], $id, $typeNum);
                $GLOBALS['TSFE']->connectToDB();
                $GLOBALS['TSFE']->initFEuser();
                $GLOBALS['TSFE']->determineId();
                $GLOBALS['TSFE']->initTemplate();
                $GLOBALS['TSFE']->getConfigArray();
                $GLOBALS['LANG']->csConvObj = $GLOBALS['TSFE']->csConvObj;

                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
                    $rootline = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($id);
                    $host = \TYPO3\CMS\Backend\Utility\BackendUtility::firstDomainRecord($rootline);
                    $_SERVER['HTTP_HOST'] = $host;
                    $GLOBALS['TSFE']->config['config']['absRefPrefix'] = $host;
                }
            }
        }
    }
}

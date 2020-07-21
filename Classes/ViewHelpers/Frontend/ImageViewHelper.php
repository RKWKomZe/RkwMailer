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

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$currentVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
if ($currentVersion < 8000000)  {

    /**
     * Class ImageViewHelper
     *
     * @author Steffen Kroggel <developer@steffenkroggel.de>
     * @copyright Rkw Kompetenzzentrum
     * @package RKW_RkwMailer
     * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
     * @deprecated
     */
    class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
    {


        /**
         * Resizes a given image (if required) and renders the respective img tag
         *
         * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
         * @param string $src a path to a file, a combined FAL identifier or an uid (int). If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record. If you already got a FAL object, consider using the $image parameter instead
         * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
         * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
         * @param int $minWidth minimum width of the image
         * @param int $minHeight minimum height of the image
         * @param int $maxWidth maximum width of the image
         * @param int $maxHeight maximum height of the image
         * @param bool $treatIdAsReference given src argument is a sys_file_reference record
         * @param FileInterface|AbstractFileFolder $image a FAL object
         * @param string|bool $crop overrule cropping of image (setting to FALSE disables the cropping set in FileReference)
         * @param bool $absolute Force absolute URL
         *
         * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
         * @return string Rendered tag
         */
        public function render($src = null, $width = null, $height = null, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null, $treatIdAsReference = false, $image = null, $crop = null, $absolute = false)
        {

            // init frontend
            /** @todo: should not be necessary any more - try removing this */
            \RKW\RkwBasics\Helper\Common::initFrontendInBackendContext();

            try {

                $result = parent::render($src, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight, $treatIdAsReference, $image, $crop, $absolute);
                return $this->replacePath($result);

            } catch (\Exception $e) {

                // try fallback without rendering!
                try {
                    $image = $this->imageService->getImage($src, $image, $treatIdAsReference);
                    $imageUri = $this->imageService->getImageUri($image, $absolute);

                    $this->tag->addAttribute('src', $imageUri);
                    $this->tag->addAttribute('width', intval($width));

                    $styleAttribute = $this->tag->getAttribute('style');
                    if (strrpos($styleAttribute, ';') !== (strlen($styleAttribute) -1)) {
                        $styleAttribute .= ';';
                    }
                    if ($maxHeight) {
                        $styleAttribute .= 'max-height:' . intval($maxHeight) .'px;';
                    }
                    if ($maxWidth) {
                        $styleAttribute .= 'max-width:' . intval($maxWidth) .'px;';
                    }
                    if ($styleAttribute) {
                        $this->tag->addAttribute('style', $styleAttribute );
                    }

                    $alt = $image->getProperty('alternative');
                    $title = $image->getProperty('title');

                    // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
                    if (empty($this->arguments['alt'])) {
                        $this->tag->addAttribute('alt', $alt);
                    }
                    if (empty($this->arguments['title']) && $title) {
                        $this->tag->addAttribute('title', $title);
                    }

                    $result = $this->replacePath($this->tag->render());
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Using fallback image rendering for image %s. Result: %s', $imageUri, $result));
                    return $result;

                } catch (\Exception $e) {
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Unable to render image %s.', $imageUri));
                }
            }

            return '';
        }

        /**
         * Replaces relative paths and absolute paths to server-root
         *
         * @param string $tag
         * @return string
         */
        protected function replacePath ($tag)
        {

            /* @toDo: Check if Environment-variables are still valid in TYPO3 8.7 and upwards! */
            $replacePaths = [
                GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'),
                $_SERVER['TYPO3_PATH_ROOT'] .'/'
            ];

            foreach ($replacePaths as $replacePath) {
                $tag = preg_replace('/(src|href)="' . str_replace('/', '\/', $replacePath) . '([^"]+)"/', '$1="' . '/$2"', $tag);
            }

            return $tag;
        }


        /**
         * @return LoggerInterface
         */
        protected function getLogger()
        {
            return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }
    }

} else {
    /**
     * Class ImageViewHelper
     *
     * @author Steffen Kroggel <developer@steffenkroggel.de>
     * @copyright Rkw Kompetenzzentrum
     * @package RKW_RkwMailer
     * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
     */
    class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
    {

        /**
         * Resizes a given image (if required) and renders the respective img tag
         *
         * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
         * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
         * @return string Rendered tag
         */
        public function render()
        {

            // init frontend
            /** @todo: should not be necessary any more - try removing this */
            \RKW\RkwBasics\Helper\Common::initFrontendInBackendContext();

            try {

                $result = parent::render();
                return $this->replacePath($result);

            } catch (\Exception $e) {

                // try fallback without rendering!
                try {
                    $image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);
                    $imageUri = $this->imageService->getImageUri($image, $this->arguments['absolute']);

                    $this->tag->addAttribute('src', $imageUri);
                    $this->tag->addAttribute('width', intval($this->arguments['width']));

                    $styleAttribute = $this->tag->getAttribute('style');
                    if (strrpos($styleAttribute, ';') !== (strlen($styleAttribute) -1)) {
                        $styleAttribute .= ';';
                    }
                    if ($this->arguments['maxHeight']) {
                        $styleAttribute .= 'max-height:' . intval($this->arguments['maxHeight']) .'px;';
                    }
                    if ($this->arguments['maxWidth']) {
                        $styleAttribute .= 'max-width:' . intval($this->arguments['maxWidth']) .'px;';
                    }
                    if ($styleAttribute) {
                        $this->tag->addAttribute('style', $styleAttribute );
                    }

                    $alt = $image->getProperty('alternative');
                    $title = $image->getProperty('title');

                    // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
                    if (empty($this->arguments['alt'])) {
                        $this->tag->addAttribute('alt', $alt);
                    }
                    if (empty($this->arguments['title']) && $title) {
                        $this->tag->addAttribute('title', $title);
                    }

                    $result = $this->replacePath($this->tag->render());
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Using fallback image rendering for image %s. Result: %s', $imageUri, $result));
                    return $result;

                } catch (\Exception $e) {
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Unable to render image.'));
                }
            }

            return '';
        }

        /**
         * Replaces relative paths and absolute paths to server-root
         *
         * @param string $tag
         * @return string
         */
        protected function replacePath ($tag)
        {

            /* @toDo: Check if Environment-variables are still valid in TYPO3 8.7 and upwards! */
            $replacePaths = [
                GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'),
                $_SERVER['TYPO3_PATH_ROOT'] .'/'
            ];

            foreach ($replacePaths as $replacePath) {
                $tag = preg_replace('/(src|href)="' . str_replace('/', '\/', $replacePath) . '([^"]+)"/', '$1="' . '/$2"', $tag);
            }

            return $tag;
        }


        /**
         * @return LoggerInterface
         */
        protected function getLogger()
        {
            return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }
    }
}

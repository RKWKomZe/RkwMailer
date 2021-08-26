<?php
namespace RKW\RkwMailer\Persistence;

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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * A class to reduce the size of markers in order to be able to persist them in a database
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class MarkerReducer
{

    /**
     * Namespace Keyword
     *
     * @const string
     */
    const NAMESPACE_KEYWORD = 'RKW_MAILER_NAMESPACES';

    /**
     * Namespace Keyword
     *
     * @const string
     */
    const NAMESPACE_ARRAY_KEYWORD = 'RKW_MAILER_NAMESPACES_ARRAY';


    /**
     * logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;



    /**
     * implodeMarker
     * transform objects into simple references
     *
     * @param array $marker
     * @return array
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function implodeMarker($marker)
    {
        /** @var DataMapper $dataMapper */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $dataMapper = $objectManager->get(DataMapper::class);
        foreach ($marker as $key => $value) {

            // replace current entry with "table => uid" reference
            // keep current variable name, don't use "unset"
            if (is_object($value)) {

                // Normal DomainObject
                if ($value instanceof AbstractEntity) {

                    $namespace = filter_var(
                        $dataMapper->getDataMap(get_class($value))->getClassName(),
                        FILTER_SANITIZE_STRING
                    );

                    if ($value->_isNew()) {
                        $this->getLogger()->log(
                            LogLevel::WARNING,
                            sprintf(
                                'Object with namespace %s in marker-array is not persisted and will be stored as serialized object in the database. This may cause performance issues!',
                                $namespace
                            )
                        );
                    } else {

                        $marker[$key] = self::NAMESPACE_KEYWORD . ' ' . $namespace . ":" . $value->getUid();
                        $this->getLogger()->log(
                            LogLevel::DEBUG,
                            sprintf(
                                'Replacing object with namespace %s and uid %s in marker-array.',
                                $namespace,
                                $value->getUid()
                            )
                        );
                    }

                // ObjectStorage or QueryResult
                } else {

                    if ($value instanceof \Iterator) {
                        
                        // rewind is crucial in live-context!
                        $value->rewind();
                        
                        if (
                            
                            (
                                ($value instanceof QueryResultInterface)
                                && ($firstObject = $value->getFirst())
                            )
                            || (
                                ($value instanceof ObjectStorage)
                                && ($firstObject = $value->current())
                            )
                            && ($firstObject instanceof AbstractEntity)
                        ) {
    
                            $newValues = array();
                            $namespace = filter_var($dataMapper->getDataMap(get_class($firstObject))->getClassName(), FILTER_SANITIZE_STRING);
                            $replaceObjectStorage = true;
                            foreach ($value as $object) {
                                if ($object instanceof AbstractEntity) {
    
                                    if ($object->_isNew()) {
    
                                        $replaceObjectStorage = false;
                                        $this->getLogger()->log(
                                            LogLevel::WARNING, sprintf(
                                                'Object with namespace %s in marker-array is not persisted. The object storage it belongs to will be stored as serialized object in the database. This may cause performance issues!',
                                                $namespace
                                            )
                                        );
                                        break;
    
                                    } else {
    
                                        $newValues[] = $namespace . ":" . $object->getUid();
                                        $this->getLogger()->log(
                                            LogLevel::DEBUG,
                                            sprintf(
                                                'Replacing object with namespace %s and uid %s in marker-array.',
                                                $namespace,
                                                $object->getUid()
                                            )
                                        );
                                    }
                                }
                            }
                            if ($replaceObjectStorage) {
                                $marker[$key] = self::NAMESPACE_ARRAY_KEYWORD . ' ' . implode(',', $newValues);
                            }
    
                        } else {
                            $this->getLogger()->log(
                                LogLevel::WARNING,
                                sprintf(
                                    'Object of class %s in marker-array will be stored as serialized object in the database. This may cause performance issues!',
                                    get_class($value)
                                )
                            );
                        }
                    }
                }
            }
        }

        return $marker;
    }



    /**
     * explodeMarker
     * transform simple references to objects
     *
     * @param array $marker
     * @return array
     */
    public function explodeMarker($marker)
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        foreach ($marker as $key => $value) {

            // check for keyword
            if (
                (is_string($value))
                && (
                    (strpos(trim($value), self::NAMESPACE_KEYWORD) === 0)
                    || (strpos(trim($value), self::NAMESPACE_ARRAY_KEYWORD) === 0)
                )
            ) {

                // check if we have an array here
                $isArray = (bool)(strpos(trim($value), self::NAMESPACE_ARRAY_KEYWORD) === 0);
                $this->getLogger()->log(
                    LogLevel::DEBUG,
                    sprintf(
                        'Detection of objectStorage: %s.',
                        intval($isArray)
                    )
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage */
                $objectStorage = $objectManager->get(ObjectStorage::class);

                // clean value from keyword
                $cleanedValue = trim(
                    str_replace(
                        array(
                            self::NAMESPACE_ARRAY_KEYWORD,
                            self::NAMESPACE_KEYWORD,
                        ),
                        '',
                        $value
                    )
                );

                // Go through list of objects. May be comma-separated in case of QueryResultInterface or ObjectStorage
                $listOfObjectDefinitions = GeneralUtility::trimExplode(',', $cleanedValue);
                foreach ($listOfObjectDefinitions as $objectDefinition) {

                    // explode namespace and uid
                    $explodedValue = GeneralUtility::trimExplode(':', $objectDefinition);
                    $namespace = trim($explodedValue[0]);
                    $uid = intval($explodedValue[1]);

                    if (class_exists($namespace)) {

                        // @toDo: Find a way to get the repository namespace instead of this replace
                        $repositoryName = str_replace('Model', 'Repository', $namespace) . 'Repository';
                        if (class_exists($repositoryName)) {

                            /** @var \TYPO3\CMS\Extbase\Persistence\Repository $repository */
                            $repository = $objectManager->get($repositoryName);

                            // build query - we fetch everything here!
                            $query = $repository->createQuery();
                            $query->getQuerySettings()->setRespectStoragePage(false);
                            $query->getQuerySettings()->setIgnoreEnableFields(true);
                            $query->getQuerySettings()->setIncludeDeleted(true);
                            $query->matching(
                                $query->equals('uid', $uid)
                            )->setLimit(1);


                            if ($result = $query->execute()->getFirst()) {

                                $objectStorage->attach($result);
                                $this->getLogger()->log(
                                    LogLevel::DEBUG,
                                    sprintf(
                                        'Replacing object with namespace %s and uid %s in marker-array.',
                                        $namespace,
                                        $result->getUid()
                                    )
                                );
                            }
                        }
                    }
                }

                // add complete objectStorage OR only the first item of it - depending on keyword
                if ($isArray) {
                    $marker[$key] = $objectStorage;
                } else {

                    // if object not found AND no object storage, delete empty key
                    if ($objectStorage->count() > 0) {
                        $objectStorage->rewind();
                        $marker[$key] = $objectStorage->current();
                    } else {
                        unset($marker[$key]);
                    }
                }
            }
        }

        return $marker;
    }


    /**
     * Returns logger instance
     *
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}

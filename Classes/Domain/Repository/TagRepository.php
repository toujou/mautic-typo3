<?php
declare(strict_types = 1);
namespace Bitmotion\Mautic\Domain\Repository;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Florian Wessels <f.wessels@Leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 *
 ***/

use Mautic\Api\Tags;
use Mautic\Exception\ContextNotFoundException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TagRepository extends AbstractRepository
{
    /**
     * @var Tags
     */
    protected $tagsApi;

    /**
     * @throws ContextNotFoundException
     */
    protected function injectApis(): void
    {
        $this->tagsApi = $this->getApi('tags');
    }

    public function findAll(): array
    {
        return $this->tagsApi->getList('', 0, 999)['tags'] ?: [];
    }

    public function synchronizeTags()
    {
        $availableTags = $this->getAvailableTags();
        $this->deleteAllTags();
        $tags = $this->findAll();
        $time = time();

        foreach ($tags as $tag) {
            if (isset($availableTags[$tag['id']])) {
                $this->updateTag($tag, $time);
            } else {
                $this->insertTag($tag, $time);
            }
        }
    }

    protected function updateTag(array $tag, int $time)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->update('tx_mautic_domain_model_tag')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($tag['id'], \PDO::PARAM_INT)))
            ->set('tstamp', $time)
            ->set('title', $tag['tag'])->set('deleted', 0)->executeStatement();
    }

    protected function insertTag(array $tag, int $time)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->insert('tx_mautic_domain_model_tag')->values([
             'uid' => (int)$tag['id'],
             'crdate' => $time,
             'tstamp' => $time,
             'title' => $tag['tag'],
             'deleted' => 0,
         ])->executeStatement();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_mautic_domain_model_tag');
    }

    protected function deleteAllTags()
    {
        $this->getQueryBuilder()
            ->update('tx_mautic_domain_model_tag')->set('deleted', 1)->executeStatement();
    }

    protected function getAvailableTags(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder
            ->select('*')->from('tx_mautic_domain_model_tag')->executeQuery();

        $availableTags = [];

        while ($row = $result->fetchAssociative()) {
            $availableTags[$row['uid']] = $row;
        }

        return $availableTags;
    }
}

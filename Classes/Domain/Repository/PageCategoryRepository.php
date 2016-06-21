<?php
namespace AUS\AusPage\Domain\Repository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Markus Hölzle <m.hoelzle@andersundsehr.com>, anders und sehr GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class PageCategoryRepository
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Domain\Repository
 */
class PageCategoryRepository extends Repository
{

    /**
     * @param int $dokType
     * @param int $storagePid
     * @return QueryResultInterface
     */
    public function findByDokType(int $dokType = 0, int $storagePid = 0): QueryResultInterface
    {
        $query = $this->createQuery();
        if ($storagePid === 0) {
            $query->getQuerySettings()->setRespectStoragePage(false);
        } else {
            $query->getQuerySettings()->setRespectStoragePage(true);
            $query->getQuerySettings()->setStoragePageIds([$storagePid]);
        }
        if ($dokType !== 0) {
            $query->matching($query->equals('dokType', $dokType));
        }
        return $query->execute();
    }

}

<?php

namespace AUS\AusPage\Domain\Model;

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

/**
 * Class PageFilter
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Domain\Model
 */
class PageFilter
{

    /**
     * @var int
     * @deprecated
     */
    protected $pageCategoryUid = 0;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $pageTreeDepth = 99;

    /**
     * @var int
     */
    protected $pageTreeBegin = 0;

    /**
     * @var int[]
     */
    protected $selectedPages = [];

    /**
     * @var bool
     */
    protected $sortRecursive = false;

    /**
     * @return int
     * @deprecated
     */
    public function getPageCategoryUid()
    {
        return isset($this->fields['page_categories']) ? $this->fields['page_categories'] : $this->pageCategoryUid;
    }

    /**
     * @param int $pageCategoryUid
     * @deprecated
     */
    public function setPageCategoryUid($pageCategoryUid)
    {
        $this->pageCategoryUid = (int)$pageCategoryUid;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        /*
         * include legacy tx_auspage_onelevelnavigation[filter][pageCategoryUid]
         * replace it with include legacy tx_auspage_onelevelnavigation[filter][fields][page_category]
        */
        return ($this->pageCategoryUid > 0) ? array_merge(['page_categories' => $this->pageCategoryUid], $this->fields) : $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return (int)$this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return (int)$this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = (int)$offset;
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return void
     */
    public function _setProperty($propertyName, $propertyValue)
    {
        $this->{$propertyName} = $propertyValue;
    }

    /**
     * @return int[]
     */
    public function getSelectedPages(): array
    {
        return $this->selectedPages;
    }

    /**
     * @param string|int[] $selectedPages
     */
    public function setSelectedPages($selectedPages)
    {
        if (is_string($selectedPages)) {
            $selectedPages = array_filter(array_map('intval', explode(',', $selectedPages)));
        }
        $this->selectedPages = $selectedPages;
    }

    /**
     * @return bool
     */
    public function isSortRecursive()
    {
        return (bool)$this->sortRecursive;
    }

    /**
     * @param bool $sortRecursive
     */
    public function setSortRecursive($sortRecursive)
    {
        $this->sortRecursive = (bool)$sortRecursive;
    }

    /**
     * @return int
     */
    public function getPageTreeDepth()
    {
        return (int)$this->pageTreeDepth;
    }

    /**
     * @return int
     */
    public function getPageTreeBegin()
    {
        return (int)$this->pageTreeBegin;
    }
}

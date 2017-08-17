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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class PageCategory
 *
 * @author Markus Hölzle <m.hoelzle@andersundsehr.com>
 * @package AUS\AusPage\Domain\Model
 */
class PageCategory extends AbstractEntity
{

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var int
     */
    protected $dokType = 0;


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getDokType()
    {
        return $this->dokType;
    }

    /**
     * @param int $dokType
     */
    public function setDokType($dokType)
    {
        $this->dokType = $dokType;
    }
}

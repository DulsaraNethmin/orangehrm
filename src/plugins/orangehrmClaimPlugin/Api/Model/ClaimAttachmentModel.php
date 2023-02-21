<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 *
 */

namespace OrangeHRM\Claim\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\ClaimAttachment;

class ClaimAttachmentModel implements Normalizable
{
    use ModelTrait;

    public function __construct(ClaimAttachment $claimAttachment)
    {
        $this->setEntity($claimAttachment);
        $this->setFilters(
            [
                'requestId',
                'eattachId',
                'eattachFileName',
                'eattachType',
                'eattachSize',
                'eattachDESC',
                'attachedByName',
                'attachedTime',
            ]
        );
        $this->setAttributeNames(
            [
                'requestId',
                'attachmentId',
                'fileName',
                'fileType',
                'fileSize',
                'fileDescription',
                'attachedBy',
                'attachedTime',
            ]
        );
    }
}
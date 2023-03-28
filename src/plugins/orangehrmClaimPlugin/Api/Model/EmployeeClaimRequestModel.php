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
 */

namespace OrangeHRM\Claim\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\ClaimRequest;

class EmployeeClaimRequestModel implements Normalizable
{
    use ModelTrait;

    public function __construct(ClaimRequest $claimRequest)
    {
        $this->setEntity($claimRequest);
        $this->setFilters(
            [
                'id',
                'referenceId',
                ['getClaimEvent', 'getId'],
                ['getClaimEvent', 'getName'],
                ['getCurrencyType', 'getId'],
                ['getCurrencyType', 'getName'],
                'description',
                'status',
                ['getEmployee', 'getEmpNumber'],
                ['getEmployee', 'getFirstName'],
                ['getEmployee', 'getLastName'],
                ['getEmployee', 'getMiddleName'],
                ['getEmployee', 'getEmployeeId'],
                ['getEmployee', 'getEmployeeTerminationRecord', 'getId'],
                ['getDecorator', 'getTotalExpense'],
                ['getDecorator', 'getSubmittedDate']
            ]
        );
        $this->setAttributeNames(
            [
                'id',
                'referenceId',
                ['claimEvent', 'id'],
                ['claimEvent', 'name'],
                ['currencyType', 'id'],
                ['currencyType', 'name'],
                'remarks',
                'status',
                ['employee', 'empNumber'],
                ['employee', 'firstName'],
                ['employee', 'lastName'],
                ['employee', 'middleName'],
                ['employee', 'employeeId'],
                ['employee', 'terminationId'],
                'total',
                'submittedDate'
            ]
        );
    }
}

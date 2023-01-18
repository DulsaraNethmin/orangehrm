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

namespace OrangeHRM\Claim\Api;

use OrangeHRM\Claim\Api\Model\ClaimEventModel;
use OrangeHRM\Claim\Dto\ClaimEventSearchFilterParams;
use OrangeHRM\Claim\Traits\Service\ClaimServiceTrait;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Exception\RecordNotFoundException;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Api\V2\Validator\Rules\EntityUniquePropertyOption;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Entity\ClaimEvent;

class ClaimEventAPI extends Endpoint implements CrudEndpoint
{
    use EntityManagerHelperTrait;
    use ClaimServiceTrait;
    use AuthUserTrait;

    public const PARAMETER_NAME = 'name';
    public const PARAMETER_DESCRIPTION = 'description';
    public const PARAMETER_ID = 'id';
    public const PARAMETER_IDS = 'ids';
    public const PARAMETER_STATUS = 'status';
    public const DESCRIPTION_MAX_LENGTH = 1000;
    public const NAME_MAX_LENGTH = 100;

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
    {
        $claimEvent = new ClaimEvent();
        $this->setClaimEvent($claimEvent);
        return new EndpointResourceResult(ClaimEventModel::class, $claimEvent);
    }

    /**
     * @param ClaimEvent $claimEvent
     */
    public function setClaimEvent(ClaimEvent $claimEvent)
    {
        $claimEvent->setName($this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NAME));
        $claimEvent->setDescription($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_DESCRIPTION));
        $claimEvent->setStatus($this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_STATUS, true));
        $userId = $this->getAuthUser()->getUserId();
        $claimEvent->getDecorator()->setUserByUserId($userId);
        $this->getClaimEventService()->getClaimEventDao()->saveEvent($claimEvent);
    }

    /**
     * @return EndpointResult
     */
    public function getAll(): EndpointResult
    {
        $claimEventSearchFilterParams = new ClaimEventSearchFilterParams();
        $this->setSortingAndPaginationParams($claimEventSearchFilterParams);
        $claimEventSearchFilterParams->setName($this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_NAME));
        $claimEventSearchFilterParams->setStatus($this->getRequestParams()->getBooleanOrNull(RequestParams::PARAM_TYPE_QUERY, self::PARAMETER_STATUS));
        $claimEvents = $this->getClaimEventService()->getClaimEventDao()->getClaimEventList($claimEventSearchFilterParams);
        $count = $this->getClaimEventService()->getClaimEventDao()->getClaimEventCount($claimEventSearchFilterParams);
        return new EndpointCollectionResult(ClaimEventModel::class, $claimEvents, new ParameterBag([CommonParams::PARAMETER_TOTAL => $count]));
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_NAME,
                    new Rule(
                        Rules::STRING_TYPE
                    )
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_STATUS,
                    new Rule(
                        Rules::STRING_TYPE
                    )
                )
            ),
            ...$this->getSortingAndPaginationParamsRules(ClaimEventSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->requiredParamRule(
                $this->getNameRule(false),
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_DESCRIPTION,
                    new Rule(
                        Rules::STRING_TYPE
                    ),
                    new Rule(Rules::LENGTH, [null, self::DESCRIPTION_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_STATUS,
                    new Rule(Rules::BOOL_TYPE)
                )
            ),
        );
    }

    protected function getNameRule(bool $update): ParamRule
    {
        $entityProperties = new EntityUniquePropertyOption();
        $ignoreValues = ['isDeleted' => true];
        if ($update) {
            $ignoreValues['getId'] = $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_ATTRIBUTE,
                CommonParams::PARAMETER_ID
            );
        }
        $entityProperties->setIgnoreValues($ignoreValues);

        return new ParamRule(
            self::PARAMETER_NAME,
            new Rule(Rules::STRING_TYPE),
            new Rule(Rules::LENGTH, [null, self::NAME_MAX_LENGTH]),
            new Rule(Rules::ENTITY_UNIQUE_PROPERTY, [ClaimEvent::class, 'name', $entityProperties])
        );
    }

    /**
     * @return EndpointResult
     */
    public function delete(): EndpointResult
    {
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_IDS);
        $this->getClaimEventService()->getClaimEventDao()->deleteClaimEvents($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_IDS,
                new Rule(Rules::ARRAY_TYPE)
            ),
        );
    }

    /**
     * @return EndpointResult
     * @throws RecordNotFoundException
     */
    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $claimEvent = $this->getClaimEventService()->getClaimEventDao()->getClaimEventById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($claimEvent, ClaimEvent::class);
        return new EndpointResourceResult(ClaimEventModel::class, $claimEvent);
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_ID,
                new Rule(Rules::POSITIVE)
            ),
        );
    }

    /**
     * @return EndpointResult
     * @throws RecordNotFoundException
     */
    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_ID);
        $claimEvent = $this->getClaimEventService()->getClaimEventDao()->getClaimEventById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($claimEvent, ClaimEvent::class);
        $this->setClaimEvent($claimEvent);
        return new EndpointResourceResult(ClaimEventModel::class, $claimEvent);
    }

    /**
     * @return ParamRuleCollection
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_ID,
                new Rule(Rules::POSITIVE)
            ),
            $this->getNameRule(true),
            new ParamRule(
                self::PARAMETER_DESCRIPTION,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [null, self::DESCRIPTION_MAX_LENGTH]),
            ),
            new ParamRule(
                self::PARAMETER_STATUS,
                new Rule(Rules::BOOL_TYPE)
            )
        );
    }
}

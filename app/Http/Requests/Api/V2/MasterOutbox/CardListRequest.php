<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use App\Models\MasterOutbox\Card;
use Sxope\Http\Requests\FilteringDefinitions;
use Sxope\Http\Requests\SearchRequest;

class CardListRequest extends SearchRequest
{
    public static function getAvailableFields(): array
    {
        return Card::$onlyFields;
    }

    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        return FilteringDefinitions::create(static function (FilteringDefinitions $ruleBuilder) {
            $ruleBuilder->boolean('is_custom');
            $ruleBuilder->containsBytes('entity_id');
            $ruleBuilder->containsInteger('entity_type_id');
            $ruleBuilder->boolean('show_on_dashboard');
        });
    }

    public static function getSortingDefinitions(): array
    {
        return [
            'created_at',
        ];
    }
}

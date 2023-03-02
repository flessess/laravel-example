<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use App\Http\Requests\RuleBuilders\FilesViewingRuleBuilder;
use Sxope\Http\Requests\FilteringDefinitions;
use Sxope\Http\Requests\SearchRequest;

class FileGetListRequest extends SearchRequest
{
    public static function getAvailableFields(): array
    {
        return [
            'file_id',
            'description',
            'assigned_period',
            'visibility_type_id',
            'card_id',
            'file_size',
            'is_read',
            'created_at',
            'created_by',
            'created_by_name',
            'updated_at',
            'updated_by',
            'updated_by_name',
        ];
    }

    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        return FilteringDefinitions::create(static function (FilteringDefinitions $ruleBuilder) {
            $ruleBuilder->containsBytes('card_id');
            $ruleBuilder->containsBytes('entity_id');
            $ruleBuilder->containsInteger('entity_type_id');
            $ruleBuilder->boolean('is_read');
        });
    }

    public static function getSortingDefinitions(): array
    {
        return [
            'created_at',
            'is_read',
        ];
    }
}

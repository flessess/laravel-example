<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Attributes\Schemas\Models\V2\MasterOutbox\CardAllowedEntity;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\CardAllowedEntityDeleteRequestSchema;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\CardAllowedEntityRequest;
use App\Http\Requests\Api\V2\MasterOutbox\CardAllowedEntitiesDeleteRequest;
use App\Http\Requests\Api\V2\MasterOutbox\CardAllowedEntitiesRequest;
use App\Services\MasterOutbox\CardAllowedEntitiesService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Schemas\Operations\SxopePost;
use Sxope\Http\Attributes\Schemas\Requests\JsonRequestBody;
use Sxope\Http\Attributes\Schemas\Responses\EntityListResponse;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;
use Sxope\Http\Attributes\Schemas\Responses\OkResponse;
use Sxope\ValueObjects\Id;

class CardAllowedEntitiesController extends BaseController
{
    #[SxopePost(
        path: '/api/v2/master-outbox/cards/{cardId}/allowed-entities',
        operationId: 'master-outbox-card-allowed-entity-create-v2',
        description: 'Create card allowed entity',
        security: ['ApiKeyAuth' => []],
        requestBody: new JsonRequestBody(new CardAllowedEntityRequest()),
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'cardId', name: 'cardId', description: 'Card id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            [EntityListResponse::class, CardAllowedEntity::class, '2.0'],
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function create(
        string $cardId,
        CardAllowedEntitiesRequest $request,
        CardAllowedEntitiesService $service
    ): JsonResponse {
        return $this->respondSuccess($service->create(Id::create($cardId), $request->toDtos()));
    }

    #[SxopePost(
        path: '/api/v2/master-outbox/cards/{cardId}/allowed-entities/delete',
        operationId: 'master-outbox-card-allowed-entity-delete-v2',
        description: 'Create card allowed entity',
        summary: 'Delete card allowed entity',
        security: ['ApiKeyAuth' => []],
        requestBody: new JsonRequestBody(new CardAllowedEntityDeleteRequestSchema()),
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'cardId', name: 'cardId', description: 'Card id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function delete(
        string $cardId,
        CardAllowedEntitiesDeleteRequest $request,
        CardAllowedEntitiesService $service
    ): JsonResponse {
        $service->delete(Id::create($cardId), $request->get('card_allowed_entity_id'));
        return $this->respondSuccess([]);
    }
}

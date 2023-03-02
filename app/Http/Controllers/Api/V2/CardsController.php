<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Attributes\Schemas\Models\V2\MasterOutbox\CardWithAllowedEntities;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\CardCreateRequestSchema;
use App\Http\Requests\Api\V2\MasterOutbox\CardCreateRequest;
use App\Http\Requests\Api\V2\MasterOutbox\CardListRequest;
use App\Http\Requests\Api\V2\MasterOutbox\CardUpdateRequest;
use App\Services\MasterOutbox\CardsService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Parameters\Collections\Pagination;
use Sxope\Http\Attributes\Schemas\Operations\SxopeGet;
use Sxope\Http\Attributes\Schemas\Operations\SxopePost;
use Sxope\Http\Attributes\Schemas\Requests\FormDataRequestBody;
use Sxope\Http\Attributes\Schemas\Requests\SearchRequest;
use Sxope\Http\Attributes\Schemas\Responses\EntityListResponse;
use Sxope\Http\Attributes\Schemas\Responses\EntityResponse;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;
use Sxope\Http\Attributes\Schemas\Responses\OkResponse;
use Sxope\ValueObjects\Id;

class CardsController extends BaseController
{
    #[Post(
        path: '/api/v2/master-outbox/cards',
        operationId: 'master-outbox-card-create-v2',
        description: 'Create card',
        summary: 'Create card',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(CardCreateRequestSchema::class),
        tags: ['Upload API - v2 - Master Outbox Cards'],
        responses: [
            new EntityResponse('MasterOutboxCard', '2.0'),
            new Error422Response(),
            new Error500Response(),
        ]
    )]
    public function create(CardCreateRequest $cardCreateRequest, CardsService $cardsService): JsonResponse
    {
        return $this->respondSuccess($cardsService->create($cardCreateRequest->toDto()));
    }

    #[Post(
        path: '/api/v2/master-outbox/cards/{cardId}',
        operationId: 'master-outbox-card-update-v2',
        description: 'Update card',
        summary: 'Update card',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody('MasterOutboxCardUpdateRequestV2'),
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'cardId', name: 'cardId', description: 'Card id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            new EntityResponse('MasterOutboxCard', '2.0'),
            new Error404Response(),
            new Error422Response(),
            new Error500Response(),
        ]
    )]
    public function update(string $cardId, CardUpdateRequest $request, CardsService $cardsService): JsonResponse
    {
        return $this->respondSuccess($cardsService->update(Id::create($cardId), $request->toDto()));
    }

    #[Delete(
        path: '/api/v2/master-outbox/cards/{cardId}',
        operationId: 'master-outbox-card-delete-v2',
        description: 'Delete card',
        summary: 'Delete card',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'cardId', name: 'cardId', description: 'Card id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            new OkResponse(),
            new Error404Response(),
            new Error422Response(),
            new Error500Response(),
        ]
    )]
    public function delete(string $cardId, CardsService $cardsService): JsonResponse
    {
        $cardsService->delete(Id::create($cardId));
        return $this->respondSuccess([]);
    }

    #[SxopePost(
        path: '/api/v2/master-outbox/cards/list',
        operationId: 'master-outbox-card-list-v2',
        summary: 'List card',
        security: ['ApiKeyAuth' => []],
        requestBody: [SearchRequest::class, CardListRequest::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: new Pagination(Pagination::SCOPE_PAGE),
        responses: [
            [EntityListResponse::class, 'MasterOutboxCard', '2.0'],
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function list(CardListRequest $request, CardsService $cardsService): JsonResponse
    {
        return $this->respondSuccess($cardsService->list($request->getSearchConditions()));
    }

    #[SxopeGet(
        path: '/api/v2/master-outbox/cards/{cardId}',
        operationId: 'master-outbox-card-get-v2',
        description: 'Get card',
        summary: 'Get card',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'cardId', name: 'cardId', description: 'Card id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            new EntityResponse(CardWithAllowedEntities::class, '2.0'),
            new Error404Response(),
            new Error422Response(),
            new Error500Response(),
        ]
    )]
    public function get(string $cardId, CardsService $cardsService): JsonResponse
    {
        return $this->respondSuccess($cardsService->findById(Id::create($cardId)));
    }
}

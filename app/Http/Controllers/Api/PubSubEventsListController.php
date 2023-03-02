<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class PubSubEventsListController
 * @package App\Http\Controllers\Api
 */
class PubSubEventsListController
{
//    /**
//     *
//     * @param Request $request
//     * @param DataSourceApiContract $api
//     *
//     * @return JSONResponse
//     *
//     * @OA\Get(
//     *   path="/pubsub-events-list",
//     *   tags={"events-list"},
//     *   summary="Method for getting events list",
//     *   description="Method for getting events list",
//     *   operationId="events-list",
//     *   @OA\Response(
//     *      response=200,
//     *      description="Events list",
//     *      @OA\JsonContent(
//     *        @OA\Property(
//     *          property="code",
//     *          type="string",
//     *          example="200"
//     *        ),
//     *        @OA\Property(
//     *          property="events",
//     *          type="array",
//     *            @OA\Items(
//     *              type="object",
//     *              @OA\Property(
//     *                property="topic",
//     *                type="string"
//     *              ),
//     *              @OA\Property(
//     *                property="eventType",
//     *                type="string"
//     *              ),
//     *              @OA\Property(
//     *                property="description",
//     *                type="string"
//     *              )
//     *            ),
//     *        )
//     *      )
//     *   ),
//     *   @OA\Response(response=422, description="Bad request"),
//     *   @OA\Response(response=401, description="Authentication error"),
//     *   @OA\Response(response=418, description="Custom Error Message"),
//     *   @OA\Response(response=500, description="Internal Server Error"),
//     *   security={
//     *     {"ApiKeyAuth": {}}
//     *   }
//     * )
//     */
//    public function eventsList(Request $request)
//    {
//        return response()->json(
//            [
//                'code' => Response::HTTP_OK,
//                'events' => [
//                    [
//                        'topic' => config('pubsub.pubsub_app_events_topic'),
//                        'eventType' => 'PubSubAppEvent',
//                        'description' => 'Application event',
//                    ],
//                    [
//                        'topic' => config('pubsub.pubsub_app_events_topic'),
//                        'eventType' => 'PubSubExampleEvent',
//                        'description' => 'Sample event',
//                    ],
//                ],
//            ],
//            Response::HTTP_OK
//        );
//    }
}

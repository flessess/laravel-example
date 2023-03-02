<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *      version="1.0.1",
 *      title="SXOPE upload API",
 *      description="SXOPE upload API docs",
 *      @OA\Contact(
 *          email="admin@sxope-upload-api.com"
 *      ),
 *     @OA\License(
 *         name="Commercial",
 *     )
 * )
 *
 * Security
 * https://swagger.io/docs/specification/authentication/api-keys/
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     description="Use X-API-KEY in header",
 *     name="X-API-KEY",
 *     in="header",
 *     securityScheme="ApiKeyAuth",
 * )
 *
 * @OA\Schema(
 *     schema="Errors_ServerError",
 *     required={"code", "status", "data", "meta"},
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         example="500"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="error"
 *     ),
 *      @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(
 *            type="string",
 *            example="Server error"
 *         )
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="string",
 *         example="api version 1.0"
 *     )
 * )
 * @OA\Schema(
 *     schema="Errors_NotFound",
 *     required={"code", "status", "data", "meta"},
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         example="404"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="error"
 *     ),
 *      @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(
 *            type="string",
 *            example="Entity not found"
 *         )
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="string",
 *         example="api version 1.0"
 *     )
 * )
 * @OA\Schema(
 *     schema="Errors_InvalidCredentials",
 *     required={"code", "status", "data", "meta"},
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         example="403"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="error"
 *     ),
 *      @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(
 *            type="string",
 *            example="Invalid X-API-KEY token"
 *         )
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="string",
 *         example="api version 1.0"
 *     )
 * )
 * @OA\Schema(
 *      schema="Fguid",
 *      @OA\Property(
 *          property="fguid",
 *          type="string",
 *          example="0f3755e6f6164e88a989fff5a24584de.pdf",
 *      ),
 * )
 * @OA\Schema(
 *     schema="PayerFileRequestBody",
 *     @OA\Property(
 *          property="payer_id",
 *          type="string",
 *          example="d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00",
 *      ),
 *      @OA\Property(
 *          property="payer_file_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="payer_file_visibility_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="assigned_period",
 *          type="string",
 *          example="2021-12-31",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          example="Test custom value",
 *      ),
 *      @OA\Property(
 *          property="attachment",
 *          type="file",
 *          description="Only PDF files allowed with max size ~50MB",
 *      ),
 * )
 * @OA\Schema(
 *     schema="PayerFile",
 *     @OA\Property(
 *          property="payer_id",
 *          type="string",
 *          example="d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00",
 *      ),
 *      @OA\Property(
 *          property="payer_file_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="data_owner_id",
 *          type="string",
 *          example="d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          example="Example description",
 *      ),
 *      @OA\Property(
 *          property="payer_file_visibility_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="payer_file_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="assigned_period",
 *          type="string",
 *          example="2021-12-31",
 *      ),
 *      @OA\Property(
 *          property="original_file_name",
 *          type="string",
 *          example="avatar.jpg",
 *      ),
 *      @OA\Property(
 *          property="file_size",
 *          type="integer",
 *          example="123",
 *      ),
 *      @OA\Property(
 *          property="is_read",
 *          type="boolean",
 *          example="true",
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          type="string",
 *          example="2020-01-01 00:00:00"
 *      ),
 *      @OA\Property(
 *          property="created_by",
 *          type="string",
 *          example="b4f67f6-a5dd-4bc4-9182-9e4d90976840"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          example="2020-01-01 00:00:00"
 *      ),
 *      @OA\Property(
 *          property="updated_by",
 *          type="string",
 *          example="b4f67f6-a5dd-4bc4-9182-9e4d90976840"
 *      ),
 * )
 * @OA\Schema(
 *     schema="PayerFileUpdateRequestBody",
 *     @OA\Property(
 *          property="payer_file_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="payer_file_visibility_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="assigned_period",
 *          type="string",
 *          example="2021-12-31",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          example="Test custom value",
 *      ),
 * )
 * @OA\Schema(
 *     schema="PayerFileUpdateViewStatusRequestBody",
 *     @OA\Property(
 *          property="view_status",
 *          type="string",
 *          example="true",
 *          enum={"true","false"},
 *      )
 * )
 *
 * @OA\Schema(
 *     schema="PayerFilesAggregated",
 *     @OA\Property(
 *          property="payer_id",
 *          type="string",
 *          example="d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00",
 *      ),
 *      @OA\Property(
 *          property="total_files",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="total_read_files",
 *          type="integer",
 *          example="0",
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          example="2021-01-01",
 *      ),
 * )
 * @OA\Schema(
 *     schema="PayerFileVisibilityType",
 *     @OA\Property(
 *          property="payer_file_visibility_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="payer_file_visibility_type_name",
 *          type="string",
 *          example="PUBLIC",
 *      ),
 * )
 * @OA\Schema(
 *     schema="PayerFileType",
 *     @OA\Property(
 *          property="payer_file_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="payer_file_type_name",
 *          type="string",
 *          example="NOTICE",
 *      ),
 * )
 * @OA\Schema(
 *     schema="MasterOutboxFileRequestBody",
 *     @OA\Property(
 *          property="entity_id",
 *          type="string",
 *          example="d46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00",
 *      ),
 *      @OA\Property(
 *          property="master_outbox_file_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="master_outbox_file_entity_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="master_outbox_file_visibility_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="assigned_period",
 *          type="string",
 *          example="2021-12-31",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          example="Test custom value",
 *      ),
 *      @OA\Property(
 *          property="attachment",
 *          type="file",
 *          description="Only PDF files allowed with max size ~50MB",
 *      ),
 * )
 * @OA\Schema(
 *     schema="MasterOutboxFileUpdateRequestBody",
 *     @OA\Property(
 *          property="master_outbox_file_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="master_outbox_file_visibility_type_id",
 *          type="integer",
 *          example="1",
 *      ),
 *      @OA\Property(
 *          property="assigned_period",
 *          type="string",
 *          example="2021-12-31",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          example="Test custom value",
 *      ),
 * )
 * @OA\Schema(
 *     schema="MasterOutboxFileUpdateViewStatusRequestBody",
 *     @OA\Property(
 *          property="view_status",
 *          type="string",
 *          example="true",
 *          enum={"true","false"},
 *      )
 * )
 * @OA\Schema(
 *     schema="UploadApiFile",
 *     @OA\Property(
 *          property="file_id",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19",
 *      ),
 *      @OA\Property(
 *          property="data_owner_id",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19",
 *      ),
 *      @OA\Property(
 *          property="original_file_name",
 *          type="string",
 *          example="PAYER",
 *      ),
 *      @OA\Property(
 *          property="file_size",
 *          type="integer",
 *          example="123",
 *      ),
 *      @OA\Property(
 *          property="pages_count",
 *          type="integer",
 *          example="123",
 *      ),
 *      @OA\Property(
 *          property="uploaded_at",
 *          type="string",
 *          example="2022-03-22T11:37:23.146400Z",
 *      ),
 *      @OA\Property(
 *          property="known_source_id",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19",
 *      ),
 *      @OA\Property(
 *          property="file_source_id",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19",
 *      ),
 *      @OA\Property(
 *          property="uploaded_by",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19",
 *      ),
 *      @OA\Property(
 *          property="mime_type_id",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19",
 *      ),
 *      @OA\Property(
 *          property="file_status_id",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19",
 *      ),
 * )
 * @OA\Schema(
 *     schema="MasterOutboxCardCreateRequestV2",
 *     @OA\Property(
 *          property="card_name",
 *          type="string",
 *          example="Card name",
 *     ),
 *     @OA\Property(
 *          property="logo",
 *          type="file",
 *          description="Only image files allowed with max size ~500KB",
 *     ),
 *     @OA\Property(
 *          property="show_on_dashboard",
 *          type="boolean",
 *          description="true",
 *     ),
 * )
 * @OA\Schema(
 *     schema="MasterOutboxCardUpdateRequestV2",
 *     @OA\Property(
 *          property="card_name",
 *          type="string",
 *          example="Card name"
 *     ),
 *     @OA\Property(
 *          property="logo",
 *          type="file",
 *          description="Only image files allowed with max size ~500KB",
 *     ),
 *     @OA\Property(
 *          property="show_on_dashboard",
 *          type="boolean",
 *          description="true",
 *     ),
 *     @OA\Property(
 *          property="delete_logo",
 *          type="boolean",
 *          description="false",
 *     ),
 * )
 * @OA\Schema(
 *     schema="MasterOutboxCard",
 *     @OA\Property(
 *          property="card_id",
 *          type="string",
 *          example="14fc52e2-613e-4519-93ab-60f36c443d19"
 *     ),
 *     @OA\Property(
 *          property="card_name",
 *          type="string",
 *          description="Card name",
 *     ),
 *     @OA\Property(
 *          property="logo",
 *          type="string",
 *          description="base64_string",
 *     ),
 *     @OA\Property(
 *          property="is_custom",
 *          type="boolean",
 *          description="true",
 *     ),
 *     @OA\Property(
 *          property="show_on_dashboard",
 *          type="boolean",
 *          description="true",
 *     ),
 *      @OA\Property(
 *          property="created_at",
 *          type="string",
 *          example="2020-01-01 00:00:00"
 *      ),
 *      @OA\Property(
 *          property="created_by_name",
 *          type="string",
 *          example="Test User"
 *      ),
 *      @OA\Property(
 *          property="created_by",
 *          type="string",
 *          example="b4f67f6-a5dd-4bc4-9182-9e4d90976840"
 *      ),
 *      @OA\Property(
 *          property="updated_by_name",
 *          type="string",
 *          example="Test User"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          example="Test User"
 *      ),
 *      @OA\Property(
 *          property="updated_by",
 *          type="string",
 *          example="b4f67f6-a5dd-4bc4-9182-9e4d90976840"
 *      ),
 * )
 */
class ApiMetadata
{
}

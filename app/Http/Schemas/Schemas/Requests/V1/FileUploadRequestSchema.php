<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V1;

use Sxope\Http\Attributes\Properties\FileProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\DateProperty;

class FileUploadRequestSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new FileProperty('file', 'File'),
                new StringProperty(
                    'source',
                    'EMAIL',
                    [
                        'FAX',
                        'EMAIL',
                        '5STAR',
                        'SXOPE UNIVERSAL INBOX',
                        'DESKTOP PARSER',
                        'SXOPE PORTAL',
                        'SXOPE',
                        'ARCHER',
                        'CHART RETRIEVAL'
                    ]
                ),
                new StringProperty('email', 'testtest@emample.com', description: 'Sender email'),
                new UuidProperty('user_id', 'Sender user ID - for uploads from 5star / SXOPE'),
                new StringProperty('npi'),
                new IntegerProperty('provider_portal_user_id'),
                new StringProperty('email_subject'),
                new StringProperty('sender_phone_number', example: '+123-123-1234'),
                new UuidProperty('data_owner_id'),
                new IntegerProperty('skip_tagging', 1, 'Skip tagging process for file - 0 / 1'),
                new IntegerProperty('file_type_id', 1, 'File type id: 1 - PhyPartners, 2 - Archer'),
                new UuidProperty('member_id'),
                new UuidProperty('pcp_id'),
                new UuidProperty('document_type_id'),
                new DateProperty('date_of_service'),
            ]
        );
    }
}

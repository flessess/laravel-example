<?php

declare(strict_types=1);

namespace App\Repository\MasterOutbox;

use App\Models\MasterOutbox\Card;
use Illuminate\Database\Eloquent\Model;
use Sxope\Repositories\Repository;
use Sxope\Repositories\SearchRepository;

/**
 * @extends Repository<Card>
 */
class CardsRepository extends SearchRepository
{
    public static function getEntityInstance(): Model
    {
        return new Card();
    }
}

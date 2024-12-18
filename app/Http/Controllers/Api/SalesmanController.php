<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Salesman\IndexRequest;
use App\Http\Requests\Api\Salesman\StoreRequest;
use App\Http\Requests\Api\Salesman\UpdateRequest;
use App\Http\Requests\Api\Salesman\DeleteRequest;
use App\Models\Salesman;

class SalesmanController extends ApiBaseController
{
    protected $model = Salesman::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function storing($salesman)
    {
        $salesman->user_type = 'salesman';

        return $salesman;
    }

    public function updating($salesman)
    {
        $salesman->user_type = 'salesman';

        return $salesman;
    }
}

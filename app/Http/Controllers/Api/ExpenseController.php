<?php

namespace App\Http\Controllers\Api;

use App\Classes\Notify;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Expense\IndexRequest;
use App\Http\Requests\Api\Expense\StoreRequest;
use App\Http\Requests\Api\Expense\UpdateRequest;
use App\Http\Requests\Api\Expense\DeleteRequest;
use App\Models\Expense;
use Examyou\RestAPI\Exceptions\ApiException;

class ExpenseController extends ApiBaseController
{
    protected $model = Expense::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

  
}

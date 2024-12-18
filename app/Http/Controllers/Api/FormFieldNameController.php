<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\FormFieldName\IndexRequest;
use App\Http\Requests\Api\FormFieldName\StoreRequest;
use App\Http\Requests\Api\FormFieldName\UpdateRequest;
use App\Http\Requests\Api\FormFieldName\DeleteRequest;
use App\Http\Requests\Api\FormFieldName\UpdateStatusRequest;
use App\Models\FormFieldName;
use Examyou\RestAPI\ApiResponse;

class FormFieldNameController extends ApiBaseController
{
    protected $model = FormFieldName::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function storing($formFieldName)
    {
        $loggedUser = user();

        $formFieldName->created_by = $loggedUser->id;
        $formFieldName->updated_by = $loggedUser->id;

        return $formFieldName;
    }

    public function updating($formFieldName)
    {
        $loggedUser = user();

        $formFieldName->updated_by = $loggedUser->id;

        return $formFieldName;
    }

    public function updateStatus(UpdateStatusRequest $request)
    {
        $xid = $request->xid;
        $id = $this->getIdFromHash($xid);

        $formFieldName = FormFieldName::find($id);
        $formFieldName->visible = $request->status;
        $formFieldName->save();

        return ApiResponse::make('Success', []);
    }

    public function allFormFieldNames()
    {
        $allFormFieldName = FormFieldName::select('id', 'field_name', 'similar_field_names')
            ->where('visible', 1)
            ->get();

        return ApiResponse::make('Success', [
            'data' => $allFormFieldName
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Form\IndexRequest;
use App\Http\Requests\Api\Form\StoreRequest;
use App\Http\Requests\Api\Form\UpdateRequest;
use App\Http\Requests\Api\Form\DeleteRequest;
use App\Http\Requests\Api\Form\UpdateStatusRequest;
use App\Models\Form;
use Examyou\RestAPI\ApiResponse;

class FormController extends ApiBaseController
{
    protected $model = Form::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        $user = user();

        if (!$user->ability('admin', 'forms_view_all')) {
            $query = $query->where('created_by', $user->id);
        }

        return $query;
    }

    public function storing($form)
    {
        $loggedUser = user();

        $form->created_by = $loggedUser->id;
        $form->updated_by = $loggedUser->id;

        return $form;
    }

    public function updating($form)
    {
        $loggedUser = user();

        $form->updated_by = $loggedUser->id;

        return $form;
    }

    public function updateStatus(UpdateStatusRequest $request)
    {
        $xid = $request->xid;
        $id = $this->getIdFromHash($xid);

        $form = Form::find($id);
        $form->status = $request->status;
        $form->save();

        return ApiResponse::make('Success', []);
    }

    public function allForms()
    {
        $allForms = Form::select('id', 'name', 'form_fields')->get();

        return ApiResponse::make('Success', [
            'data' => $allForms
        ]);
    }
}

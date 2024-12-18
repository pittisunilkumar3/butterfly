<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\EmailTemplate\IndexRequest;
use App\Http\Requests\Api\EmailTemplate\StoreRequest;
use App\Http\Requests\Api\EmailTemplate\UpdateRequest;
use App\Http\Requests\Api\EmailTemplate\DeleteRequest;
use App\Http\Requests\Api\EmailTemplate\UpdateStatusRequest;
use App\Models\EmailTemplate;
use Examyou\RestAPI\ApiResponse;

class EmailTemplateController extends ApiBaseController
{
    protected $model = EmailTemplate::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        $user = user();

        if (!$user->ability('admin', 'email_templates_view_all')) {
            $query = $query->where('created_by', $user->id);
        }

        return $query;
    }

    public function storing($emailTemplate)
    {
        $loggedUser = user();

        $emailTemplate->created_by = $loggedUser->id;
        $emailTemplate->updated_by = $loggedUser->id;

        return $emailTemplate;
    }

    public function updating($emailTemplate)
    {
        $loggedUser = user();

        $emailTemplate->updated_by = $loggedUser->id;

        return $emailTemplate;
    }

    public function updateStatus(UpdateStatusRequest $request)
    {
        $xid = $request->xid;
        $id = $this->getIdFromHash($xid);

        $emailTemplate = EmailTemplate::find($id);
        $emailTemplate->status = $request->status;
        $emailTemplate->save();

        return ApiResponse::make('Success', []);
    }

    public function allEmailTemplates()
    {
        $user = user();

        $emailTemplates = EmailTemplate::select('id', 'name', 'body', 'subject')
            ->where('status', 1)
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhere('sharable', 1);
            })
            ->get();

        return ApiResponse::make('Success', [
            'email_templates' => $emailTemplates
        ]);
    }
}

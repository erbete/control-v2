<?php

namespace Control\Rebinding\Http;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Control\Common\Controller;
use Control\Common\Traits\HttpErrorResponseTrait;
use Control\Rebinding\Actions\RebindingGetAccountDetailsAction;
use Control\Rebinding\Actions\RebindingGetAccountsAction;
use Control\Rebinding\Actions\RebindingSetNoteAction;
use Control\Rebinding\Actions\RebindingSetStatusAction;
use Control\Rebinding\Mappers\RebindingAccountDetailsMapper;
use Control\Infrastructure\RebindingActivity;
use Control\Infrastructure\RebindingAccount;
use Control\Rebinding\Mappers\RebindingRebindedAccountsMapper;

class RebindingController extends Controller
{
    use HttpErrorResponseTrait;

    public function index(
        Request $request,
        RebindingGetAccountsAction $getAccounts
    ) {
        $accounts = $getAccounts->execute(
            $request->lockinFromMonth,
            $request->lockinToMonth,
            $request->statuses,
            $request->periods,
            $request->perPage ?? 50,
            $request->page ?? 1,
        );

        return response()->json($accounts);
    }

    public function details(Request $request, RebindingGetAccountDetailsAction $getAccountDetails)
    {
        $customer = $getAccountDetails->execute($request->accountId);

        if ($customer->count() === 0) {
            return $this->responseFailure(Response::HTTP_NOT_FOUND, "Fant ingen kunde med konto-ID $request->accountId.");
        }

        return response()->json(RebindingAccountDetailsMapper::map($customer, $request->periods));
    }

    public function setNote(Request $request, RebindingSetNoteAction $setNote)
    {
        $request->validate(RebindingActivity::$addNoteRules, RebindingActivity::$addNoteMessages);

        $result = $setNote->execute(
            $request->accountId,
            $request->note,
        );

        if (!$result->accountExists) {
            return $this->responseFailure(Response::HTTP_NOT_FOUND, "Fant ingen kunde med konto-ID $request->accountId.");
        }

        if (!$result->success) {
            return $this->responseFailure(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($result->conflict) {
            return $this->responseFailure(Response::HTTP_CONFLICT);
        }

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    public function setStatus(Request $request, RebindingSetStatusAction $setStatus)
    {
        // Modify status input before validation
        $status = trim(strtoupper($request->input('status')));
        $request->merge(['status' => $status]);

        $request->validate(RebindingActivity::setStatusRules(), RebindingActivity::$setStatusMessages);

        $result = $setStatus->execute(
            $request->accountId,
            $request->status,
        );

        if (!$result->accountExists) {
            return $this->responseFailure(Response::HTTP_NOT_FOUND, "Fant ingen kunde med konto-ID $request->accountId.");
        }

        if (!$result->success) {
            return $this->responseFailure(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($result->conflict) {
            return $this->responseFailure(Response::HTTP_CONFLICT);
        }

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    public function rebindedAccounts(Request $request)
    {
        return response()
            ->json(
                RebindingRebindedAccountsMapper::map(
                    RebindingAccount::paginate($request->perPage ?? 50)
                )
            );
    }
}

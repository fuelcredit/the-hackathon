<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\FuelPurchase;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\{Hash, Http};
use App\Models\Validators\UserValidator;
use Illuminate\Http\{Request, Response};
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserController extends Controller
{
    public function index()
    {
       
        $users = User::query()
            ->when(
                request('status') == 'active',
                fn($builder) => $builder->where(
                    'accountStatus', User::ACCOUNT_VERIFIED
                ),
                fn($builder) => $builder
            )
            ->when(
                request('status') == 'inactive',
                fn($builder) => $builder->where(
                    'accountStatus', User::ACCOUNT_NOT_VERIFIED
                ) 
            )
            ->latest('id')
            ->paginate(20);

        return response()->json($users);
    }

    // create e-Naira Consumer
    public function createConsumer(Request $request){
        $http = Http::post(
            'https://rgw.k8s.apis.ng/centric-platforms/uat/enaira-user/CreateConsumerV2', [
                {
                    "channelCode": "APISNG",
                    "uid": "22142360969",
                    "uidType": "BVN",
                    "reference": "NXG3547585HGTKJHGO",
                    "title": "Mr",
                    "firstName": "Ifeanyichukwu",
                    "middleName": "Gerald",
                    "lastName": "Mbah",
                    "userName": "icmbah@cbn.gov.ng",
                    "phone": "08036349590",
                    "emailId": "icmbah@cbn.gov.ng",
                    "postalCode": "900110",
                    "city": "gwarinpa",
                    "address": "Lagos Estate, Abuja",
                    "countryOfResidence": "NG",
                    "tier": "2",
                    "accountNumber": "0025592222",
                    "dateOfBirth": "31/12/1987",
                    "countryOfBirth": "NG",
                    "password": "1234567890",
                    "remarks": "Passed",
                    "referralCode": "@imbah.01"
                  }
            ]
        )->json();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\V1\User $user 
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        abort_unless(
            auth()->user()->tokenCan('admin.disabled-user.update'),
            Response::HTTP_FORBIDDEN
        );

        $user->update(
            [
                'accountStatus' => User::ACCOUNT_NOT_VERIFIED
            ]
        );

        return response()->json(
            [
                'message' => 'Account disabled successfully'
            ]
        );
    }

     /**
     * User Profile Update.
     *
     * @param \Illuminate\Request $request 
     * @param \App\Models\V1\User $user 
     * 
     * @return \Illuminate\Http\Response
     */
    public function profileUpdate(Request $request, User $user)
    {
        abort_unless(
            auth()->user()->tokenCan('user.update'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', $user);

        $attributes = (new UserValidator())->validate($user, $request->all());

        $user->fill($attributes)->save();

        return response()->json(
            [
                'message' => 'Account updated successfully'
            ]
        );
    }

    /**
     * Reset Transaction PIN
     * 
     * @return \Illuminate\Http\Response
     */
    public function resetTransactionPIN()
    {
        abort_unless(
            auth()->user()->tokenCan('user.reset.transaction.pin'),
            Response::HTTP_FORBIDDEN
        );

        $user = auth()->user();

        $code = User::generateRandomInt();

        $message = "Your transaction pin verification code on fuel credit is ".$code;
        /* $message = "Your otp verification on fuel credit is ".
            Str::substr($code, 0, 3).' '.Str::substr($code, 3, 6); */

        $user->otp = $code;
        $user->save();

        // $sms = User::sendOTP4TransactionPIN($user->mobileNumber, $message);
        $sms = User::sendOTP4rmMultitexter($user->mobileNumber, $message);

        if ($sms['status'] === 1) {
            return response()->json(
                [
                    'message' => 'OTP Successfully sent to '.$user->mobileNumber
                ], 200
            );
        }
        

        /* if ($sms['response']['status'] === "SUCCESS") {
            return response()->json(
                [
                    'message' => 'OTP Successfully sent to '.$user->mobileNumber
                ], 200
            );
        } */

        return response()->json(
            [
                'message' => 'Error occur while sending SMS to '
                .$user->mobileNumber.', please try again!'
            ], 404
        );

    }

    /**
     * Update User Transaction PIN
     * 
     * @return \Illuminate\Http\Response
     */
    public function updateTransactionPIN()
    {
        abort_unless(
            auth()->user()->tokenCan('user.update.transaction.pin'),
            Response::HTTP_FORBIDDEN
        );

        $data = validator(
            request()->all(), [
                'pin' => 'required|string|min:4|max:4',
            ]
        )->validate();

        $user = auth()->user();
        $resetTransactionPIN = $data['pin'];
        $user->transactionPin = $resetTransactionPIN;
        $user->save();

        return response()->json(
            [
                'message' => 'Transaction PIN reset successfully'
            ]
        );
    }

    /**
     * Get User Info
     *
     * @param \App\Models\V1\User $user 
     * 
     * @return \Illuminate\Http\Response
     */
    public function getUser(User $user)
    {
        abort_unless(
            auth()->user()->tokenCan('user.info'),
            Response::HTTP_FORBIDDEN
        );

        return response()->json(
            [
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'mobileNumber' => $user->mobileNumber,
                'nin' => $user->nin,
                'email' => $user->email,
                'pin' => $user->transactionPin,
            ]
        );
    }

    
    /**
     * Reset User Password
     *
     * @param \Illuminate\Request $request 
     * @param \App\Models\V1\User $user 
     * 
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request, User $user)
    {
        abort_unless(
            auth()->user()->tokenCan('change.password'),
            Response::HTTP_FORBIDDEN
        );

        $validator = Validator::make(
            $request->all(), [
                'newPassword' => 'required|string|min:8',
                'oldPassword' => 'required|string|min:8',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'errors' => $validator->errors()
                ], 401
            );
        }

        if (!Hash::check($request->oldPassword, $user->password)) {
            return response()->json(
                [
                    'oldPassword' => 'invalid old password'
                ], 401
            );
        }

        $user->password = $request->newPassword;
        $user->save();

        return response()->json(
            [
                'message' => 'Password reset successfully'
            ]
        );
    }

    /** 
     * Fetches user, fuel purchase total, wallet balance, credit balance and 
     * number of beneficiary
     * 
     * @return Response 
     */
    public function userData()
    {
        abort_unless(
            auth()->user()->tokenCan('display-transaction-to-admin'),
            Response::HTTP_FORBIDDEN
        );

        $user = auth()->user();

        // abort_unless(
        //     $user->userRole !== UserRole::USER_ADMIN 
        //     || $user->userRole !== UserRole::USER_SUPPORT,
        //     403,
        //     'You dont have access to authorized this'
        // );

        $user = User::query()
            ->select(
                [
                    'id', 'email', 'dateRegistered', 'mobileNumber',
                    'firstName', 'lastName'
                ]
            )
            ->with(
                [
                    'wallet:id,userId', 'wallet.walletBalance:id,walletId,balance',
                    'approvedCreditRequests:id,userId,creditUsed',
                ]
            )
            // ->totalFuelPurchases()
            ->withCount(['subUser'])
            // ->withSum(['totalFuelPurchase'], 'purchaseAmount')
            ->latest('id')
            ->paginate(20);
        return response()->json(
            $user
        );
    }


    }

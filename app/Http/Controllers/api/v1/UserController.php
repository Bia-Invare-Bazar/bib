<?php

namespace App\Http\Controllers\api\v1;

use App\Model\v1\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{
    private $apiToken;

    public function __construct()
    {
        // Unique Token
        $this->apiToken = uniqid(base64_encode(str_random(60)));
    }

    public function actionRegister(Request $request)
    {
        // Validations
        $rules = [
            'userName' => 'required|string|max:50|unique:user',
            'password' => 'required|string|min:6',
            'email' => 'email|max:50|unique:user',
            'mobile' => 'required|string|digits:11|unique:user',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            // Validation failed
            return response()->json([
                'message' => $validator->messages(),
            ]);
        } else {
            if (preg_match("/^09[0-9]{9}$/", $request->mobile)) {
                $postArray = [
                    'userName' => $request->userName,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'mobile' => $request->mobile,
                    'token' => $this->apiToken,
                    'type' => 1,
                    'state' => 1,
                    'joinDate' => Carbon::now(),
                    'lastLoginDate' => Carbon::now(),
                ];
                // $user = User::GetInsertId($postArray);
                $user = Users::insert($postArray);

                if ($user) {
                    return response()->json([
                        'userName' => $request->userName,
                        'email' => $request->email,
                        'mobile' => $request->mobile,
                        'token' => $this->apiToken,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Registration failed, please try again.',
                    ]);
                }
            } else {
                return response([
                    'status' => '400',
                    'message' => 'شماره موبایل نامعتبر',
                ]);
            }

        }
    }

    public function actionLogin(Request $request)
    {
        // Validations
        $rules = [
            'mobile' => 'required|string|digits:11',
            'password' => 'required|min:6'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            // Validation failed
            return response()->json([
                'message' => $validator->messages(),
            ]);
        } else {
            if (preg_match("/^09[0-9]{9}$/", $request->mobile))
            {
                // Fetch User
                $user = Users::where('mobile', $request->mobile)->first();
                if ($user) {
                    // Verify the password
                    if (password_verify($request->password, $user->password)) {
                        // Update Token
                        $postArray = ['token' => $this->apiToken];
                        $login = Users::where('mobile', $request->mobile)->update($postArray);

                        if ($login) {
                            return response()->json([
                                'userName' => $user->userName,
                                'mobile' => $user->mobile,
                                'token' => $this->apiToken,
                            ]);
                        }
                    } else {
                        return response()->json([
                            'message' => 'Invalid Password',
                        ]);
                    }
                } else {
                    return response()->json([
                        'message' => 'User not found',
                    ]);
                }
            }else{
                return response([
                    'status' => '400',
                    'message' => 'شماره موبایل نامعتبر',
                ]);
            }

        }
    }

    public function actionLogout(Request $request)
    {
        $token = $request->header('Authorization');
        $user = Users::where('token',$token)->first();
        if($user) {
            $postArray = ['token' => null];
            $logout = Users::where('userID',$user->userID)->update($postArray);
            if($logout) {
                return response()->json([
                    'message' => 'User Logged Out',
                ]);
            }
        } else {
            return response()->json([
                'message' => 'User not found',
            ]);
        }
    }

}

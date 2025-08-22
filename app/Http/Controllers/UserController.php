<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserSignUpRequest;
use App\Models\User;
use App\Models\UserProfile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserSignUpRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            $profilePictureUrl = null;

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = $user->id . now()->format('Ymd') . str_replace(' ', '', $user->name);
                $extention = $file->extension();
                $path = $file->storeAs('profile', $filename . '.' . $extention, 'public');
                $profilePictureUrl = asset('storage/' . $path);
            }

            UserProfile::create([
                'user_id' => $user->id,
                'profile_picture_url' => $profilePictureUrl,
                'about' => $validated['about'] ?? null,
                'designation' => $validated['designation'] ?? null
            ]);

            DB::commit();
            return response()->json([
                'message' => 'User created successfully.'
            ], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create user.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * User Login implement here
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => ['required'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $credentials = $request->only('email_or_phone', 'password');
        $field = filter_var($credentials['email_or_phone'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$field] = $credentials['email_or_phone'];
        unset($credentials['email_or_phone']);

        $user = User::with('profile')->where($field, $credentials[$field])->first();

        if ($user && !$user->email_verified_at) {
            return response()->json([
                'error' => 'Email not verified',
            ], 401);
        }
        if ($user && !$user->phone_verified_at) {
            return response()->json([
                'error' => 'Phone not verified',
            ], 401);
        }

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('API Token')->plainTextToken;

            Auth::login($user);

            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture_url' => $user->profile->profile_picture_url ?? null,
                'about' => $user->profile->about ?? null,
                'designation' => $user->profile->designation ?? null,
            ];

            return response()->json([
                'message' => 'Successfully logged in',
                'user' => $userData,
                'token' => $token
            ], 201);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserSignUpRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}

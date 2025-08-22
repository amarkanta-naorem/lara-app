<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserSignUpRequest;
use App\Models\User;
use App\Models\UserProfile;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

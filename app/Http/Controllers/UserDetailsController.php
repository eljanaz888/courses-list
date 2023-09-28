<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;

class UserDetailsController extends Controller
{
    //add user details function
    public function addUserDetails(Request $request)
    {

        try {
            $this->validate($request, [
                'street_address' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'postal_code' => 'required|integer',
                'country' => 'required|string',
                'phone_number' => 'required|string',
                'date_of_birth' => 'required|date',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Validation Error',
                'error' => $e->getMessage()
            ], 500);
        }

        try {
            $userDetails = $request->user()->userDetails()->create([
                'street_address' => $request->street_address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
            ]);
            return response()->json([
                'message' => 'User details added successfully',
                'users_details' => $userDetails
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occured while adding user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //update user details function

    public function updateUserDetails(Request $request)
    {

        try {
            $this->validate($request, [
                'street_address' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'postal_code' => 'required|integer',
                'country' => 'required|string',
                'phone_number' => 'required|string',
                'date_of_birth' => 'required|date',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Validation Error',
                'error' => $e->getMessage()
            ], 500);
        }

        try {
            $userDetails = $request->user()->userDetails()->update([
                'street_address' => $request->street_address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
            ]);
            return response()->json([
                'message' => 'User details updated successfully',
                'users_details' => $userDetails
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occured while updating user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

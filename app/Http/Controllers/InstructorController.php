<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class InstructorController extends Controller
{
    /**
     * Create a new InstructorController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api-instructor', ['except' => ['register', 'login']]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::guard('api-instructor')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Register a new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:instructors',
            'password' => 'required|string|min:6'
        ]);

        try {

            $instructor = Instructor::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $instructor->save();

            //return successful response
            return response()->json(['instructor' => $instructor, 'message' => 'CREATED'], 201);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Instructor Registration Failed!'], 409);
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'instructor' => Auth::instructor(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    //create threads function

    public function createThread(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'body' => 'required|string',
                'course_id' => 'required|integer',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Validation Error',
                'error' => $e->getMessage()
            ], 500);
        }

        try {
            /**
             * @var Instructor $instructor
             */
            $instructor = auth('api-instructor')->user();

            // Check if the instructor owns the course
            $course = $instructor->courses()->find($request->course_id);

            if (!$course) {
                return response()->json([
                    'message' => 'You are not authorized to create a thread for this course or the course does not exist'
                ], 404);
            }

            // Create a thread for the course
            $thread = $course->threads()->create([
                'title' => $request->title,
                'body' => $request->body,
                'course_id' => $request->course_id
            ]);

            return response()->json([
                'message' => 'Thread created successfully',
                'thread' => $thread
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while creating a thread',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //delete user reply function

    public function deleteReply($reply_id)
    {
        try {
            /**
             * @var Instructor $instructor
             */
            $instructor = auth('api-instructor')->user();
            if (!$instructor) {
                return response()->json([
                    'message' => 'You are not authorized to delete this reply'
                ], 404);
            }
            $reply = Reply::find($reply_id);
            if (!$reply) {
                return response()->json([
                    'message' => 'Reply not found'
                ], 404);
            }
            $reply->delete();
            return response()->json([
                'message' => 'Reply deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occured while deleting reply',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

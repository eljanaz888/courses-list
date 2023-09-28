<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);
        $credentials = $request->only(['email', 'password']);
        if (!$token = Auth::attempt($credentials)) {
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
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'is_admin' => 'required|boolean'
        ]);

        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $request->is_admin ?? '0'
            ]);

            $user->save();

            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
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
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    //enroll course function
    public function enrollCourse(Request $request, $course_id)
    {
        try {
            $user = $request->user();
            $user->courses()->attach($course_id);

            return response()->json([
                'message' => 'Course enrolled successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while enrolling course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //get threads for one course

    public function getThreads(Request $request, $course_id)
    {
        try {
            $course = Course::find($course_id);
            $threads = Thread::all();
            $courseThreads = $threads->where('course_id', $course_id);
            $user = $request->user();

            if ($user->courses->contains($course) === false) {
                return response()->json([
                    'message' => 'You are not enrolled in this course',
                ], 401);
            }

            return response()->json([
                'threads' => $courseThreads,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while fetching threads',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteCourse(Request $request, $course_id)
    {
        try {
            /**
             * @var User $user
             */
            $user = auth('api')->user();
            if (!$user->isAdmin()) {
                return response()->json([
                    'message' => 'You are not authorized to delete this reply'
                ], 404);
            }

            $course = Course::find($course_id);
            if (!$course) {
                return response()->json([
                    'message' => 'Course not found'
                ], 404);
            }
            $course->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while deleting reply',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

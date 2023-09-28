<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Instructor;
use App\Models\User;


class CoursesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api-instructor', ['except' => ['index', 'show']]);
    }

    //add course function

    public function addCourse(Request $request)
    {

        try {
            $this->validate($request, [
                'title' => 'required|string',
                'description' => 'required|string',
                'level' => 'required|string',
                'language' => 'required|string',
                'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/', 'numeric'],
                'currency' => 'required|string',
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

            $course = $instructor->courses()->create([
                'title' => $request->title,
                'description' => $request->description,
                'level' => $request->level,
                'language' => $request->language,
                'price' => $request->price,
                'currency' => $request->currency,
            ]);
            return response()->json([
                'message' => 'Course added successfully',
                'course' => $course
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occured while adding course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //update course function

    public function updateCourse(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'description' => 'required|string',
                'level' => 'required|string',
                'language' => 'required|string',
                'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/', 'numeric'],
                'currency' => 'required|string',
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

            // Find the course by its ID that belongs to the authenticated instructor
            $course = $instructor->courses()->find($id);

            if (!$course) {
                return response()->json([
                    'message' => 'Course not found'
                ], 404);
            }

            // Update the course attributes
            $course->update([
                'title' => $request->title,
                'description' => $request->description,
                'level' => $request->level,
                'language' => $request->language,
                'price' => $request->price,
                'currency' => $request->currency,
            ]);

            return response()->json([
                'message' => 'Course updated successfully',
                'course' => $course
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while updating course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //admin delete course function

    public function deleteCourse($course_id)
    {
        try {
            /**
             * @var Instructor $deleter
             */
            $deleter = auth('api-instructor')->user();

            if (!$deleter) {
                /**
                 * @var User $deleter
                 */
                $deleter = auth('api')->user();

                if (!$deleter->isAdmin()) {
                    return response()->json([
                        'message' => 'You are not authorised to perform this action'
                    ], 401);
                }
            }
            $course = $deleter->courses()->find($course_id);

            if (!$course) {
                return response()->json([
                    'message' => 'Course not found'
                ], 404);
            }
            $course->delete();

            return response()->json([
                'message' => 'Course deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while deleting course',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

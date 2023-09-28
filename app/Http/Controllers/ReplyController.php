<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReplyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // User can reply to a thread

    public function reply(Request $request, $thread_id)
    {
        $validator = Validator::make($request->all(), [
            'reply' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the thread and check if the user is enrolled in the course
        $thread = Thread::find($thread_id);
        if (!$thread) {
            return response()->json([
                'message' => 'Thread not found',
            ], 404);
        }

        $course_id = $thread->course_id;
        $course = Course::find($course_id);
        $user = $request->user();

        if (!$user->courses->contains($course_id)) {
            return response()->json([
                'message' => 'You are not enrolled in this course',
            ], 401);
        }

        // Create the reply
        $reply = $user->replies()->create([
            'reply' => $request->reply,
            'thread_id' => $thread_id,
        ]);

        return response()->json([
            'message' => 'Reply successfully created',
            'reply' => $reply,
        ], 201);
    }

    public function deleteReply($reply_id)
    {
        try {
            /**
             * @var User $user
             */
            $user = auth('api')->user();
            if (!$user) {
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

            // Check if the user is authorized to delete the reply
            if ($reply->user_id === $user->id) {
                $reply->delete();

                return response()->json([
                    'message' => 'Reply deleted successfully',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'You are not authorized to delete this reply'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while deleting reply',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\API\AuthController;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/




$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
    $router->post('/instructor-register', "InstructorController@register");
    $router->post('/instructor-login', "InstructorController@login");



    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/logout', 'AuthController@logout');
        $router->post('/instructor-logout', 'InstructorController@logout');
        $router->post('/add-user-details', 'UserDetailsController@addUserDetails');
        $router->post('/update-user-details', 'UserDetailsController@updateUserDetails');
        $router->post('/enroll-course/{course_id}', 'AuthController@enrollCourse');
        $router->get('/get-threads/{course_id}', 'AuthController@getThreads');
        $router->post('/reply/{thread_id}', 'ReplyController@reply');
        $router->delete('/delete-reply/{reply_id}', 'ReplyController@deleteReply');
        $router->delete('/delete-course/{course_id}', 'AuthController@deleteCourse');
    });

    $router->group(['middleware' => 'auth:api-instructor'], function () use ($router) {
        $router->post('/add-course', 'CoursesController@addCourse');
        $router->post('/update-course/{id}', 'CoursesController@updateCourse');
        $router->post("/create-thread", "InstructorController@createThread");
        $router->delete('/delete-reply/{reply_id}', 'InstructorController@deleteReply');
        $router->delete('/delete-course/{course_id}', 'CoursesController@deleteCourse');
    });
});

<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['basicAuth'])->group(function () {
	Route::post('register_api', 'AuthApiController@index');
	Route::post('validate_otp', 'AuthApiController@validateOtp');
	Route::post('resend_otp', 'AuthApiController@resendOtp');

	Route::middleware(['tokenAuth'])->group(function () {
		Route::post('users/me', 'UserApiController@show');
		Route::post('users/update', 'UserApiController@update');
		Route::post('users/address/update', 'UserApiController@updateAddress');
		Route::post('users/delete', 'UserApiController@destroy');
        Route::post('users/logout', 'UserApiController@logoutUser')->name('user.logout');
		Route::post('users/notification/status', 'UserApiController@updateNotificationStatus');

        //Enrolled Contest
        Route::post('contest/enrolled_contest', 'EnrolledContestController@index')->name('contest.enrolled_contest');
        Route::post('contest/enrolling', 'EnrolledContestController@store')->name('contest.enrolling');

        //language
        Route::post('language/list', 'LanguageApiController@index')->name('language.list');

        // start Contest playing
        Route::post('contest/start_playing', 'LiveContestController@startPlaying')->name('contest.start_playing');

        //contest
        Route::post('contest/show', 'ContestApiController@show')->name('contest.show');
        Route::post('contest/list', 'ContestApiController@index')->name('contest.list');

        //contest answer
        Route::post('contest_answer/create', 'ContestAnswerApiController@store')->name('contest_answer.create');

        //State and city api
        Route::post('state/list', 'StateCityApiController@index')->name('state.list');
        Route::post('city/list', 'StateCityApiController@citiesList')->name('city.list');

        //FAQs
        Route::post('faqs/list', 'FaqApiController@index')->name('faqs.list');

        // Policies api
        Route::post('policies', 'PolicyApiController@show')->name('policies.show');

        //update fcm
        Route::post('notification_token/update', 'UserApiController@updateFcmId')->name('notification_token.update');

        //suggest question
        Route::post('suggest_question', 'SuggestQuestionApiController@suggestQuestion')->name('suggest_question.suggest');
        
        //home listing
        Route::post('home/list','HomeApiController@index')->name('home.list');
        Route::post('home/contest_result_details','HomeApiController@contestResultDetails');
        Route::post('home/contest_answer_details','HomeApiController@contestAnswerDetails');

        //notification list
        Route::post('notification/list','UserNotificationApiController@index');
    });
    //contact us
    Route::post('contact/create','ContactApiController@store')->name('contact.create');
    Route::post('contact/show','ContactApiController@show')->name('contact.show');
    
}); 

Route::middleware(['tokenAuth'])->group(function () {
    Route::post('startup_api', 'StartupApiController@index')->name('startup_api');
});


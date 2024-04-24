<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Exception $e, Request $request) {
            if ($request->is('api/*')) {
                $data['exception_message'] = $e->getMessage();
                $data['exception_code'] = $e->getCode();
                $data['exception_line'] = $e->getLine();
                $data['exception_file'] = $e->getFile();
                \Log::error("Exception Message: ".$data['exception_message']." __LINE__".$data['exception_line']." __FILE__ ".$data['exception_file']);

                return response()->json(["error" => true, "message" => $data['exception_message'], "data"=> $data ], 403);
            }
        });
    }
}

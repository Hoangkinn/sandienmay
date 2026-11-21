<?php

namespace App\Exceptions;

use App\Services\LogService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
    /*public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            LogService::frontend()->log('error', $e->getMessage()."\n".$e->getFile().':'.$e->getLine());
        });
    }*/
    public function report(Throwable $e): void
    {
        // Để mặc định Laravel xử lý validation, không log
        if ($e instanceof ValidationException) {
            parent::report($e);
            return;
        }
        if ($e instanceof NotFoundHttpException) {
            $detect = new CrawlerDetect;
            $userAgent = request()->header('User-Agent');
            $botName = $detect->isCrawler($userAgent) ? $detect->getMatches() : null;

            LogService::log404()->log('error',
                url()->current() . "\t"
                . url()->previous() . "\t"
                . ($botName ?: 'Human')
            );
        } elseif ($e instanceof MethodNotAllowedHttpException){
            LogService::hack()->log('warning',
                'Invalid Method: ' . $e->getMessage() . "\n"
                . 'Method: ' . request()->method() . "\n"
                . 'URL: ' . url()->current() . "\n"
                . 'IP: ' . request()->ip()
            );
        } else {
            LogService::frontend()->log('error',
                $e->getMessage() . "\n"
                . $e->getFile() . ':' . $e->getLine() . "\n"
                . 'Current: ' . url()->current()
            );
        }
//        parent::report($e);
    }
}

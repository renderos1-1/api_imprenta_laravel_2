<?php

namespace App\Http\Controllers;

use App\Repositories\UserLogRepository;
use Illuminate\View\View;

class UserLogController extends Controller
{
    protected $userLogRepository;

    public function __construct(UserLogRepository $userLogRepository)
    {
        $this->userLogRepository = $userLogRepository;
    }

    /**
     * Display user activity logs
     */
    public function index(): View
    {
        $logs = $this->userLogRepository->getPaginatedLogs(25);
        return view('userlog', compact('logs'));
    }
}

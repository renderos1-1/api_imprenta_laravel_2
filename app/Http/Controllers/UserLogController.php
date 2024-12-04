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
        // Obtener solo los últimos 20 registros
        $logs = $this->userLogRepository->getLatestLogs(20);
        return view('userlog', compact('logs'));
    }
}

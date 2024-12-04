<?php

namespace App\Repositories;

use App\Models\UserLog;
use Illuminate\Support\Facades\Request;

class UserLogRepository
{
    /**
     * Create a new user log entry
     */
    public function log(string $dui, string $action): UserLog
    {
        return UserLog::create([
            'dui' => $dui,
            'action' => $action,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }

    /**
     * Get the latest logs with limit
     */
    public function getLatestLogs(int $limit = 20)
    {
        return UserLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get logs for a specific user
     */
    public function getUserLogs(string $dui, int $limit = 20)
    {
        return UserLog::with('user')
            ->where('dui', $dui)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }
}

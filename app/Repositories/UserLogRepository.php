<?php

namespace App\Repositories;

use App\Models\UserLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class UserLogRepository
{
    /**
     * Create a new user log entry
     *
     * @param string $dui
     * @param string $action
     * @return UserLog
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
     *
     * @param int $limit Number of records to retrieve
     * @return \Illuminate\Support\Collection
     */
    public function getLatestLogs(int $limit = 20)
    {
        return DB::table('user_logs')
            ->join('users', 'user_logs.dui', '=', 'users.dui')
            ->select(
                'user_logs.*',
                'users.full_name'
            )
            ->orderBy('user_logs.created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a specific user
     *
     * @param string $dui
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUserLogs(string $dui, int $limit = 20)
    {
        return DB::table('user_logs')
            ->join('users', 'user_logs.dui', '=', 'users.dui')
            ->select(
                'user_logs.*',
                'users.full_name'
            )
            ->where('user_logs.dui', $dui)
            ->orderBy('user_logs.created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

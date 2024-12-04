<?php

namespace App\Repositories;

use App\Models\UserLog;
use Illuminate\Support\Facades\Request;

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
     * Get paginated user logs
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedLogs(int $perPage = 15)
    {
        return UserLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get logs for a specific user
     *
     * @param string $dui
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserLogs(string $dui, int $perPage = 15)
    {
        return UserLog::with('user')
            ->where('dui', $dui)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

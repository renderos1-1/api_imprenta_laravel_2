<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class UserLogRepository
{
    /**
     * Get the latest user logs with user details
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
}

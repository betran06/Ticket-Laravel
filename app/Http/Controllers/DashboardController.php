<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DashboardResource;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        $currenMonth = Carbon::now()->startOfMonth();
        $endOfMonth = $currenMonth->copy()->endOfMonth();

        $totalTickets = Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])->count();

        $activeTickets = Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])
            ->where('status', '!=', 'resolved')
            ->count();
        
        $resolvedTickets = Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->count();

        $avgResolutionTime = Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->whereNotNull('completed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_time'))
            ->value('avg_time') ?? 0;

        $statusDistribution = [
            'open' => Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])->where('status', 'open')->count(),
            'onprogress' => Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])->where('status', 'onprogress')->count(),
            'resolved' => Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])->where('status', 'resolved')->count(),
            'rejected' => Ticket::whereBetween('created_at', [$currenMonth, $endOfMonth])->where('status', 'rejected')->count(),
        ];

        $dashboardData = [
            'total_tickets' => $totalTickets,
            'active_tickets' => $activeTickets,
            'resolved_tickets' => $resolvedTickets,
            'avg_resolution_time' => round($avgResolutionTime, 1),
            'status_distribution' => $statusDistribution,
        ];

        return response()->json([
            'message' => 'Dashboard statistics fetched successfully',
            'data' => new DashboardResource($dashboardData)
        ]);
    }
}

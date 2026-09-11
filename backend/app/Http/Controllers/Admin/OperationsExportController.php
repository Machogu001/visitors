<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Models\Visit;
use App\Services\AuditRecorder;
use App\Services\OccupancyService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class OperationsExportController extends Controller
{
    public function rollCall(Request $request, OccupancyService $occupancy, AuditRecorder $audit): StreamedResponse
    {
        $user = $this->admin($request);
        $rows = $occupancy->current();
        $audit->record('export.roll_call', null, $user, metadata: ['row_count' => $rows->count()]);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Visitor', 'Company', 'Host', 'Site', 'Checked in', 'Status']);

            foreach ($rows as $row) {
                fputcsv($output, array_map($this->escapeCell(...), [
                    $row['visitor'], $row['company'], $row['host'], $row['site'],
                    $row['checked_in_at']?->toIso8601String(), $row['overdue'] ? 'Overdue' : 'On site',
                ]));
            }

            fclose($output);
        }, 'emergency-roll-call-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function visits(Request $request, AuditRecorder $audit): StreamedResponse
    {
        $user = $this->admin($request);
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = filled($validated['from'] ?? null) ? now()->parse($validated['from'])->startOfDay() : now()->subDays(30)->startOfDay();
        $to = filled($validated['to'] ?? null) ? now()->parse($validated['to'])->endOfDay() : now()->endOfDay();
        abort_if($from->diffInDays($to) > 366, 422, 'Report range may not exceed 366 days.');

        $rows = Visit::query()
            ->with(['site:id,name', 'department:id,name', 'host:id,first_name,name'])
            ->withCount('visitors')
            ->whereBetween('scheduled_from', [$from, $to])
            ->orderBy('scheduled_from')
            ->get();
        $audit->record('export.visit_report', null, $user, metadata: [
            'from' => $from->toDateString(), 'to' => $to->toDateString(), 'row_count' => $rows->count(),
        ]);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Visit ID', 'Site', 'Department', 'Title', 'Host', 'Starts', 'Ends', 'Status', 'Participants']);

            foreach ($rows as $visit) {
                fputcsv($output, array_map($this->escapeCell(...), [
                    $visit->id, $visit->site?->name, $visit->department?->name, $visit->title,
                    $visit->host?->fullName, $visit->scheduled_from?->toIso8601String(),
                    $visit->scheduled_until?->toIso8601String(), $visit->status, $visit->visitors_count,
                ]));
            }

            fclose($output);
        }, 'visit-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function admin(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active && $user->hasAnyRole(['admin', 'super_admin']), 403);

        return $user;
    }

    private function escapeCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
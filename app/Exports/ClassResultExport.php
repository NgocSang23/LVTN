<?php

namespace App\Exports;

use App\Models\History;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassResultExport implements FromCollection, WithHeadings
{
    protected $classroom;

    public function __construct($classroom)
    {
        $this->classroom = $classroom;
    }

    public function collection()
    {
        return History::whereHas('test.classrooms', function ($q) {
            $q->where('class_rooms.id', $this->classroom->id);
        })
            ->with('user', 'test')
            ->get()
            ->map(function ($record) {
                return [
                    'Học viên' => $record->user->name,
                    'Email' => $record->user->email,
                    'Bài kiểm tra' => $record->test->content,
                    'Số câu đúng' => (int) $record->correct_count,
                    'Tổng câu' => (int) $record->total_questions,
                    'Điểm' => (int) $record->score,
                    'Thời gian nộp' => $record->created_at->format('d/m/Y H:i'),
                ];
            });
    }

    public function headings(): array
    {
        return ['Học viên', 'Email', 'Bài kiểm tra', 'Số câu đúng', 'Tổng câu', 'Điểm', 'Thời gian nộp'];
    }
}

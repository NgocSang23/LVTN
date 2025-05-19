<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeworkHistoryController extends Controller
{
    public function defineEssay()
    {
        $userId = Auth::guard('web')->user()->id;

        $define_data = DB::table('answer_users as au')
            ->join('users as u', 'au.user_id', '=', 'u.id')
            ->join('questions as q', 'au.question_id', '=', 'q.id')
            ->join('topics as t', 'q.topic_id', '=', 't.id')
            ->join('subjects as s', 't.subject_id', '=', 's.id')
            ->join('cards as c', 'q.card_id', '=', 'c.id')
            ->join('users as u_name', 'c.user_id', '=', 'u_name.id')
            ->select(
                't.id as topic_id',
                't.title as ten_chu_de',
                's.name as ten_mon_hoc',
                'u_name.name as nguoi_tao',
                DB::raw('COUNT(DISTINCT au.question_id) as tong_so_the_da_hoc'),
                DB::raw('MIN(c.id) as first_card_id'),
                DB::raw("GROUP_CONCAT(DISTINCT c.id ORDER BY c.id ASC SEPARATOR ',') as card_ids")
            )
            ->where('u.id', $userId)
            ->where('q.type', 'definition')
            ->groupBy('t.id', 't.title', 's.name', 'u_name.name')
            ->get();

        foreach ($define_data as $item) {
            $item->tong_so_the = DB::table('cards as c')
                ->join('questions as q', 'c.id', '=', 'q.card_id')
                ->where('q.topic_id', $item->topic_id)
                ->where('q.type', 'definition')
                ->distinct('c.id')
                ->count('c.id');
        }

        $essay_data = DB::table('answer_users as au')
            ->join('users as u', 'au.user_id', '=', 'u.id')
            ->join('questions as q', 'au.question_id', '=', 'q.id')
            ->join('topics as t', 'q.topic_id', '=', 't.id')
            ->join('subjects as s', 't.subject_id', '=', 's.id')
            ->join('cards as c', 'q.card_id', '=', 'c.id')
            ->join('users as u_name', 'c.user_id', '=', 'u_name.id')
            ->select(
                't.id as topic_id',
                't.title as ten_chu_de',
                's.name as ten_mon_hoc',
                'u_name.name as nguoi_tao',
                DB::raw('COUNT(DISTINCT au.question_id) as tong_so_the_da_hoc'),
                DB::raw('MIN(c.id) as first_card_id'),
                DB::raw("GROUP_CONCAT(DISTINCT c.id ORDER BY c.id ASC SEPARATOR ',') as card_ids")
            )
            ->where('u.id', $userId)
            ->where('q.type', 'essay')
            ->groupBy('t.id', 't.title', 's.name', 'u_name.name')
            ->get();

        foreach ($essay_data as $item) {
            $item->tong_so_the = DB::table('cards as c')
                ->join('questions as q', 'c.id', '=', 'q.card_id')
                ->where('q.topic_id', $item->topic_id)
                ->where('q.type', 'essay')
                ->distinct('c.id')
                ->count('c.id');
        }

        return view('user.homework_history.define_essay', compact('define_data', 'essay_data'));
    }


    public function saveHistory(Request $request)
    {
        $validated = $request->validate([
            'correct_count' => 'required|integer',
            'total_questions' => 'required|integer',
            'score' => 'required|numeric',
            'time_spent' => 'required|string',
            'test_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        History::create($validated);

        return response()->json([
            'message' => 'Lưu lịch sử thành công!',
            'data' => $validated
        ]);
    }

    public function multipleChoice()
    {
        $multiple_data = DB::table('histories as h')
            ->join('tests as t', 'h.test_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->select(
                't.id as id_de_thi',
                'h.correct_count as so_cau_dung',
                'h.total_questions as tong_so_cau',
                'h.score as diem',
                'h.time_spent as thoi_gian',
                't.content as ten_de_thi',
                'u.name as nguoi_tao'
            )
            ->where('h.user_id', Auth::guard('web')->user()->id)
            ->get();
        return view('user.homework_history.multiple', compact('multiple_data'));
    }
}

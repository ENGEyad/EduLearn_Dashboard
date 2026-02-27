<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Auth\GenericUser;

class BroadcastAuthController extends Controller
{
    public function auth(Request $request)
    {
        // Pusher/Reverb protocol fields
        $request->validate([
            'socket_id'    => 'required|string',
            'channel_name' => 'required|string',
        ]);

        // We accept identity via body OR headers (Flutter authorizer uses headers)
        $as = $request->input('as')
            ?? $request->header('X-Chat-As')
            ?? $request->header('x-chat-as');

        $teacherCode = $request->input('teacher_code')
            ?? $request->header('X-Teacher-Code')
            ?? $request->header('x-teacher-code');

        $academicId = $request->input('academic_id')
            ?? $request->header('X-Academic-Id')
            ?? $request->header('x-academic-id');

        if (!in_array($as, ['teacher', 'student'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid "as". Must be teacher or student.',
            ], 422);
        }

        $conversationId = $this->extractConversationId($request->channel_name);
        if (!$conversationId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid channel name.',
            ], 422);
        }

        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        if ($as === 'teacher') {
            if (!$teacherCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'teacher_code is required.',
                ], 422);
            }

            $teacher = Teacher::where('teacher_code', $teacherCode)->first();
            if (!$teacher || (int) $teacher->id !== (int) $conversation->teacher_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized teacher.',
                ], 403);
            }

            Auth::setUser(new GenericUser([
                'id'   => (int) $teacher->id,
                'role' => 'teacher',
            ]));
        } else {
            if (!$academicId) {
                return response()->json([
                    'success' => false,
                    'message' => 'academic_id is required.',
                ], 422);
            }

            $student = Student::where('academic_id', $academicId)->first();
            if (!$student || (int) $student->id !== (int) $conversation->student_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized student.',
                ], 403);
            }

            Auth::setUser(new GenericUser([
                'id'   => (int) $student->id,
                'role' => 'student',
            ]));
        }

        // This returns the auth signature for private channels
        return Broadcast::auth($request);
    }

    private function extractConversationId(string $channelName): ?int
    {
        // private-conversation.123 OR conversation.123
        if (preg_match('/^(private-)?conversation\.(\d+)$/', $channelName, $m)) {
            return (int) $m[2];
        }
        return null;
    }
}

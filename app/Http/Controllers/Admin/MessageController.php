<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.messages', [
            'rows'   => Message::orderByDesc('id')->get(),
            'edit'   => $request->query('edit') ? Message::find((int) $request->query('edit')) : null,
            'total'  => Message::count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => ['nullable', 'string', 'max:150'],
            'body'       => ['required', 'string', 'max:2000'],
            'target'     => ['required', 'in:all,class'],
            'class_no'   => ['nullable', 'integer', 'between:1,12'],
            'section'    => ['nullable', 'string', 'max:8'],
            'priority'   => ['nullable', 'in:Normal,Important'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        Message::create([
            'title'      => (string) ($data['title'] ?? ''),
            'body'       => $data['body'],
            'class_no'   => $data['target'] === 'class' ? (int) $data['class_no'] : null,
            'section'    => $data['target'] === 'class' ? (string) ($data['section'] ?? '') : '',
            'priority'   => $data['priority'] ?? 'Normal',
            'expires_at' => $data['expires_at'] ?? null,
            'admin_id'   => $request->session()->get('admin_id'),
        ]);

        return back()->with('success', 'Message bhej diya — students ke portal par foran dikh jayega.' . (($data['expires_at'] ?? null) ? ' Expiry: ' . $data['expires_at'] . ' ke baad khud hide ho jayega.' : ''));
    }

    public function update(Request $request, int $id)
    {
        $message = Message::find($id);
        if (!$message) {
            return back()->with('error', 'Message nahi mila.');
        }

        $data = $request->validate([
            'title'      => ['nullable', 'string', 'max:150'],
            'body'       => ['required', 'string', 'max:2000'],
            'target'     => ['required', 'in:all,class'],
            'class_no'   => ['nullable', 'integer', 'between:1,12'],
            'section'    => ['nullable', 'string', 'max:8'],
            'priority'   => ['nullable', 'in:Normal,Important'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $message->update([
            'title'      => (string) ($data['title'] ?? ''),
            'body'       => $data['body'],
            'class_no'   => $data['target'] === 'class' ? (int) $data['class_no'] : null,
            'section'    => $data['target'] === 'class' ? (string) ($data['section'] ?? '') : '',
            'priority'   => $data['priority'] ?? 'Normal',
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('admin.messages.index')->with('success', 'Message update ho gaya.');
    }

    public function destroy(int $id)
    {
        $message = Message::find($id);
        if (!$message) {
            return back()->with('error', 'Message nahi mila.');
        }

        $message->delete();

        return back()->with('success', 'Message delete ho gaya.');
    }
}

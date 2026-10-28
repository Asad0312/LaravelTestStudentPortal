<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $stream = $request->input('stream', 'school');
        $classLevel = $request->input('class_level');
        $section = $request->input('section');

        $books = Book::query()
            ->when($stream, fn($q) => $q->where('stream', $stream))
            ->when($classLevel && $stream === 'school', fn($q) => $q->where('class_level', $classLevel))
            ->when($section && $stream === 'madarsa', fn($q) => $q->where('madarsa_section', $section))
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        return view('admin.books', [
            'books' => $books,
            'stream' => $stream,
            'classLevel' => $classLevel,
            'section' => $section,
            'templates' => Book::templates(),
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'stream' => ['required', 'in:school,madarsa'],
            'class_level' => ['nullable', 'integer', 'between:1,8'],
            'madarsa_section' => ['nullable', 'string', 'max:1'],
            'items' => ['array'],
        ]);

        $stream = $data['stream'];
        Book::query()
            ->when($stream === 'school', fn($q) => $q->where('stream', 'school')->where('class_level', $data['class_level']))
            ->when($stream === 'madarsa', fn($q) => $q->where('stream', 'madarsa')->where('madarsa_section', strtoupper($data['madarsa_section'] ?? 'A')))
            ->delete();

        if (!empty($data['items'])) {
            foreach ($data['items'] as $i => $item) {
                if (empty($item['title'])) continue;
                Book::create([
                    'stream' => $stream,
                    'class_level' => $stream === 'school' ? ($data['class_level'] ?? null) : null,
                    'madarsa_section' => $stream === 'madarsa' ? strtoupper($data['madarsa_section'] ?? 'A') : null,
                    'title' => $item['title'],
                    'price' => $item['price'] ?? 0,
                    'sort_order' => $i,
                ]);
            }
        }

        return back()->with('success', 'Books saved.');
    }

    public function templates()
    {
        return response()->json(Book::templates());
    }
}

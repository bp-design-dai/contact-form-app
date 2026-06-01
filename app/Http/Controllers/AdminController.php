<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(IndexContactRequest $request): View
    {
        $query = Contact::with(['category', 'tags'])->latest();

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('gender') && $request->input('gender') !== '0') {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $contacts = $query->paginate(7)->withQueryString();

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    public function show(Contact $contact): View
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}

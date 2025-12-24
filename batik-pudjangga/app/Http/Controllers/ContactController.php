<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('home.contact');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        Contact::create($request->only('name', 'email', 'message'));

        return back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}

<?php

namespace App\Http\Controllers;


class ContactListController extends Controller
{

    public function index()
    {
        return view('contact-list');
    }
}
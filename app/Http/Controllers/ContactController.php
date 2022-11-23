<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Traits\ResponseTrait;


class ContactController extends Controller
{
    use ResponseTrait;
    public function contactForm(ContactRequest $request)
    {
        $data = $request->all();
        $data = Contact::create($data);
        return $this->sendSuccessResponse('Contact add successfully', $data);
    }
}

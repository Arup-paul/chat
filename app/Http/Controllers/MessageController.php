<?php

namespace App\Http\Controllers;

use App\Events\MessageSend;
use App\Message;
use App\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function userList()
    {
        $users = User::latest()->where('id', '!=', auth()->user()->id)->get();
        if (\Request::ajax()) {
            return response()->json($users, 200);
        }
        return abort(404);

    }

    public function userMessage($id = null)
    {
        if (!\Request::ajax()) {
            return abort(404);
        }
        $user = User::findOrFail($id);

        $messages = $this->messageByUserId($id);
        return response()->json([
            'messages' => $messages,
            'user' => $user,
        ]);

    }

    public function sendMessage(Request $request)
    {
        if (!$request->ajax()) {
            return abort(404);
        }

        $messages = Message::create([
            'message' => $request->message,
            'from' => auth()->user()->id,
            'to' => $request->user_id,
            'type' => 0,
        ]);
        $messages = Message::create([
            'message' => $request->message,
            'from' => auth()->user()->id,
            'to' => $request->user_id,
            'type' => 1,
        ]);
         broadcast( new MessageSend($messages));
        return response()->json($messages, 201);
    }

    public function deleteSingleMessage($id = null)
    {
        if (!\Request::ajax()) {
            return abort(404);
        }
        Message::findOrFail($id)->delete();
        return response()->json('deleted', 200);
    }

    public function deleteAllMessage($id = null)
    {
        if (!\Request::ajax()) {
            return abort(404);
        }
        $messages = $this->messageByUserId($id);
        foreach($messages as $message){
            Message::findOrFail($message->id)->delete();
        }
        return response()->json('alldeleted',200);

    }

    public function messageByUserId($id)
    {
        $messages = Message::where(function ($q) use ($id) {
            $q->where('from', auth()->user()->id);
            $q->where('to', $id);
            $q->where('type', 0);
        })->orWhere(function ($q) use ($id) {
            $q->where('from', $id);
            $q->where('to', auth()->user()->id);
            $q->where('type', 1);

        })->with('user')->get();
        return $messages;

    }
}

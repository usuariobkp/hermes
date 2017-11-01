<?php

namespace App\Http\Controllers;

use App\AppUser;
use App\Rating;
use App\Message;
use App\Jobs\SendMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use TCG\Voyager\Facades\Voyager;
use Yajra\Datatables\Datatables;
use Validator;

class MessagesController extends DataTablesController
{
    /**
    * Process datatables ajax request.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function messagesAPI(Request $request)
    {
        if (Auth::user()->hasPermission('browse_messages')) {
            $params = $request->query()['columns'];
            $model = Rating::with('latestMessage', 'app', 'appuser', 'platform')->where('has_message', true)->get();

            $datatables = Datatables::of($model)
                ->filter(function ($query) use($params) {
                    $query = $this->filterQuery($query, $params);
                }, true);

            return $datatables->make(true);
        }

        return Response::json([], 401);
    }

    // POST BRE(A)D
    public function create(Request $request)
    {
        Voyager::canOrFail('add_messages');

        $created = 201;
        $badRequest = 400;
        $unprocessableEntity = 422;
        $internalServerError = 500;

        $ratingId = $request->input('rating');
        $messageText = $request->input('message');

        $inputData = ['ratingId' => $ratingId, 'message' => $messageText];
        $rules = ['ratingId' => 'required|integer|min:1', 'message' => 'required|string|max:1500'];
        $validation = Validator::make($inputData, $rules);

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->messages(), 'status' => $badRequest]);
        }

        $rating = Rating::find($ratingId);

        if ($rating === null) {
            return response()->json(['errors' => 'Invalid rating.', 'status' => $unprocessableEntity]);
        }

        $user = AppUser::find($rating->appuser_id);

        if ($user === null) {
            return response()->json(['errors' => 'Invalid user.', 'status' => $unprocessableEntity]);
        }

        $replyTo = Message::where([['rating_id', '=', $rating->id], ['direction', '=', 'in']])
            ->latest()
            ->first();

        if ($replyTo === null) {
            return response()->json(['errors' => 'Message not found.', 'status' => $internalServerError]);
        }

        if (isset($user->email)) {
            $subject = env('MAIL_SUBJECT', 'Gracias por tus comentarios');
            $message = new Message;

            $message->message = $messageText;
            $message->direction = 'out';
            $message->status = 0;
            $message->rating_id = $rating->id;
            $message->createdBy()->associate(Auth::user());

            if (!$message->save()) {
                return response()->json(['errors' => 'Could not save new message.', 'status' => $internalServerError]);
            }

            SendMessage::dispatch($subject, $message, $replyTo, $user);
        }
        else {
            return response()->json(['errors' => 'User has no email.', 'status' => $unprocessableEntity]);
        }

        return response()->json(['status' => $created, 'message' => $message]);
    }
}
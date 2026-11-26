<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $me = Auth::id();
        $ids = Message::where('sender_id', $me)->orWhere('receiver_id', $me)
            ->orderByDesc('created_at')->get()
            ->map(fn ($m) => $m->sender_id === $me ? $m->receiver_id : $m->sender_id)
            ->unique()->values();
        $partners = User::whereIn('id', $ids)->get()->keyBy('id');
        $threads = $ids->map(function ($id) use ($partners, $me) {
            $last = Message::where(function ($q) use ($id, $me) {
                $q->where('sender_id', $me)->where('receiver_id', $id);
            })->orWhere(function ($q) use ($id, $me) {
                $q->where('sender_id', $id)->where('receiver_id', $me);
            })->latest()->first();
            $unread = Message::where('sender_id', $id)->where('receiver_id', $me)->whereNull('read_at')->count();
            return ['user' => $partners->get($id), 'last' => $last, 'unread' => $unread];
        })->filter(fn ($t) => $t['user'] !== null);
        return view('messages.index', compact('threads'));
    }

    public function show(User $user): View
    {
        $me = Auth::user();
        abort_if($me->id === $user->id, 403);
        Message::where('sender_id', $user->id)->where('receiver_id', $me->id)
            ->whereNull('read_at')->update(['read_at' => now()]);
        $messages = Message::where(function ($q) use ($user, $me) {
            $q->where('sender_id', $me->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user, $me) {
            $q->where('sender_id', $user->id)->where('receiver_id', $me->id);
        })->orderBy('created_at')->paginate(50);
        return view('messages.show', compact('user', 'messages'));
    }

    /**
     * Live-update endpoint for an open thread: returns messages newer
     * than the given id, newest last. The chat page polls this every
     * few seconds so new messages appear without a page reload.
     */
    public function updates(Request $request, User $user): JsonResponse
    {
        $me = Auth::user();
        abort_if($me->id === $user->id, 403);

        $after = (int) $request->query('after', 0);

        Message::where('sender_id', $user->id)->where('receiver_id', $me->id)
            ->whereNull('read_at')->update(['read_at' => now()]);

        $messages = Message::where(function ($q) use ($user, $me) {
            $q->where('sender_id', $me->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user, $me) {
            $q->where('sender_id', $user->id)->where('receiver_id', $me->id);
        })->where('id', '>', $after)->orderBy('created_at')->limit(50)->get();

        $unread = Message::where('receiver_id', $me->id)->whereNull('read_at')->count();

        return response()->json([
            'messages' => $messages->map(fn (Message $m) => [
                'id' => $m->id,
                'body' => $m->body,
                'mine' => $m->sender_id === $me->id,
                'time' => $m->created_at->diffForHumans(),
            ]),
            'unread' => $unread,
        ]);
    }

    /**
     * Recent history for the floating chat dock: the latest 30
     * messages, oldest first. Also marks incoming messages read.
     */
    public function history(User $user): JsonResponse
    {
        $me = Auth::user();
        abort_if($me->id === $user->id, 403);

        Message::where('sender_id', $user->id)->where('receiver_id', $me->id)
            ->whereNull('read_at')->update(['read_at' => now()]);

        $messages = Message::where(function ($q) use ($user, $me) {
            $q->where('sender_id', $me->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user, $me) {
            $q->where('sender_id', $user->id)->where('receiver_id', $me->id);
        })->orderByDesc('created_at')->orderByDesc('id')->limit(30)->get()->reverse()->values();

        return response()->json([
            'messages' => $messages->map(fn (Message $m) => [
                'id' => $m->id,
                'body' => $m->body,
                'mine' => $m->sender_id === $me->id,
                'time' => $m->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $me = Auth::user();
        abort_if($me->id === $user->id, 403);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $message = Message::create(['sender_id' => $me->id, 'receiver_id' => $user->id, 'body' => $data['body']]);
        $user->notify(new UserNotification(
            $me->name . ' sent you a message.', route('messages.show', $me), 'message'));
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => [
                'id' => $message->id,
                'body' => $message->body,
                'mine' => true,
                'time' => $message->created_at->diffForHumans(),
            ]], 201);
        }
        return back();
    }
}

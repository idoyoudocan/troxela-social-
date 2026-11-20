@extends('layouts.app')
@section('title', 'Chat with ' . $user->name)
@section('content')
<div class="card">
  <h1 style="display:flex;gap:10px;align-items:center"><img class="avatar" src="{{ $user->avatarUrl() }}" alt=""> {{ $user->name }}</h1>
  <div id="thread" data-last-id="{{ $messages->last()?->id ?? 0 }}">
    @forelse($messages as $m)
      <div class="msg {{ $m->sender_id === auth()->id() ? 'me' : 'them' }}" data-msg-id="{{ $m->id }}">
        {{ $m->body }}<br><span class="small" style="opacity:.7">{{ $m->created_at->diffForHumans() }}</span>
      </div>
    @empty
      <p class="muted">No messages yet — say hello!</p>
    @endforelse
    {{ $messages->links() }}
  </div>
  <form method="POST" action="{{ route('messages.store', $user) }}" style="margin-top:14px">
    @csrf
    <textarea name="body" rows="2" placeholder="Write a message..." required maxlength="2000"></textarea>
    <div style="margin-top:8px"><button type="submit">Send</button></div>
  </form>
</div>
<script>
(function(){
  var thread = document.getElementById('thread');
  if (!thread) return;
  var lastId = parseInt(thread.dataset.lastId || '0', 10);
  var url = "{{ route('messages.updates', $user) }}";
  function nearBottom(){
    return (window.innerHeight + window.scrollY) >= (document.body.scrollHeight - 120);
  }
  function poll(){
    fetch(url + '?after=' + lastId, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(function(res){ if (!res.ok) return null; return res.json(); })
      .then(function(data){
        if (!data) return;
        var stick = nearBottom();
        (data.messages || []).forEach(function(m){
          if (thread.querySelector('[data-msg-id="' + m.id + '"]')) return;
          var div = document.createElement('div');
          div.className = 'msg ' + (m.mine ? 'me' : 'them');
          div.setAttribute('data-msg-id', m.id);
          div.appendChild(document.createTextNode(m.body));
          div.appendChild(document.createElement('br'));
          var span = document.createElement('span');
          span.className = 'small';
          span.style.opacity = .7;
          span.textContent = m.time;
          div.appendChild(span);
          thread.appendChild(div);
          if (m.id > lastId) lastId = m.id;
        });
        thread.dataset.lastId = lastId;
        if (stick) window.scrollTo({top: document.body.scrollHeight});
        var badge = document.querySelector('.msg-badge');
        if (badge) {
          if (data.unread > 0) { badge.textContent = data.unread; badge.style.display = ''; }
          else { badge.style.display = 'none'; }
        }
      })
      .catch(function(){ /* transient error — next poll retries */ });
  }
  setInterval(poll, 3000);
})();
</script>
@endsection

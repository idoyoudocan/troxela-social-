@auth
{{-- Facebook-style floating chat dock: chat windows open at the
     bottom-right of the page instead of navigating away. --}}
<div id="chat-dock" data-me-id="{{ auth()->id() }}" aria-label="Chats"></div>
<script>
(function(){
  var dock = document.getElementById('chat-dock');
  if (!dock) return;
  var meId = parseInt(dock.dataset.meId || '0', 10);
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var MAX_WINDOWS = 3;
  var STORAGE_KEY = 'troxela-chat-windows';
  var SOUND_KEY = 'troxela-message-sound';
  var soundOn = (function(){ try { return localStorage.getItem(SOUND_KEY) !== 'off'; } catch(_){ return true; } })();
  var audioCtx = null;

  // Soft two-note pop when a message arrives. The AudioContext is
  // created/resumed on the first user gesture, so later polls may play.
  function ensureAudio(){
    if (!audioCtx) { try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch(_){} }
    if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
  }
  ['pointerdown', 'keydown'].forEach(function(ev){
    document.addEventListener(ev, ensureAudio, {once: true});
  });
  function playMessageSound(){
    if (!soundOn) return;
    ensureAudio();
    if (!audioCtx) return;
    try {
      var t = audioCtx.currentTime;
      [[880, 0], [1174.66, 0.09]].forEach(function(note){
        var osc = audioCtx.createOscillator();
        var gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.value = note[0];
        gain.gain.setValueAtTime(0.0001, t + note[1]);
        gain.gain.exponentialRampToValueAtTime(0.06, t + note[1] + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + note[1] + 0.16);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(t + note[1]);
        osc.stop(t + note[1] + 0.18);
      });
    } catch(_){}
  }
  function setSoundOn(on){
    soundOn = on;
    try { localStorage.setItem(SOUND_KEY, on ? 'on' : 'off'); } catch(_){}
    windows.forEach(function(w){ w.soundBtn.textContent = on ? '🔊' : '🔇'; });
    if (on) playMessageSound();
  }
  var windows = new Map(); // userId -> window state
  var isMobile = function(){ return window.innerWidth <= 640; };

  function el(tag, cls, text){
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text != null) e.textContent = text;
    return e;
  }

  function saveState(){
    var arr = [];
    windows.forEach(function(w){
      arr.push({id: w.user.id, name: w.user.name, avatar: w.user.avatar, username: w.user.username, min: w.minimized});
    });
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); } catch(_){}
  }
  function loadState(){
    try {
      var arr = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
      return Array.isArray(arr) ? arr : [];
    } catch(_){ return []; }
  }

  function scrollBottom(w){ w.body.scrollTop = w.body.scrollHeight; }

  function appendMessage(w, m, stick){
    if (w.body.querySelector('[data-msg-id="' + m.id + '"]')) return false;
    var empty = w.body.querySelector('.chat-empty');
    if (empty) empty.remove();
    var div = el('div', 'msg ' + (m.mine ? 'me' : 'them'));
    div.setAttribute('data-msg-id', m.id);
    div.appendChild(document.createTextNode(m.body));
    div.appendChild(document.createElement('br'));
    var span = el('span', 'small');
    span.style.opacity = .7;
    span.textContent = m.time;
    div.appendChild(span);
    w.body.appendChild(div);
    if (m.id > w.lastId) w.lastId = m.id;
    if (stick) scrollBottom(w);
    return true;
  }

  function setMinimized(w, min){
    w.minimized = min;
    w.el.classList.toggle('minimized', min);
    if (!min) { w.unread = 0; updateBadge(w); scrollBottom(w); w.input.focus(); }
    saveState();
  }
  function updateBadge(w){
    if (w.unread > 0) { w.badge.textContent = w.unread; w.badge.style.display = ''; }
    else { w.badge.style.display = 'none'; }
  }

  function closeWindow(w){
    w.el.remove();
    windows.delete(w.user.id);
    saveState();
  }

  function loadHistory(w){
    fetch('/messages/' + encodeURIComponent(w.user.username) + '/history', {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(function(res){ if (!res.ok) return null; return res.json(); })
      .then(function(data){
        if (!data) return;
        (data.messages || []).forEach(function(m){ appendMessage(w, m, false); });
        scrollBottom(w);
      })
      .catch(function(){ /* transient error — polling still picks messages up */ });
  }

  function buildWindow(user){
    var win = el('div', 'chat-window');
    win.setAttribute('data-user-id', user.id);

    var head = el('div', 'chat-win-head');
    var avatar = el('img');
    avatar.src = user.avatar;
    avatar.alt = '';
    var name = el('span', 'chat-win-name', user.name);
    var badge = el('span', 'chat-win-badge');
    badge.style.display = 'none';
    var soundBtn = el('button', 'chat-win-btn', soundOn ? '🔊' : '🔇');
    soundBtn.type = 'button';
    soundBtn.title = 'Sound on/off';
    soundBtn.setAttribute('aria-label', 'Toggle message sound');
    var minBtn = el('button', 'chat-win-btn chat-win-min', '–');
    minBtn.type = 'button';
    minBtn.title = 'Minimize';
    minBtn.setAttribute('aria-label', 'Minimize chat');
    var closeBtn = el('button', 'chat-win-btn', '×');
    closeBtn.type = 'button';
    closeBtn.title = 'Close';
    closeBtn.setAttribute('aria-label', 'Close chat');
    head.appendChild(avatar);
    head.appendChild(name);
    head.appendChild(badge);
    head.appendChild(soundBtn);
    head.appendChild(minBtn);
    head.appendChild(closeBtn);

    var body = el('div', 'chat-msgs');
    body.appendChild(el('p', 'muted chat-empty', 'No messages yet — say hello!'));

    var form = el('form', 'chat-win-form');
    form.setAttribute('autocomplete', 'off');
    var input = el('input');
    input.type = 'text';
    input.placeholder = 'Write a message...';
    input.maxLength = 2000;
    input.setAttribute('aria-label', 'Write a message');
    var send = el('button', 'btn btn-sm');
    send.type = 'submit';
    send.textContent = 'Send';
    form.appendChild(input);
    form.appendChild(send);

    win.appendChild(head);
    win.appendChild(body);
    win.appendChild(form);

    var w = {user: user, el: win, body: body, input: input, form: form,
             badge: badge, soundBtn: soundBtn, lastId: 0, minimized: false, unread: 0};

    head.addEventListener('click', function(e){
      if (e.target === closeBtn) { closeWindow(w); return; }
      if (e.target === soundBtn) { setSoundOn(!soundOn); return; }
      if (e.target === minBtn) { setMinimized(w, !w.minimized); return; }
      setMinimized(w, !w.minimized);
    });
    form.addEventListener('submit', function(e){
      e.preventDefault();
      var text = input.value.trim();
      if (!text) return;
      input.value = '';
      var fd = new FormData();
      fd.append('body', text);
      fetch('/messages/' + encodeURIComponent(user.username), {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf},
        body: fd
      })
      .then(function(res){ if (!res.ok) return null; return res.json(); })
      .then(function(data){
        if (data && data.message) { appendMessage(w, data.message, true); }
        else { input.value = text; }
      })
      .catch(function(){ input.value = text; });
    });

    return w;
  }

  function openChat(userId, name, avatar, username){
    userId = parseInt(userId, 10);
    if (!userId || userId === meId) return;
    if (windows.has(userId)) {
      var ex = windows.get(userId);
      setMinimized(ex, false);
      return;
    }
    if (isMobile()) {
      windows.forEach(function(w){ closeWindow(w); });
    } else if (windows.size >= MAX_WINDOWS) {
      var first = windows.values().next().value;
      if (first) closeWindow(first);
    }
    var w = buildWindow({id: userId, name: name, avatar: avatar, username: username});
    windows.set(userId, w);
    dock.appendChild(w.el);
    loadHistory(w);
    saveState();
    if (!w.minimized) w.input.focus();
  }
  window.openChat = openChat;

  // Links marked .chat-open open a floating window instead of
  // navigating to the full chat page (which still works as a fallback).
  document.addEventListener('click', function(e){
    var a = e.target.closest('.chat-open');
    if (!a) return;
    e.preventDefault();
    openChat(a.dataset.userId, a.dataset.userName, a.dataset.userAvatar, a.dataset.userUsername);
  });

  // Poll every open window for new messages, like the full chat page.
  setInterval(function(){
    if (document.hidden) return;
    windows.forEach(function(w){
      fetch('/messages/' + encodeURIComponent(w.user.username) + '/updates?after=' + w.lastId,
        {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(function(res){ if (!res.ok) return null; return res.json(); })
        .then(function(data){
          if (!data) return;
          var stick = !w.minimized;
          (data.messages || []).forEach(function(m){
            var added = appendMessage(w, m, stick);
            if (added && !m.mine) {
              playMessageSound();
              if (w.minimized) { w.unread++; updateBadge(w); }
            }
          });
          if (stick) scrollBottom(w);
          var navBadge = document.querySelector('.msg-badge');
          if (navBadge) {
            if (data.unread > 0) { navBadge.textContent = data.unread; navBadge.style.display = ''; }
            else { navBadge.style.display = 'none'; }
          }
        })
        .catch(function(){ /* transient error — next poll retries */ });
    });
  }, 3000);

  // Restore windows that were open when the user left the page.
  loadState().forEach(function(saved){
    if (isMobile() && windows.size >= 1) return;
    if (!isMobile() && windows.size >= MAX_WINDOWS) return;
    var w = buildWindow({id: saved.id, name: saved.name, avatar: saved.avatar, username: saved.username});
    windows.set(saved.id, w);
    dock.appendChild(w.el);
    loadHistory(w);
    if (saved.min) setMinimized(w, true);
  });
})();
</script>
@endauth

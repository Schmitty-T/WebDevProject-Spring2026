var uphone = document.getElementById('uphone');
var utopic = document.getElementById('utopic');
var umsg = document.getElementById('umsg');
var ccount = document.getElementById('ccount');

// keep dark mode consistent across pages
if (localStorage.getItem('theme') === 'dark') {
  document.body.classList.add('dark');
}

document.getElementById('themeToggle').addEventListener('click', function() {
  document.body.classList.toggle('dark');
  localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
});

umsg.addEventListener('input', function() {
  var l = umsg.value.length;
  ccount.textContent = l + ' / 1000';
  ccount.classList.toggle('warn', l > 900);
});

uphone.addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9+\-\s]/g, '');
});

function mark(el, errId, ok) {
  el.classList.toggle('ok', ok);
  el.classList.toggle('bad', !ok)
  document.getElementById(errId).classList.toggle('show', !ok);
}

function phoneOk(v) {
  return v == '' || /^[0-9+\-\s]{7,15}$/.test(v);
}

uphone.addEventListener('blur', function() {
  var v = uphone.value.trim();
  if (v == '') {
    uphone.classList.remove('ok', 'bad');
    return;
  }
  mark(uphone, 'uphone-err', phoneOk(v));
});

utopic.addEventListener('change', function() {
  mark(utopic, 'utopic-err', utopic.value != '');
});

umsg.addEventListener('blur', function() {
  mark(umsg, 'umsg-err', umsg.value.trim().length >= 10)
});

document.getElementById('cform').addEventListener('submit', function(e) {
  var ph = uphone.value.trim();
  var top = utopic.value;
  var m = umsg.value.trim();

  mark(utopic, 'utopic-err', top != '');
  mark(umsg, 'umsg-err', m.length >= 10);
  if (ph != '') mark(uphone, 'uphone-err', phoneOk(ph));

  if (!phoneOk(ph) || !top || m.length < 10) {
    e.preventDefault();
    return;
  }
});

var uname = document.getElementById('uname');
var uemail = document.getElementById('uemail');
var uphone = document.getElementById('uphone');
var utopic = document.getElementById('utopic');
var umsg = document.getElementById('umsg');
var ccount = document.getElementById('ccount');

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
  el.classList.toggle('bad', !ok);
  document.getElementById(errId).classList.toggle('show', !ok);
}

function emailOk(v) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
}

function phoneOk(v) {
  return v == '' || /^[0-9+\-\s]{7,15}$/.test(v);
}

uname.addEventListener('blur', function() {
  mark(uname, 'uname-err', uname.value.trim() != '');
});

uemail.addEventListener('blur', function() {
  mark(uemail, 'uemail-err', emailOk(uemail.value.trim()));
});

uphone.addEventListener('blur', function() {
  var v = uphone.value.trim();
  if (v == '') {
    uphone.classList.remove('ok', 'bad');
    document.getElementById('uphone-err').classList.remove('show');
    return;
  }
  mark(uphone, 'uphone-err', phoneOk(v));
});

utopic.addEventListener('change', function() {
  mark(utopic, 'utopic-err', utopic.value != '');
});

umsg.addEventListener('blur', function() {
  mark(umsg, 'umsg-err', umsg.value.trim().length >= 10);
});

document.getElementById('cform').addEventListener('submit', function(e) {
  e.preventDefault();

  var n = uname.value.trim();
  var em = uemail.value.trim();
  var ph = uphone.value.trim();
  var top = utopic.value;
  var m = umsg.value.trim();

  mark(uname, 'uname-err', n != '');
  mark(uemail, 'uemail-err', emailOk(em));
  if (ph != '') mark(uphone, 'uphone-err', phoneOk(ph));
  mark(utopic, 'utopic-err', top != '');
  mark(umsg, 'umsg-err', m.length >= 10);

  if (!n || !emailOk(em) || !phoneOk(ph) || !top || m.length < 10) return;

  this.reset();
  ccount.textContent = '0 / 1000';
  [uname, uemail, uphone, utopic, umsg].forEach(function(el) {
    el.classList.remove('ok', 'bad');
  });

  var t = document.getElementById('toast');
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 3500);
});

document.getElementById('themeToggle').addEventListener('click', function() {
  document.body.classList.toggle('dark');
  this.textContent = document.body.classList.contains('dark') ? 'Light Mode' : 'Dark Mode';
});

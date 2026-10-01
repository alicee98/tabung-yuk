"""End-to-end smoke test; creates isolated demo data in its own cookie jar.
Run: python3 tests/smoke.py http://localhost:8000
Use TABUNG_SESSION_DRIVER=cookie and TABUNG_APP_KEY on the PHP server to test cookies locally.
"""
import datetime
import html
import http.cookiejar
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

base = sys.argv[1].rstrip('/') + '/' if len(sys.argv)>1 else 'http://localhost:8000/'
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

def request(path, data=None):
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    with client.open(urllib.parse.urljoin(base, path), body, timeout=30) as response:
        text = response.read().decode()
        assert not re.search(r'(Warning|Fatal error|Parse error):', text), text
        return response.url, text

def hidden(text):
    return {name:html.unescape(value) for name,value in re.findall(r'<input type="hidden" name="(.*?)" value="(.*?)">',text)}

url,text = request('dashboard.php')
assert url.endswith('/login.php')
url,text = request('login.php', {**hidden(text),'username':'farisah01','password':'tabung123'})
assert url.endswith('/beranda.php')
for name in ['beranda','jadwal','deposit','dashboard','riwayat']:
    request(name+'.php')
_,text = request('jadwal.php')
today = (datetime.datetime.now(datetime.timezone.utc)+datetime.timedelta(hours=7)).date().isoformat()
form = {**hidden(text),'name':'Uji deployment','target':'500000','installment':'5000','frequency':'Harian','start':today}
_,text = request('jadwal.php', form)
assert 'Jadwal berhasil dibuat' in text
request('jadwal.php', form)
_,text = request('deposit.php')
goals = re.findall(r'<option value="([a-f0-9]{16})"',text)
assert len(goals)==1
deposit_form = {**hidden(text),'goal_id':goals[0],'amount':'5000','note':'Uji otomatis'}
url,text = request('deposit.php',deposit_form)
assert '/pembayaran.php?id=' in url
payment=url
number=re.search(r'<div class="payment-total">.*?<strong>(Rp[\d.]+)</strong>',text,re.S).group(1)
assert 5001 <= int(number[2:].replace('.','')) <= 5999
_,text=request(payment)
assert number in text
_,text=request(payment,{**hidden(text),'action':'confirm'})
assert 'Berhasil (simulasi)' in text
request(payment,{**hidden(text),'action':'confirm'})
_,text=request('dashboard.php')
assert '<strong>'+number+'</strong>' in text
_,text=request('riwayat.php?status=success')
assert text.count('<tr><td>')==1
try:
    request('deposit.php',{'amount':'5000'})
    raise AssertionError('POST without CSRF should fail')
except urllib.error.HTTPError as error:
    assert error.code==403
_,text=request('beranda.php')
request('logout.php',hidden(text))
url,_=request('dashboard.php')
assert url.endswith('/login.php')
print('PASS: login, routes, schedule, duplicate form, deposit, refresh, single credit, history, CSRF, logout')

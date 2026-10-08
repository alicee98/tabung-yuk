"""HTTP regression for multi-item schedules. Run against an isolated local server.
python3 tests/multi_items.py http://localhost:8000
"""
import datetime
import html
import http.cookiejar
import re
import sys
import urllib.parse
import urllib.request

base = sys.argv[1].rstrip('/') + '/'
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def request(path, data=None):
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    with client.open(urllib.parse.urljoin(base, path), body, timeout=30) as response:
        text = response.read().decode()
        assert not re.search(r'(Warning|Fatal error|Parse error):', text)
        return response.url, text

def hidden(text):
    return {name: html.unescape(value) for name, value in re.findall(r'<input type="hidden" name="(.*?)" value="(.*?)">', text)}

_, text = request('login.php')
request('login.php', {**hidden(text), 'username': 'farisah01', 'password': 'tabung123'})
_, text = request('jadwal.php')
today = (datetime.datetime.now(datetime.timezone.utc) + datetime.timedelta(hours=7)).date().isoformat()
form = {**hidden(text), 'name': 'Perlengkapan kuliah', 'target': '1', 'installment': '100000', 'frequency': 'Harian', 'start': today,
        'items[0][name]': 'Laptop', 'items[0][amount]': '4000000', 'items[1][name]': 'Tas <kuliah>', 'items[1][amount]': '1000000'}
_, text = request('jadwal.php', form)
assert 'Jadwal berhasil dibuat' in text
assert 'Rp5.000.000' in text and '50 hari lagi' in text
assert 'Tas &lt;kuliah&gt;' in text
_, text = request('dashboard.php')
assert 'Laptop' in text and '50 hari lagi' in text
_, text = request('deposit.php')
goal = re.search(r'<option value="([a-f0-9]{16})"', text).group(1)
payment, text = request('deposit.php', {**hidden(text), 'goal_id': goal, 'amount': '3200000', 'note': 'Uji saldo'})
request(payment, {**hidden(text), 'action': 'confirm'})
_, text = request('dashboard.php')
assert '18 hari lagi' in text and '18 setoran harian lagi' in text
_, text = request('jadwal.php')
assert '18 hari lagi' in text
invalid = {**form, **hidden(text), 'name': 'Tidak lengkap', 'items[1][amount]': ''}
_, text = request('jadwal.php', invalid)
assert 'Isi nama setiap barang' in text
_, text = request('deposit.php')
assert len(re.findall(r'<option value="([a-f0-9]{16})"', text)) == 1
_, text = request('beranda.php')
request('logout.php', hidden(text))
print('PASS: server-calculated item totals, escaped names, shared balance, live forecast after deposit, invalid rows rejected')

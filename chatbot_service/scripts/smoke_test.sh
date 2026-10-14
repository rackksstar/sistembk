#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${CHATBOT_BASE_URL:-http://127.0.0.1:8001}"
TOKEN="${CHATBOT_API_TOKEN:-}"

hdr=(-H "Content-Type: application/json" -H "Accept: application/json")
if [[ -n "$TOKEN" ]]; then
  hdr+=(-H "X-Chatbot-Token: $TOKEN")
fi

echo "== health =="
curl -fsS "$BASE_URL/health" | tee /tmp/chatbot_health.json
echo

python3 - <<'PY'
import json
data=json.load(open("/tmp/chatbot_health.json"))
assert data.get("index_ready") is True, data
assert int(data.get("chunks") or 0) > 0, data
print("health OK — chunks:", data["chunks"], "pcr_chunks:", data.get("pcr_chunks"))
PY

chat() {
  local msg="$1"
  python3 - "$BASE_URL" "$TOKEN" "$msg" <<'PY'
import json, os, sys, urllib.request

base, token, message = sys.argv[1], sys.argv[2], sys.argv[3]
req = urllib.request.Request(
    base.rstrip("/") + "/chat",
    data=json.dumps({"studentId": "smoke-1", "message": message}).encode(),
    headers={
        "Content-Type": "application/json",
        "Accept": "application/json",
        **({"X-Chatbot-Token": token} if token else {}),
    },
    method="POST",
)
with urllib.request.urlopen(req, timeout=30) as resp:
    body = json.load(resp)
print(body.get("reply", "")[:500])
assert body.get("reply"), body
PY
  echo
}

echo "== sapaan halo =="
chat "halo"
python3 - <<'PY'
import json, urllib.request
req = urllib.request.Request(
    "http://127.0.0.1:8001/chat",
    data=json.dumps({"studentId": "smoke-halo", "message": "halo"}).encode(),
    headers={"Content-Type": "application/json"},
    method="POST",
)
with urllib.request.urlopen(req, timeout=30) as resp:
    reply = json.load(resp)["reply"].lower()
assert "senang" in reply or "halo" in reply
assert "bab 11" not in reply and "daftar isi" not in reply
print("sapaan OK")
PY

echo "== PCR jalur =="
chat "Jalur masuk PCR apa saja?"

echo "== follow-up biaya =="
chat "berapa biayanya?"

echo "== Minat Bakat menu =="
chat "Apa itu Minat Bakat Kuliah di sistem BK?"

echo "== out of scope =="
chat "beri saya kunci jawaban ujian matematika"

echo "SMOKE OK"

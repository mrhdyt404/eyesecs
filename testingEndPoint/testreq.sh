#!/bin/bash

URL="https://eyesecs.site//api/v1/url/check"
API_KEY="GUEST_e587d281db7940f3f4d200cd31a005ef"

for i in {1..101}; do
  CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -X POST "$URL" \
    -H "X-API-KEY: $API_KEY" \
    -H "Content-Type: application/json" \
    -d '{"url":"https://google.com"}')

  echo "[REQ $i] HTTP $CODE"

  if [ "$CODE" = "429" ]; then
    echo "[!] RATE LIMIT HIT at request $i"
    break
  fi
done

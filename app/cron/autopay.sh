#!/bin/bash
set -euo pipefail

SECRET_KEY="thisisseckretkey08001220066"
URL="https://www.coravpn.ru/autopay/${SECRET_KEY}"

# Выполняем POST-запрос по новой ссылке, ключ не передаем в теле, он уже в URL
response=$(curl -fsS -m 15 --connect-timeout 5 -X POST "$URL" -w "\n%{http_code}" || echo -e "\n000")

# Разделим ответ и код статуса
body="$(echo "$response" | sed \$d)"
http_code="$(echo "$response" | tail -n1 | tr -d '\r\n')"

if [[ "$http_code" == "200" ]] && ([[ "$body" == *"успешно"* ]] || [[ "$body" == *"success"* ]]); then
  echo 'success'
else
  echo "$body"
  exit 1
fi

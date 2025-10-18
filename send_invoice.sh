#!/bin/bash

TOKEN_FILE="token.json"

# Read values from token.json
TOKEN_ENDPOINT=$(jq -r '.token_endpoint' "$TOKEN_FILE")
USERNAME=$(jq -r '.username' "$TOKEN_FILE")
PASSWORD=$(jq -r '.password' "$TOKEN_FILE")
GRANT_TYPE=$(jq -r '.grant_type' "$TOKEN_FILE")
INVOICE_ENDPOINT=$(jq -r '.invoice_endpoint' "$TOKEN_FILE")
INVOICE_PAYLOAD=$(jq '.invoice_payload' "$TOKEN_FILE")

# Step 1: Request new token
ACCESS_TOKEN=$(jq -n \
  --arg grant_type "$GRANT_TYPE" \
  --arg tenant_id "$USERNAME" \
  --arg tenant_secret "$PASSWORD" \
  '{grant_type: $grant_type, tenant_id: $tenant_id, tenant_secret: $tenant_secret}' \
  | curl -s -X POST "$TOKEN_ENDPOINT" \
  -H "Content-Type: application/json" -d @- \
  | jq -r '.access_token')

# Step 2: Validate token
if [ -z "$ACCESS_TOKEN" ] || [ "$ACCESS_TOKEN" == "null" ]; then
  echo "❌ Failed to get access token"
  exit 1
fi

echo "✅ Access Token issued"

# Step 3: Save the new token back to token.json
jq --arg token "$ACCESS_TOKEN" '.access_token = $token' "$TOKEN_FILE" > tmp.json && mv tmp.json "$TOKEN_FILE"
echo "🔄 token.json updated with new access_token"

# Step 4: Send invoice request
echo "➡️ Sending invoice request..."
curl -i -X POST "$INVOICE_ENDPOINT" \
-H "Authorization: Bearer $ACCESS_TOKEN" \
-H "Content-Type: application/json" \
-d "$INVOICE_PAYLOAD"

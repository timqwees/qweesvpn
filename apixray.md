auth 

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Authentication
Two authentication modes are supported. UI sessions use a cookie set by the login endpoint. Programmatic clients (bots, scripts, remote panels) authenticate with a Bearer token taken from Settings → Security → API Token. Both work for every endpoint under /panel/api/*.



POST
/login
Authenticate with username + password and receive a session cookie. Required before any cookie-based API call.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "username": "admin",
  "password": "admin",
  "twoFactorCode": "123456"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "Logged in successfully"
}
No links
400	
Error response

Media type

application/json
Example Value
Schema
{
  "success": false,
  "msg": "Wrong username or password"
}
No links

POST
/logout
Clear the session cookie. Requires the CSRF header for browser sessions.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

GET
/csrf-token
Mint a CSRF token for the current session. The SPA replays it in the X-CSRF-Token header on unsafe requests. Bearer-token callers can skip this — the middleware short-circuits CSRF for authenticated API requests.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": "csrf-token-string"
}
No links

POST
/getTwoFactorEnable
Returns whether 2FA is enabled on the panel — used by the login page to decide whether to show the OTP field.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": false
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- Inbounds

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Inbounds
Manage inbound configurations and their clients. All endpoints live under /panel/api/inbounds and require a logged-in session or Bearer token. Link-generating endpoints honour forwarded headers only when the request comes from a configured trusted proxy.



GET
/panel/api/inbounds/list
List every inbound owned by the authenticated user, including each inbound’s clientStats traffic counters. settings, streamSettings, and sniffing are returned as nested JSON objects (no escaped strings); legacy callers that send them back as JSON-encoded strings are still accepted on write.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "clientStats": [
        {
          "down": 2097152,
          "email": "user1",
          "enable": true,
          "expiryTime": 1735689600000,
          "id": 14825,
          "inboundId": 1,
          "lastOnline": 1735680000000,
          "lastSubFetch": 1735680000000,
          "reset": 0,
          "resetCount": 0,
          "resetDay": 0,
          "resetMax": 0,
          "subId": "i7tvdpeffi0hvvf1",
          "total": 10737418240,
          "up": 1048576,
          "uuid": "e18c9a96-71bf-48d4-933f-8b9a46d4290c"
        }
      ],
      "disableFlow": false,
      "down": 0,
      "enable": true,
      "expiryTime": 0,
      "fallbackParent": null,
      "id": 1,
      "lastTrafficResetTime": 0,
      "listen": "",
      "nodeId": null,
      "originNodeGuid": "",
      "port": 443,
      "protocol": "vless",
      "remark": "VLESS-443",
      "settings": null,
      "shareAddr": "",
      "shareAddrStrategy": "node",
      "sniffing": null,
      "streamSettings": null,
      "subSortIndex": 1,
      "tag": "in-443-tcp",
      "total": 0,
      "trafficReset": "never",
      "trafficResetDay": 1,
      "up": 0
    }
  ]
}
No links

GET
/panel/api/inbounds/list/slim
Same shape as /list but with settings.clients[] stripped down to {email, enable, comment} and ClientStats not enriched with UUID/SubId. Use this for list pages; fetch /get/:id when you need the full per-client payload (uuid, password, flow, ...).



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "id": 1,
      "remark": "VLESS-443",
      "settings": {
        "clients": [
          {
            "email": "alice",
            "enable": true
          }
        ],
        "decryption": "none"
      },
      "clientStats": []
    }
  ]
}
No links

GET
/panel/api/inbounds/options
Lightweight picker projection of the authenticated user’s inbounds. Returns id, remark, tag, protocol, port, a server-computed tlsFlowCapable flag (true for VLESS on TCP with tls or reality, or on XHTTP with VLESS encryption / vlessenc enabled), and ssMethod (the Shadowsocks cipher, empty for non-Shadowsocks inbounds — used by the client UI to generate a valid Shadowsocks 2022 PSK). Use this for dropdowns and attach pickers — it skips settings, streamSettings, and clientStats so the payload stays small even on panels with thousands of clients.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "awgServer": null,
      "enable": true,
      "id": 1,
      "listen": "",
      "mtprotoDomain": "",
      "network": "",
      "nodeAddress": "",
      "nodeId": null,
      "port": 443,
      "protocol": "vless",
      "remark": "VLESS-443",
      "security": "",
      "shareAddr": "",
      "shareAddrStrategy": "",
      "ssMethod": "",
      "tag": "in-443-tcp",
      "tlsFlowCapable": true,
      "tuicServer": null,
      "wgDns": "",
      "wgMtu": 0,
      "wgPublicKey": ""
    }
  ]
}
No links

GET
/panel/api/inbounds/allLinks
Return every protocol URL (vless://, vmess://, trojan://, ss://, hysteria://, mtproto) across all inbounds and all of their clients. Links are rendered through the subscription engine, so the configured remark template (name-only display part) is applied per client — the same output the client info/QR pages use. Protocols without a URL form (socks, http, mixed, wireguard, dokodemo, tunnel) contribute nothing. Used by the panel’s "Export all inbound links" action.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "vless://uuid@host:443?security=reality&...#Germany-alice",
    "vmess://eyJ2IjoyLC..."
  ]
}
No links

GET
/panel/api/inbounds/get/{id}
Fetch a single inbound by numeric ID.



Parameters
Cancel
Name	Description
id *
integer
(path)
Inbound ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/add
Create a new inbound. Send the full inbound payload (protocol, port, settings, streamSettings, sniffing, remark, expiryTime, total, enable). settings, streamSettings, and sniffing may be sent as nested JSON objects (preferred) or as JSON-encoded strings (legacy).



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "enable": true,
  "remark": "VLESS-443",
  "listen": "",
  "port": 443,
  "protocol": "vless",
  "expiryTime": 0,
  "total": 0,
  "settings": {
    "clients": [
      {
        "id": "...",
        "email": "user1"
      }
    ],
    "decryption": "none",
    "fallbacks": []
  },
  "streamSettings": {
    "network": "tcp",
    "security": "reality",
    "realitySettings": {
      "show": false,
      "dest": "..."
    }
  },
  "sniffing": {
    "enabled": true,
    "destOverride": [
      "http",
      "tls"
    ]
  }
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links
400	
Error response

Media type

application/json
Example Value
Schema
{
  "success": false,
  "msg": "Port 443 is already in use"
}
No links

POST
/panel/api/inbounds/del/{id}
Delete an inbound by ID. Also removes its associated client stats rows.



Parameters
Cancel
Name	Description
id *
integer
(path)
Inbound ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/bulkDel
Delete many inbounds in one call. Processes the list sequentially; failures are reported per id and the rest still proceed. Restarts xray at most once.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "ids": [
    1,
    2,
    3
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "deleted": 2,
    "skipped": [
      {
        "id": 3,
        "reason": "..."
      }
    ]
  }
}
No links

POST
/panel/api/inbounds/update/{id}
Replace an inbound’s configuration. Body shape mirrors /add. Heavy on inbounds with thousands of clients — prefer /setEnable for enable-only flips.



Parameters
Cancel
Name	Description
id *
integer
(path)
Inbound ID.

id
Request body

application/json
Edit Value
Schema
{
  "enable": true,
  "remark": "VLESS-443",
  "listen": "",
  "port": 443,
  "protocol": "vless",
  "expiryTime": 0,
  "total": 0,
  "settings": {
    "clients": [
      {
        "id": "...",
        "email": "user1"
      }
    ],
    "decryption": "none",
    "fallbacks": []
  },
  "streamSettings": {
    "network": "tcp",
    "security": "reality",
    "realitySettings": {
      "show": false,
      "dest": "..."
    }
  },
  "sniffing": {
    "enabled": true,
    "destOverride": [
      "http",
      "tls"
    ]
  }
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/setEnable/{id}
Toggle only the enable flag without serialising the whole settings JSON. Recommended for UI switches on large inbounds.



Parameters
Cancel
Name	Description
id *
integer
(path)
Inbound ID.

id
Request body

application/json
Edit Value
Schema
{
  "enable": false
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/{id}/subSortIndex
Set only the subscription sort order. Reads the stored inbound, so a reorder cannot carry a stale client list over a concurrent edit.



Parameters
Cancel
Name	Description
id *
integer
(path)
Inbound ID.

id
Request body

application/json
Edit Value
Schema
{
  "subSortIndex": 2
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/{id}/resetTraffic
Zero out upload + download counters for a single inbound. Does not touch per-client counters.



Parameters
Cancel
Name	Description
id *
integer
(path)
Inbound ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/{id}/delAllClients
Remove every client attached to a single inbound while keeping the inbound itself. Collects emails from settings.clients[] and feeds them into the optimized bulk-delete path (runtime user removal + traffic-row cleanup + SyncInbound). Destructive and cannot be undone.



Parameters
Cancel
Name	Description
id *
integer
(path)
Inbound ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "deleted": 12
  }
}
No links

POST
/panel/api/inbounds/resetAllTraffics
Reset upload + download counters on every inbound. Destructive — accounting history is lost.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/import
Bulk-import an inbound from a JSON blob (e.g. one exported via the UI). The body uses form encoding with a single "data" field.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
data *
string
JSON-encoded inbound payload.

string
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/inbounds/pushClientTraffics
Receive a master panel's aggregated per-client usage, keyed by the master's GUID. Stored in a side table used only for the UI display overlay and local quota enforcement — never folded into the local counters that masters poll, so delta accounting stays intact. Called panel-to-panel by the node traffic sync job.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "masterGuid": "9f6c2d-…",
  "traffics": [
    {
      "email": "alice",
      "up": 1048576,
      "down": 2097152
    }
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

GET
/panel/api/inbounds/{id}/fallbacks
List the fallback rules attached to a master VLESS/Trojan TCP-TLS inbound. Each rule links one child inbound (the dest) to optional SNI/ALPN/path/dest/xver match criteria. When dest is empty the child inbound's listen+port is used.



Parameters
Cancel
Name	Description
id *
integer
(path)
Master inbound ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "id": 1,
      "masterId": 10,
      "childId": 11,
      "name": "",
      "alpn": "",
      "path": "/vlws",
      "dest": "",
      "xver": 2,
      "sortOrder": 0
    }
  ]
}
No links

POST
/panel/api/inbounds/{id}/fallbacks
Replace the entire fallback list for a master inbound. Body is JSON. Triggers an Xray restart.



Parameters
Cancel
Name	Description
id *
integer
(path)
Master inbound ID.

id
Request body

application/json
Edit Value
Schema
{
  "fallbacks": [
    {
      "childId": 11,
      "path": "/vlws",
      "xver": 2
    },
    {
      "childId": 12,
      "alpn": "h2",
      "dest": "8443"
    }
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "Inbound updated"
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- server

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Server
System status, log retrieval, certificate generators, Xray binary management, and backup/restore. All under /panel/api/server.



GET
/panel/api/openapi.json
Serve this API description as an OpenAPI 3 document — the same file that powers the API Docs page. Requires a session or Bearer token like the rest of /panel/api. Useful for generating clients or importing into API tooling.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/status
Real-time machine snapshot: CPU, memory, swap, disk, network IO, load averages, open connections, Xray state. Cached and refreshed every 2 seconds in the background.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "cpu": 12.5,
    "mem": {
      "current": 2147483648,
      "total": 8589934592
    },
    "swap": {
      "current": 0,
      "total": 4294967296
    },
    "disk": {
      "current": 53687091200,
      "total": 268435456000
    },
    "netIO": {
      "up": 1073741824,
      "down": 2147483648
    },
    "xray": {
      "state": "running",
      "version": "v25.10.31"
    },
    "tcpCount": 42,
    "load": {
      "load1": 0.5,
      "load5": 0.3,
      "load15": 0.2
    }
  }
}
No links

GET
/panel/api/server/fail2banStatus
Reports whether per-client IP limits can be enforced on this host. The panel uses it to gate the "IP Limit" field, since enforcement depends on Fail2ban being installed.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "enabled": true,
    "installed": true,
    "usable": true,
    "windows": false
  }
}
No links

GET
/panel/api/server/cpuHistory/{bucket}
Legacy: aggregated CPU history. Use /history/cpu/:bucket instead — same data with a uniform {t, v} shape.



Parameters
Cancel
Name	Description
bucket *
integer
(path)
Bucket size in seconds. Allowed: 2, 30, 60, 120, 180, 300.

bucket
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/history/{metric}/{bucket}
Aggregated time-series for one metric. Returns an array of {t, v} samples covering the last ~6 hours.



Parameters
Cancel
Name	Description
metric *
string
(path)
cpu | mem | netUp | netDown | online | load1 | load5 | load15.

metric
bucket *
integer
(path)
Bucket size in seconds. Allowed: 2, 30, 60, 120, 180, 300.

bucket
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "t": 1700000000,
      "v": 12.5
    },
    {
      "t": 1700000002,
      "v": 13.1
    }
  ]
}
No links

GET
/panel/api/server/xrayMetricsState
Xray runtime metrics state — whether the xray config has a `metrics` block, which expvar keys are flowing, and the current snapshot values for each. Returns an empty state when metrics are not configured.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/xrayMetricsHistory/{metric}/{bucket}
Time-series history for one Xray runtime metric over the last ~6 hours. Same {t, v} shape as /history/:metric/:bucket.



Parameters
Cancel
Name	Description
metric *
string
(path)
xrAlloc | xrSys | xrHeapObjects | xrNumGC | xrPauseNs.

metric
bucket *
integer
(path)
Bucket size in seconds. Allowed: 2, 30, 60, 120, 180, 300.

bucket
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/xrayObservatory
Latest snapshot from the Xray observatory — per-outbound latency, health status, and last-probe time. Only populated when the Xray config has an observatory configured.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/xrayObservatoryHistory/{tag}/{bucket}
Time-series of observatory probe results for one outbound tag. Same {t, v} shape as the other history endpoints.



Parameters
Cancel
Name	Description
tag *
string
(path)
Outbound tag from the observatory config.

tag
bucket *
integer
(path)
Bucket size in seconds. Allowed: 2, 30, 60, 120, 180, 300.

bucket
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/getXrayVersion
List Xray binary versions available for install on this host.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "v25.10.31",
    "v25.9.15",
    "v25.8.1"
  ]
}
No links

GET
/panel/api/server/getPanelUpdateInfo
Check whether a newer 3x-ui release is available on GitHub.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/getUpdateStatus
Report the outcome of the most recently launched panel self-update (see POST updatePanel). Compare the returned runId against the one updatePanel returned to tell this run apart from a stale result.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "exitCode": 0,
    "finishedAt": 1735689612,
    "runId": "1735689600123456789",
    "state": "success"
  }
}
No links

GET
/panel/api/server/getConfigJson
Return the assembled Xray config that’s currently running on this host.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/getDb
Stream a full database backup as an attachment: the SQLite .db file on SQLite panels, or a pg_dump custom-format archive (.dump) on PostgreSQL panels. Use as a manual backup.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/getMigration
Stream a cross-engine migration file as an attachment: a .dump (SQL text) on SQLite, or a .db SQLite database built from the live data on PostgreSQL.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/server/getNewUUID
Generate a fresh UUID v4. Convenience helper for client IDs.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "uuid": "550e8400-e29b-41d4-a716-446655440000"
  }
}
No links

GET
/panel/api/server/getWebCertFiles
Return this panel's own web TLS certificate and key file paths. The central panel calls it on a node (via the node API token) so "Set Cert from Panel" fills a node-assigned inbound with paths that exist on the node.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "webCertFile": "/root/cert/example.com/fullchain.pem",
    "webKeyFile": "/root/cert/example.com/privkey.pem"
  }
}
No links

GET
/panel/api/server/descendants
Read-only summaries (guid, parentGuid, name, address, status, versions) of the nodes this panel manages. A parent panel calls it on a node (via the node API token) to surface transitive sub-nodes in a chained topology. Counts are computed by the parent, not returned here.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "guid": "c3d4-...",
      "parentGuid": "a1b2-...",
      "name": "Node3",
      "address": "10.0.0.3",
      "status": "online"
    }
  ]
}
No links

GET
/panel/api/server/getNewX25519Cert
Generate a new X25519 keypair for Reality.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "privateKey": "uN9qLfV3zH8w...",
    "publicKey": "5v8xPqR2sM7k..."
  }
}
No links

GET
/panel/api/server/getNewmldsa65
Generate a new ML-DSA-65 keypair. Returns {seed, verify}.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "seed": "mldsa65-seed",
    "verify": "mldsa65-verify"
  }
}
No links

GET
/panel/api/server/getNewmlkem768
Generate a new ML-KEM-768 keypair. Returns {seed, client}.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "client": "mlkem768-client",
    "seed": "mlkem768-seed"
  }
}
No links

GET
/panel/api/server/getNewVlessEnc
Generate VLESS encryption auth options. Returns an auths array each with id, label, encryption, and decryption fields.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "auths": [
      {
        "id": 0,
        "label": "Auth #0",
        "encryption": "aes-256-gcm",
        "decryption": ""
      }
    ]
  }
}
No links

POST
/panel/api/server/stopXrayService
Stop the Xray binary. All proxies go offline immediately.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links
400	
Error response

Media type

application/json
Example Value
Schema
{
  "success": false,
  "msg": "Xray is not running"
}
No links

POST
/panel/api/server/restartXrayService
Reload Xray with the current config. Typically required after structural inbound or routing changes.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links
400	
Error response

Media type

application/json
Example Value
Schema
{
  "success": false,
  "msg": "Xray config is invalid: ..."
}
No links

POST
/panel/api/server/installXray/{version}
Download and install the specified Xray version. Pass "latest" for the newest release.



Parameters
Cancel
Name	Description
version *
string
(path)
Xray tag (e.g. v25.10.31) or "latest".

version
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/server/updatePanel
Self-update the panel to the latest version. The server restarts on success.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
dev
boolean
Override this run's channel. Omit to use the panel's configured channel.


true
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "runId": "1735689600123456789"
  }
}
No links

POST
/panel/api/server/setUpdateChannel
Toggle the panel update channel between stable and the rolling per-commit dev release. Only effective on dev builds.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
dev *
boolean
true = dev channel, false = stable.


true
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/server/updateGeofile
Refresh the default GeoIP / GeoSite data files. Use the /:fileName variant to update one file.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/server/updateGeofile/{fileName}
Refresh a single Geo file by filename (e.g. geoip.dat, geosite.dat).



Parameters
Cancel
Name	Description
fileName *
string
(path)
Filename of the data file to refresh.

fileName
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/server/logs/{count}
Return the last N lines of the panel’s own log.



Parameters
Cancel
Name	Description
count *
integer
(path)
Number of trailing log lines.

count
Request body

application/x-www-form-urlencoded
level
string
Minimum log level filter.

string
Send empty value
syslog
boolean
Read system logs instead of the panel log.


true
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "2025/01/01 12:00:00 [INFO] Server started",
    "2025/01/01 12:00:01 [INFO] Xray is running"
  ]
}
No links

POST
/panel/api/server/xraylogs/{count}
Return the last N lines of the Xray process log.



Parameters
Cancel
Name	Description
count *
integer
(path)
Number of trailing log lines.

count
Request body

application/x-www-form-urlencoded
filter
string
Keyword filter — only lines containing this string.

string
Send empty value
showDirect
string
"true" to include direct (freedom) traffic lines.

string
Send empty value
showBlocked
string
"true" to include blocked (blackhole) traffic lines.

string
Send empty value
showProxy
string
"true" to include proxy traffic lines.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "DateTime": "2025-01-01T12:00:00Z",
      "Email": "alice@example.com",
      "Event": 0,
      "FromAddress": "192.0.2.10:54321",
      "Inbound": "inbound-443",
      "Outbound": "direct",
      "ToAddress": "example.com:443"
    }
  ]
}
No links

POST
/panel/api/server/amneziawglogs/{count}
Return live AmneziaWG peer activity (handshake, endpoint, transfer) plus the panel’s own AmneziaWG event lines.



Parameters
Cancel
Name	Description
count *
integer
(path)
Maximum peer rows and event lines to return.

count
Request body

application/x-www-form-urlencoded
filter
string
Keyword filter — only rows/lines containing this string.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "events": [
      "2025/01/01 12:00:00 amneziawg: started interface awg1 for inbound 1"
    ],
    "peers": [
      {
        "allowedIPs": "10.8.1.2/32",
        "down": 4194304,
        "email": "peer@example.com",
        "endpoint": "203.0.113.9:51820",
        "handshake": 1735732800000,
        "inboundId": 1,
        "interface": "awg1",
        "online": true,
        "tag": "inbound-51820",
        "up": 1048576
      }
    ],
    "running": true
  }
}
No links

POST
/panel/api/server/importDB
Restore the panel DB from an uploaded backup (multipart form, field name "db"). SQLite panels accept a SQLite database (.db) or a SQLite migration dump (.dump); PostgreSQL panels accept a pg_dump archive (.dump), a SQLite database (.db), or a SQLite migration dump. The panel restarts after restore. Destructive.



Parameters
Cancel
Reset
No parameters

Request body

multipart/form-data
db *
string($binary)
Database backup or migration file to upload.

Файл не выбран
keepHostSettings
boolean
Keep this machine's addresses, certificates and node identity. Default true.


true
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/server/getNewEchCert
Generate a new ECH (Encrypted Client Hello) keypair and config list for the given SNI.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
sni *
string
Server Name Indication to generate the ECH config for.

string
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/server/getCertHash
Compute the hex SHA-256 of a certificate (DER) for pinning (pinnedPeerCertSha256). Provide either a server file path or inline PEM/DER content.



Parameters
Cancel
Reset
No parameters

Request body

application/x-www-form-urlencoded
Edit Value
Schema
{
  "certFile": "7V8gqwlk3i!aq0/oWMJL$yAa-0osD4C\"\" (Y>bf#^Dauz%xDykZu1OGc'1f~8`n`QUxS$k<X<J}JnH+?(V3k1[R2p.On7i%4Y)E4lt^yo-[@' $?)oj n@D77O:XI0Br*\"K><g3haE,x*F7O4iG",
  "certContent": "string"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "e8e2d3..."
  ]
}
No links

POST
/panel/api/server/getRemoteCertHash
Run `xray tls ping` against a remote server and return its live leaf-certificate SHA-256 hash(es) for pinning (pinnedPeerCertSha256).



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
server *
string
Remote server as domain or domain:port (default port 443), e.g. cloudflare-dns.com.

string
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "e8e2d3..."
  ]
}
No links

POST
/panel/api/server/scanRealityTarget
Run a live TLS 1.3 probe against a candidate REALITY target and return a feasibility verdict (TLS 1.3 + h2 + X25519 + trusted certificate) plus the certificate SAN DNS names. A target on a private/loopback address is reported with privateTarget=true and probed only when allowPrivate is set.



Parameters
Cancel
Reset
No parameters

Request body

application/x-www-form-urlencoded
target *
string
Candidate target as host or host:port (default port 443), e.g. www.cloudflare.com:443.

string
sni
string
SNI the handshake sends and the certificate is verified against (the inbound serverNames). Defaults to the target host, which a fronting proxy answers with its default certificate.

string
Send empty value
xver
integer
PROXY protocol version the target expects (matches the inbound xver). 0 = none.

0
Send empty value
allowPrivate
boolean
Probe a private/internal/loopback target (LAN, Docker service name). Default false (SSRF guard blocks it and the response sets privateTarget=true).


true
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "alpn": "h2",
    "certChainBytes": 3427,
    "certChainValid": true,
    "certIssuer": "Google Trust Services",
    "certSubject": "cloudflare.com",
    "certValid": true,
    "curveID": "X25519",
    "feasible": true,
    "h2": true,
    "host": "www.cloudflare.com",
    "ip": "104.16.124.96",
    "latencyMs": 180,
    "notAfter": "2026-08-01T00:00:00Z",
    "port": 443,
    "privateTarget": false,
    "reason": "",
    "serverNames": [
      ""
    ],
    "target": "www.cloudflare.com:443",
    "tls13": true,
    "tlsVersion": "1.3",
    "x25519": true
  }
}
No links

POST
/panel/api/server/scanRealityTargets
Probe/discover REALITY targets and return each verdict ranked by feasibility then latency. Each comma-separated token may be a domain (validated with SNI), a bare IP, or a CIDR range (discovered without SNI by reading the certificate domain). When empty, the realityScanCandidates setting is probed (the built-in seed list if that setting is empty).



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
targets
string
Optional comma-separated tokens: domain[:port], IP[:port], or CIDR (e.g. 104.16.0.0/24). When omitted, the realityScanCandidates setting is probed (the built-in seed list if that setting is empty).

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "alpn": "h2",
      "certChainBytes": 3427,
      "certChainValid": true,
      "certIssuer": "Google Trust Services",
      "certSubject": "cloudflare.com",
      "certValid": true,
      "curveID": "X25519",
      "feasible": true,
      "h2": true,
      "host": "www.cloudflare.com",
      "ip": "104.16.124.96",
      "latencyMs": 180,
      "notAfter": "2026-08-01T00:00:00Z",
      "port": 443,
      "privateTarget": false,
      "reason": "",
      "serverNames": [
        ""
      ],
      "target": "www.cloudflare.com:443",
      "tls13": true,
      "tlsVersion": "1.3",
      "x25519": true
    }
  ]
}
No links

GET
/panel/api/server/clientIps
Fetch the fully aggregated inbound_client_ips database table. Used by nodes to sync recently active IPs across the cluster.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "clientEmail": "",
      "id": 0,
      "ips": null
    }
  ]
}
No links

POST
/panel/api/server/clientIps
Submit a list of recently active IP timestamps. The panel merges them with the existing database to maintain a unified global IP-limit view.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
[
  {
    "clientEmail": "string",
    "ips": [
      {
        "ip": "string",
        "timestamp": 0
      }
    ]
  }
]
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- clients

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Clients
Manage clients as first-class entities that can be attached to one or more inbounds. A single client row drives the settings.clients entry in every inbound it belongs to. Endpoints live under /panel/api/clients.



GET
/panel/api/clients/list
List every client with its attached inbound IDs and traffic record. The reverse field, if set, is returned as a nested JSON object (legacy JSON-encoded-string form is still accepted on write).



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "id": 1,
      "email": "alice@example.com",
      "subId": "abcd1234",
      "uuid": "...",
      "totalGB": 53687091200,
      "expiryTime": 1735689600000,
      "enable": true,
      "reverse": null,
      "inboundIds": [
        3,
        5
      ],
      "traffic": {
        "up": 1024,
        "down": 4096,
        "enable": true
      }
    }
  ]
}
No links

GET
/panel/api/clients/list/paged
Filter, sort, and paginate clients on the server. Each item is a slim row (no uuid/password/auth/flow/security/reverse/tgId) so the clients page can ship 25-ish rows in a few KB instead of the full table. The response also includes a summary computed across the full DB row set so dashboard counters stay stable as the user paginates or filters: the *Count fields are exact, while the email arrays beside them stop at 200 entries so the payload does not grow with the panel. Page size capped at 200; fetch /get/:email to obtain the full per-client payload for an edit/info modal.



Parameters
Cancel
Name	Description
page
integer
(query)
1-indexed page number. Defaults to 1.

1
pageSize
integer
(query)
Rows per page. Defaults to 25, capped at 200.

25
search
string
(query)
Case-insensitive substring match on email, subId, comment, UUID, password, auth or Telegram ID.

search
filter
string
(query)
CSV status buckets: online, active, deactive, depleted or expiring. Values are ORed.

filter
protocol
string
(query)
CSV inbound protocols: vmess, vless, trojan, shadowsocks, wireguard, hysteria, http, mixed, tunnel, tun, mtproto or amneziawg. Values are ORed.

protocol
inbound
string
(query)
CSV positive inbound IDs. Values are ORed; invalid or non-positive IDs are ignored.

inbound
sort
string
(query)
Sort key. An omitted or unknown value falls back to client ID ascending.


--
order
string
(query)
Sort direction. Only descend selects descending order; otherwise ascending.


--
expiryFrom
integer
(query)
Inclusive minimum expiry time in Unix milliseconds. Zero or negative means unset.

expiryFrom
expiryTo
integer
(query)
Inclusive maximum expiry time in Unix milliseconds. Zero or negative means unbounded.

expiryTo
usageFrom
integer
(query)
Inclusive minimum combined upload and download usage in bytes. Zero means unset.

usageFrom
usageTo
integer
(query)
Inclusive maximum combined upload and download usage in bytes. Zero means unbounded.

usageTo
autoRenew
string
(query)
on selects clients with an interval or calendar-day reset; off selects clients without either.


--
hasTgId
string
(query)
yes selects clients with a non-zero Telegram ID; no selects clients without one.


--
hasComment
string
(query)
yes selects clients with a non-blank comment; no selects clients without one.


--
group
string
(query)
CSV group names, matched case-insensitively after trimming. Values are ORed.

group
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "filtered": 47,
    "groups": [
      "staff",
      "trial"
    ],
    "items": [
      {
        "comment": "Primary device",
        "createdAt": 1735000000000,
        "email": "alice@example.com",
        "enable": true,
        "expiryTime": 1735689600000,
        "group": "staff",
        "inboundIds": [
          3,
          5
        ],
        "limitHwid": 0,
        "limitIp": 0,
        "reset": 0,
        "resetDay": 0,
        "resetMax": 0,
        "subId": "abcd1234",
        "totalGB": 53687091200,
        "traffic": null,
        "updatedAt": 1735100000000
      }
    ],
    "page": 1,
    "pageSize": 25,
    "summary": {
      "active": 1850,
      "deactive": [
        "bob@example.com"
      ],
      "deactiveCount": 150,
      "depleted": [],
      "depletedCount": 0,
      "expiring": [],
      "expiringCount": 0,
      "online": [
        "alice@example.com"
      ],
      "onlineCount": 1,
      "total": 2000
    },
    "total": 2000
  }
}
No links

GET
/panel/api/clients/get/{email}
Fetch one client by email, including the inbound IDs and external config IDs it is attached to.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email (unique identifier).

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/clients/get/tgId/{tgId}
Fetch clients by Telegram user ID. Returns an array since multiple clients can share the same Telegram ID.



Parameters
Cancel
Name	Description
tgId *
integer
(path)
Telegram user ID (numeric).

tgId
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/clients/add
Create a new client and attach it to one or more inbounds in a single call. Body is JSON. Per-protocol secrets are generated server-side when omitted, so callers can send only the universal fields.



Fields the server fills in when they are omitted — a valid value sent by the caller is never overwritten. Re-adding an email that already exists, with its stored subId, reuses the stored id, password, auth and secret instead of minting new ones, so the identity stays in sync across its inbounds.

VLESS / VMess — id, a fresh UUID
Trojan — password
Shadowsocks — password. On a 2022-blake3-* inbound a supplied password that does not base64-decode to the key length of the cipher (16 or 32 bytes) is replaced by a generated key and the call still succeeds, so read the client back if you did not let the server pick. Legacy ciphers keep any non-empty password
Hysteria — auth
mtproto — secret, a FakeTLS secret derived from the fronting domain of the inbound, or from www.cloudflare.com when it has none
WireGuard — privateKey and publicKey when both are blank, or publicKey alone when only a privateKey was sent, plus allowedIPs: one free /32 taken from the /24 the existing peers of that inbound already sit in, or from 10.0.0.0/24 when it has none
Accepted on the same body but never generated: preSharedKey and keepAlive (WireGuard), adTag (mtproto).

WireGuard is the only one of these that can fail. Allocation widens the search to the containing /16 before giving up with inbound <id>: wireguard: no free address available in <scope>, and an allowedIPs supplied by the caller is validated instead of allocated: inbound <id>: wireguard: allowedIPs entry already used by another client: <address> when a different client of that same inbound already holds it. The check is per inbound, so the same address on two different inbounds is accepted. The same validation runs on POST /panel/api/clients/{email}/attach, where a client that already carries an address brings it along.

An inboundIds entry that names no existing inbound rejects the whole call before anything is written. Past that, the inbounds are applied concurrently and independently: one that fails no longer stops the others, so a success:false response can still have created the client on the rest. Every error names the inbound it came from (inbound 7: <message>), and several failures are reported together, one per line. limitHwid is applied only when every inbound succeeded, so re-run the call after fixing the failure.

Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "client": {
    "email": "alice@example.com",
    "totalGB": 53687091200,
    "expiryTime": 1735689600000,
    "tgId": 0,
    "limitIp": 0,
    "limitHwid": 0,
    "enable": true
  },
  "inboundIds": [
    3,
    5
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "Client added"
}
No links

POST
/panel/api/clients/update/{email}
Update an existing client by email. Changes propagate to every attached inbound. Body is the JSON client payload — supply the full set of fields you want to keep (the server replaces the row, it does not patch).



The inbounds are applied concurrently and independently: one that fails no longer stops the others. Every inbound error names the inbound it came from (inbound 7: <message>), and several failures are reported together, one per line. So a success:false response can still have applied the edit to the remaining inbounds. The client record is written after the inbounds, so a failure there is reported without an inbound <id>: prefix and leaves the inbound edits in place.

Parameters
Cancel
Name	Description
email *
string
(path)
Current client email (unique identifier).

email
Request body

application/json
Edit Value
Schema
{
  "email": "alice@example.com",
  "totalGB": 107374182400,
  "expiryTime": 1767225600000,
  "limitHwid": 2,
  "tgId": 123456789,
  "enable": true
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "Client updated"
}
No links

POST
/panel/api/clients/del/{email}
Delete a client by email. Removes it from every attached inbound and drops its traffic record unless keepTraffic=1 is passed.



The inbounds are applied concurrently and independently: one that fails no longer stops the others. Every inbound error names the inbound it came from (inbound 7: <message>), and several failures are reported together, one per line. So a success:false response can still have removed the client from the remaining inbounds; the client record is kept in that case, so re-running the call retries exactly the leftovers. The record and traffic rows are dropped after the inbounds, so a failure there is reported without an inbound <id>: prefix and leaves the client already removed from every inbound.

Parameters
Cancel
Name	Description
email *
string
(path)
Client email (unique identifier).

email
keepTraffic *
integer
(query)
Pass 1 to retain the xray_client_traffic row after deletion.

keepTraffic
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "Client deleted"
}
No links

POST
/panel/api/clients/{email}/attach
Attach an existing client to one or more additional inbounds. Body is JSON.



A WireGuard client brings its stored allowedIPs into the new inbound instead of being given a fresh address, so the call fails with inbound <id>: wireguard: allowedIPs entry already used by another client: <address> when a different client of the target inbound already holds it. Free the address on that inbound first — see POST /panel/api/clients/add for the full rule. Inbounds are applied independently, so the remaining ones are still attached and a success:false response can be partial.

Parameters
Cancel
Name	Description
email *
string
(path)
Client email (unique identifier).

email
Request body

application/json
Edit Value
Schema
{
  "inboundIds": [
    7,
    9
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

POST
/panel/api/clients/{email}/detach
Detach a client from one or more inbounds without deleting the client.



The inbounds are applied concurrently and independently: one that fails no longer stops the others. Every inbound error names the inbound it came from (inbound 7: <message>), and several failures are reported together, one per line. So a success:false response can still have detached the remaining inbounds. Detach writes nothing beyond the inbounds, so every error carries the prefix.

Parameters
Cancel
Name	Description
email *
string
(path)
Client email (unique identifier).

email
Request body

application/json
Edit Value
Schema
{
  "inboundIds": [
    5
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

POST
/panel/api/clients/{email}/externalLinks
Replace a client's external links and external subscriptions. Sends the full set; the server replaces all rows. Disabled rows stay saved for editing but are not emitted in generated subscriptions. The owning client's disabled or expired state also stops these rows from being emitted on future subscription fetches; credentials already imported by an app remain valid until the external provider revokes them.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email (unique identifier).

email
Request body

application/json
Edit Value
Schema
{
  "externalLinks": [
    {
      "kind": "link",
      "value": "vless://uuid@host:443?...#srv",
      "remark": "DE",
      "enable": true,
      "expiryTime": 0
    },
    {
      "kind": "subscription",
      "value": "https://provider.example/sub/abc",
      "remark": "Provider",
      "enable": false,
      "expiryTime": 1767225600000,
      "namePrefix": "[zjh] "
    }
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

POST
/panel/api/clients/resetAllTraffics
Reset the up/down counters for every client globally. Quotas and expiry are not affected. Triggers an Xray restart if any counter actually moved.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

POST
/panel/api/clients/delDepleted
Delete every client whose traffic quota is exhausted (used >= total, when reset is disabled) or whose expiry has passed. Returns the deleted count and triggers an Xray restart when any client was on a running inbound.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "deleted": 0
  }
}
No links

POST
/panel/api/clients/delOrphans
Delete every client that is not attached to any inbound, along with its traffic record, IP log, HWID devices, and external links. Useful for clearing clients left unattached after their inbounds were removed. Returns the deleted count. Cannot be undone.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "deleted": 0
  }
}
No links

GET
/panel/api/clients/export
Return every client as a {client, inboundIds} array — the same shape /bulkCreate and /import accept — so the payload round-trips straight back through /import. Clients with no inbound attachment are included with an empty inboundIds list. The UI shows this in a CodeMirror viewer (copy / download); programmatic callers get the array in obj.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "client": {
        "email": "alice@example.com",
        "id": "...",
        "totalGB": 53687091200,
        "expiryTime": 0,
        "limitHwid": 2,
        "enable": true,
        "subId": "..."
      },
      "inboundIds": [
        7,
        9
      ]
    }
  ]
}
No links

POST
/panel/api/clients/import
Import clients from a JSON body { "data": "<json>" }, where data is a string-encoded array produced by /export ([{client, inboundIds}]). Items with inboundIds are created and attached to those inbounds; items with an empty inboundIds list are restored as unattached client records. Existing emails are never overwritten — they are returned in skipped. Triggers a single Xray restart at the end if any target inbound was running.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "data": "[{\"client\":{\"email\":\"alice@example.com\",\"enable\":true},\"inboundIds\":[7]}]"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "created": 2,
    "skipped": [
      {
        "email": "alice@example.com",
        "reason": "email already in use: alice@example.com"
      }
    ]
  }
}
No links

POST
/panel/api/clients/bulkAdjust
Shift expiry and/or traffic quota for many clients in one call. addDays/addBytes may be negative. Clients with unlimited expiry (expiryTime=0) or unlimited traffic (totalGB=0) are skipped for the corresponding field — bulk extend never converts unlimited to limited. A client that was auto-disabled solely because it was depleted (expired or over quota) is automatically re-enabled — locally and on its node — when the adjustment lifts it out of depletion; a manually-disabled or still-depleted client is left disabled. The optional flow directive sets the XTLS flow on every client: "none" clears it, "xtls-rprx-vision"/"xtls-rprx-vision-udp443" set it where the inbound supports it (omit or "" to leave it unchanged). The optional limitHwid sets maximum registered devices (0 = unlimited). The optional adTag sets MTProto Telegram sponsor channel ("none" clears). Returns the adjusted count and per-email skip reasons.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ],
  "addDays": 30,
  "addBytes": 53687091200,
  "flow": "xtls-rprx-vision",
  "limitHwid": 2,
  "adTag": "0123456789abcdef0123456789abcdef"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "adjusted": 2,
    "skipped": [
      {
        "email": "carol",
        "reason": "unlimited expiry"
      }
    ]
  }
}
No links

POST
/panel/api/clients/bulkEnable
Enable many clients in one call. Emails are grouped by inbound and applied with a single read-modify-write per inbound; the running Xray (local or remote node) is updated to add each user. Note that enabling a client whose quota is exhausted or whose expiry has passed only flips the flag — the traffic loop will disable it again on the next tick. Returns the changed count and per-email skip reasons.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "changed": 2,
    "skipped": [
      {
        "email": "carol",
        "reason": "client not found"
      }
    ]
  }
}
No links

POST
/panel/api/clients/bulkDisable
Disable many clients in one call. Emails are grouped by inbound and applied with a single read-modify-write per inbound; the running Xray (local or remote node) is updated to remove each user. Returns the changed count and per-email skip reasons.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "changed": 2,
    "skipped": [
      {
        "email": "carol",
        "reason": "client not found"
      }
    ]
  }
}
No links

POST
/panel/api/clients/bulkDel
Delete many clients in one call. The server processes the list sequentially so each delete sees the committed state of the previous one — avoids the race the per-email fan-out had on the panel side. Pass keepTraffic=true to retain the xray_client_traffic rows after deletion.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ],
  "keepTraffic": false
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "deleted": 2,
    "skipped": [
      {
        "email": "carol",
        "reason": "client not found"
      }
    ]
  }
}
No links

POST
/panel/api/clients/bulkCreate
Create many clients in one call. Body is a JSON array of {client, inboundIds} payloads — the same shape /add accepts. Items are processed sequentially; per-email skip reasons are returned for items that fail (e.g., duplicate email). Triggers a single Xray restart at the end if any inbound was running.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
[
  {
    "client": {
      "email": "alice@example.com",
      "totalGB": 53687091200,
      "expiryTime": 0,
      "limitHwid": 2,
      "enable": true
    },
    "inboundIds": [
      7
    ]
  },
  {
    "client": {
      "email": "bob@example.com",
      "totalGB": 53687091200,
      "expiryTime": 0,
      "limitHwid": 0,
      "enable": true
    },
    "inboundIds": [
      7,
      9
    ]
  }
]
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "created": 2,
    "skipped": [
      {
        "email": "alice@example.com",
        "reason": "email already in use"
      }
    ]
  }
}
No links

POST
/panel/api/clients/groups/bulkAdd
Add many clients to a group in one call. Updates clients.group_name and patches the matching client entry inside every owning inbound's settings JSON in a single transaction. If the group name does not yet exist (in client_groups or as a derived label), it is auto-created as a persistent group. To clear the group label, use /groups/bulkRemove instead.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ],
  "group": "customer-a"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "affected": 2
  }
}
No links

POST
/panel/api/clients/groups/bulkRemove
Clear the group label on many clients in one call. Inverse of /groups/bulkAdd. Clients themselves are kept — only the group label is cleared from clients.group_name and from each owning inbound's settings JSON. Groups become empty if all their members are removed.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "affected": 2
  }
}
No links

POST
/panel/api/clients/bulkAttach
Attach many existing clients to many inbounds in one call. Each client keeps its identity (email/UUID/password/subId) and a shared traffic row; all clients are added to a target inbound in a single AddInboundClient call. Clients already present on a target are reported under skipped. Returns per-email attached/skipped/errors lists and triggers a single Xray restart if any target inbound was running.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ],
  "inboundIds": [
    7,
    9
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "attached": [
      "alice",
      "bob"
    ],
    "skipped": [
      "bob"
    ],
    "errors": []
  }
}
No links

POST
/panel/api/clients/bulkDetach
Mirror of bulkAttach: detach many existing clients from many inbounds in one call. For each email, intersects the client's current inbounds with the requested set and detaches from those only; (email, inbound) pairs where the client is not currently attached are silently no-ops. Emails not attached to any of the requested inbounds are reported under skipped. Client records are kept even if they become orphaned — use bulkDel for full removal. Returns per-email detached/skipped/errors lists and triggers a single Xray restart if any target inbound was running.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ],
  "inboundIds": [
    7,
    9
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "detached": [
      "alice",
      "bob"
    ],
    "skipped": [],
    "errors": []
  }
}
No links

POST
/panel/api/clients/bulkResetTraffic
Zero up/down counters for many clients in one call. Loops the single-reset path so each client is re-enabled across its attached inbounds and pushed to Xray/remote nodes. Returns the count of successfully reset clients.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "emails": [
    "alice",
    "bob"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "affected": 2
  }
}
No links

GET
/panel/api/clients/groups
List all client groups with their member counts. Merges persisted groups (rows in client_groups, including empty placeholders) with the distinct group_name values currently set on clients. Sorted alphabetically (case-insensitive).



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "name": "customer-a",
      "clientCount": 5
    },
    {
      "name": "internal",
      "clientCount": 0
    }
  ]
}
No links

GET
/panel/api/clients/groups/{name}/emails
Return just the email list of clients that currently belong to the given group. Useful for fanning a single bulk action over an entire group without round-tripping the full client list.



Parameters
Cancel
Name	Description
name *
string
(path)
Group name (URL-encoded).

name
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "alice",
    "bob",
    "carol"
  ]
}
No links

POST
/panel/api/clients/groups/create
Create a new empty (placeholder) group. The group becomes selectable in client forms and the filter drawer even before any client is added to it. Errors if a group with the same name already exists.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "name": "customer-a"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "name": "customer-a"
  }
}
No links

POST
/panel/api/clients/groups/rename
Rename a group. The new name is applied to the client_groups row AND propagated to every matching client (both clients.group_name and the client entry inside every owning inbound's settings JSON) in a single transaction. Returns the number of clients whose label was updated.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "oldName": "customer-a",
  "newName": "tier-1"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "affected": 5
  }
}
No links

POST
/panel/api/clients/groups/delete
Remove a group. Deletes the client_groups row and clears the group label from every matching client (both clients.group_name and the inbound settings JSON). The clients themselves are NOT deleted — use /bulkDel after filtering by group for that. Returns the count of clients whose label was cleared.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "name": "customer-a"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "affected": 5
  }
}
No links

POST
/panel/api/clients/groups/resetTraffic
Reset only the group-level traffic counter shown on the groups page. Snapshots the current up/down sum of the group's members as a baseline so the group total reads zero, while leaving each client's own counters (and their quotas) untouched. No Xray restart is triggered. Creates the client_groups row if the group exists only as a derived label.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "name": "customer-a"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "name": "customer-a"
  }
}
No links

POST
/panel/api/clients/resetTraffic/{email}
Zero out a single client’s up/down counters. Re-enables the client across every attached inbound and pushes the change to Xray (or the remote node) so depleted users can connect again immediately.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email.

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/clients/updateTraffic/{email}
Manually adjust a client’s upload + download counters. Useful for migrations from external accounting systems.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email.

email
Request body

application/json
Edit Value
Schema
{
  "upload": 1073741824,
  "download": 5368709120
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/clients/ips/{email}
List source IPs that have connected with the given client’s credentials. Returns an array of "ip (timestamp)" strings.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email.

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/clients/clearIps/{email}
Reset the recorded IP list for a client.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email.

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/clients/hwids/{email}
List registered HWID devices for a client with a short fingerprint. Full hashes are not exposed.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email.

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "id": 1,
      "firstSeen": 1735000000000,
      "lastSeen": 1735100000000,
      "userAgent": "Happ/1.0",
      "deviceOs": "android",
      "osVersion": "15",
      "deviceModel": "Pixel 9",
      "fingerprint": "6ad17c93e821"
    }
  ]
}
No links

DELETE
/panel/api/clients/hwids/{email}
Clear all registered HWID devices for a client so new devices can register again.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email.

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

DELETE
/panel/api/clients/hwids/{email}/{id}
Remove a single registered HWID device by its id, freeing one slot under the HWID limit.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email.

email
id *
integer
(path)
Device id, from the list endpoint.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/clients/onlines
List the emails of currently connected clients (last seen within the heartbeat window), deduped across every node.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "user1",
    "user2"
  ]
}
No links

POST
/panel/api/clients/onlinesByGuid
Online client emails grouped by the panelGuid of the node that physically hosts each client. The local panel uses its own GUID; each node (at any depth in a chain) uses its GUID. Lets the inbounds page attribute online status to the real node instead of the intermediate one it syncs through.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "a1b2-...": [
      "user1"
    ],
    "c3d4-...": [
      "user1",
      "user2"
    ]
  }
}
No links

POST
/panel/api/clients/clientIpsByGuid
Per-client source IPs grouped by the panelGuid of the node that observed them. Lets the central panel attribute and enforce per-client IP limits using the real visitor IPs each node sees, instead of the address of the intermediate panel it syncs through.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "a1b2-...": {
      "user1": [
        {
          "ip": "1.2.3.4",
          "timestamp": 1700000000
        }
      ]
    }
  }
}
No links

POST
/panel/api/clients/activeInbounds
Inbound tags that carried traffic within the heartbeat window, grouped by the hosting node's panelGuid. Pairs with onlinesByGuid so the inbounds page only marks a multi-inbound client online on the inbounds it actually used. Nodes that do not report per-inbound activity are absent.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "a1b2-...": [
      "in-443-tcp",
      "in-8443-tcp"
    ]
  }
}
No links

POST
/panel/api/clients/lastOnline
Map of client email → last-seen unix timestamp.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "user1": 1700000000,
    "user2": 1699999000
  }
}
No links

GET
/panel/api/clients/traffic/{email}
Traffic counters for a client identified by email.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email (unique across the panel).

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "down": 2097152,
    "email": "user1",
    "enable": true,
    "expiryTime": 1735689600000,
    "id": 14825,
    "inboundId": 1,
    "lastOnline": 1735680000000,
    "lastSubFetch": 1735680000000,
    "reset": 0,
    "resetCount": 0,
    "resetDay": 0,
    "resetMax": 0,
    "subId": "i7tvdpeffi0hvvf1",
    "total": 10737418240,
    "up": 1048576,
    "uuid": "e18c9a96-71bf-48d4-933f-8b9a46d4290c"
  }
}
No links

GET
/panel/api/clients/subLinks/{subId}
Return every protocol URL (vless://, vmess://, trojan://, ss://, hysteria://, hy2://) for clients matching the subscription ID. Same result set as the configured subPath endpoint, but as a JSON array — no base64. When an inbound has streamSettings.externalProxy set, one URL is emitted per external proxy. Empty array when the subId has no enabled clients.



Parameters
Cancel
Name	Description
subId *
string
(path)
Subscription ID, taken from the client's subId field.

subId
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "vless://uuid@host:443?security=reality&...#user1",
    "vmess://eyJ2IjoyLC..."
  ]
}
No links

POST
/panel/api/clients/happLink/{id}
Generate a fresh Happ crypt5 link locally from the current client subscription URL when Happ link generation is enabled. The panel applies a resource limit of 8192 UTF-8 bytes to the source URL; this is not a Happ client maximum. Longer sources return success: false with msg: happ_source_too_long and obj: null. The source URL is not sent to a generation provider, and the result is not stored or reused.



Parameters
Cancel
Name	Description
id *
integer
(path)
Stable client record ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "encryptedLink": "happ://crypt5/example"
  }
}
No links

GET
/panel/api/clients/links/{email}
Return every URL for one client across all attached inbounds, one per advertised endpoint: the managed hosts of the inbound, else its streamSettings.externalProxy entries, else its own address. Supported protocols: vmess, vless, trojan, shadowsocks, hysteria, mtproto. Protocols without a URL form (socks, http, mixed, wireguard, dokodemo, tunnel) contribute nothing.



Parameters
Cancel
Name	Description
email *
string
(path)
Client email (unique identifier).

email
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "vless://uuid@host:443?...#user1"
  ]
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- node

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Nodes
Manage remote 3x-ui panels acting as nodes for a central panel. All endpoints under /panel/api/nodes.



GET
/panel/api/nodes/list
List every configured node with its connection details, health, and last heartbeat patch.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "activeCount": 20,
      "address": "node.example.com",
      "allowPrivateAddress": false,
      "basePath": "/",
      "clientCount": 25,
      "configDirty": false,
      "configDirtyAt": 0,
      "cpuPct": 12.5,
      "createdAt": 1700000000,
      "depletedCount": 1,
      "disabledCount": 2,
      "enable": true,
      "guid": "node-guid",
      "hasApiToken": true,
      "id": 1,
      "inboundCount": 3,
      "inboundSyncMode": "all",
      "inboundTags": [
        "in-443-tcp"
      ],
      "lastError": "",
      "lastHeartbeat": 1700000000,
      "latencyMs": 42,
      "memPct": 45.2,
      "name": "edge-1",
      "netDown": 1048576,
      "netUp": 2097152,
      "onlineCount": 5,
      "outboundTag": "direct",
      "panelVersion": "v3.x.x",
      "parentGuid": "",
      "pinnedCertSha256": "",
      "port": 2053,
      "remark": "Primary edge",
      "scheme": "https",
      "status": "online",
      "tlsVerifyMode": "verify",
      "transitive": false,
      "updatedAt": 1700003600,
      "uptimeSecs": 86400,
      "xrayError": "",
      "xrayState": "running",
      "xrayVersion": "25.10.31"
    }
  ]
}
No links

POST
/panel/api/nodes/mtls/ca
This panel's node-auth CA certificate (public, PEM) to paste into a node's mTLS trust setting. Lazily mints the CA and the master client cert on first call. Pair with setting tlsVerifyMode=mtls on the node.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "caCert": "-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----\n"
  }
}
No links

POST
/panel/api/nodes/mtls/trustCA
Set the CA certificate this panel trusts for incoming node-API client certificates (this panel acting as a node). Paste the managing panel's CA (from nodes/mtls/ca). An empty caCert disables it. A non-empty value must be a PEM certificate. Applied on the next panel restart.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "caCert": "-----BEGIN CERTIFICATE-----\n...\n-----END CERTIFICATE-----\n"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/nodes/mtls/reloadClient
Validate the stored master mTLS client credential and invalidate cached transports. Each transport closes its old idle pool and rebuilds with the rotated certificate before its next request.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/nodes/get/{id}
Fetch a single node by ID.



Parameters
Cancel
Name	Description
id *
integer
(path)
Node ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "activeCount": 20,
    "address": "node.example.com",
    "allowPrivateAddress": false,
    "basePath": "/",
    "clientCount": 25,
    "configDirty": false,
    "configDirtyAt": 0,
    "cpuPct": 12.5,
    "createdAt": 1700000000,
    "depletedCount": 1,
    "disabledCount": 2,
    "enable": true,
    "guid": "node-guid",
    "hasApiToken": true,
    "id": 1,
    "inboundCount": 3,
    "inboundSyncMode": "all",
    "inboundTags": [
      "in-443-tcp"
    ],
    "lastError": "",
    "lastHeartbeat": 1700000000,
    "latencyMs": 42,
    "memPct": 45.2,
    "name": "edge-1",
    "netDown": 1048576,
    "netUp": 2097152,
    "onlineCount": 5,
    "outboundTag": "direct",
    "panelVersion": "v3.x.x",
    "parentGuid": "",
    "pinnedCertSha256": "",
    "port": 2053,
    "remark": "Primary edge",
    "scheme": "https",
    "status": "online",
    "tlsVerifyMode": "verify",
    "transitive": false,
    "updatedAt": 1700003600,
    "uptimeSecs": 86400,
    "xrayError": "",
    "xrayState": "running",
    "xrayVersion": "25.10.31"
  }
}
No links

GET
/panel/api/nodes/webCert/{id}
Fetch a node's own web TLS certificate/key file paths (proxied to the node). Used by the inbound form's "Set Cert from Panel" so a node-assigned inbound gets paths that exist on the node, not the central panel.



Parameters
Cancel
Name	Description
id *
integer
(path)
Node ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "webCertFile": "/root/cert/example.com/fullchain.pem",
    "webKeyFile": "/root/cert/example.com/privkey.pem"
  }
}
No links

POST
/panel/api/nodes/add
Register a new remote node. Provide its URL, write-only apiToken, and optional remark / allowPrivateAddress flag. Responses expose hasApiToken only.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "name": "de-fra-1",
  "remark": "",
  "scheme": "https",
  "address": "node1.example.com",
  "port": 2053,
  "basePath": "/",
  "apiToken": "abcdef...",
  "clearApiToken": false,
  "enable": true,
  "allowPrivateAddress": false
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "activeCount": 20,
    "address": "node.example.com",
    "allowPrivateAddress": false,
    "basePath": "/",
    "clientCount": 25,
    "configDirty": false,
    "configDirtyAt": 0,
    "cpuPct": 12.5,
    "createdAt": 1700000000,
    "depletedCount": 1,
    "disabledCount": 2,
    "enable": true,
    "guid": "node-guid",
    "hasApiToken": true,
    "id": 1,
    "inboundCount": 3,
    "inboundSyncMode": "all",
    "inboundTags": [
      "in-443-tcp"
    ],
    "lastError": "",
    "lastHeartbeat": 1700000000,
    "latencyMs": 42,
    "memPct": 45.2,
    "name": "edge-1",
    "netDown": 1048576,
    "netUp": 2097152,
    "onlineCount": 5,
    "outboundTag": "direct",
    "panelVersion": "v3.x.x",
    "parentGuid": "",
    "pinnedCertSha256": "",
    "port": 2053,
    "remark": "Primary edge",
    "scheme": "https",
    "status": "online",
    "tlsVerifyMode": "verify",
    "transitive": false,
    "updatedAt": 1700003600,
    "uptimeSecs": 86400,
    "xrayError": "",
    "xrayState": "running",
    "xrayVersion": "25.10.31"
  }
}
No links

POST
/panel/api/nodes/update/{id}
Replace a node’s connection details. apiToken is write-only: omit it or send an empty string to keep the stored token; set clearApiToken=true to clear it.



Parameters
Cancel
Name	Description
id *
integer
(path)
Node ID.

id
Request body

application/json
Edit Value
Schema
{
  "name": "de-fra-1",
  "remark": "",
  "scheme": "https",
  "address": "node1.example.com",
  "port": 2053,
  "basePath": "/",
  "apiToken": "",
  "clearApiToken": false,
  "enable": true,
  "allowPrivateAddress": false
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/nodes/del/{id}
Delete a node. Inbounds bound to it are not auto-migrated.



Parameters
Cancel
Name	Description
id *
integer
(path)
Node ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/nodes/setEnable/{id}
Pause or resume traffic sync with this node.



Parameters
Cancel
Name	Description
id *
integer
(path)
Node ID.

id
Request body

application/json
Edit Value
Schema
{
  "enable": true
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/nodes/test
Probe a node without saving it. Uses the body as connection details and returns the same heartbeat snapshot a registered node would have.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "scheme": "https",
  "address": "node1.example.com",
  "port": 2053,
  "basePath": "/",
  "apiToken": "abcdef..."
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "cpuPct": 12.5,
    "error": "",
    "latencyMs": 42,
    "memPct": 45.2,
    "panelVersion": "v3.x.x",
    "status": "online",
    "uptimeSecs": 86400,
    "xrayError": "",
    "xrayState": "",
    "xrayVersion": "25.10.31"
  }
}
No links

POST
/panel/api/nodes/certFingerprint
Connect to the node over HTTPS without verifying its certificate and return the leaf certificate's SHA-256 (base64). Used by the Add/Edit Node dialog to fetch and pin a self-signed certificate. Uses the same body as /test.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "scheme": "https",
  "address": "node1.example.com",
  "port": 2053,
  "basePath": "/"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": "k3b1...base64-sha256...="
}
No links

POST
/panel/api/nodes/inbounds
Use unsaved node connection details to list the remote inbounds available for selective import.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "name": "de-fra-1",
  "scheme": "https",
  "address": "node1.example.com",
  "port": 2053,
  "basePath": "/",
  "apiToken": "abcdef..."
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "tag": "inbound-443",
      "remark": "VLESS",
      "protocol": "vless",
      "port": 443
    }
  ]
}
No links

POST
/panel/api/nodes/probe/{id}
Probe an existing node, updating its cached health state.



Parameters
Cancel
Name	Description
id *
integer
(path)
Node ID.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/nodes/updatePanel
Trigger the official panel self-updater on each given node (downloads the latest release and restarts). Only enabled, online nodes are updated; offline/disabled ones are reported as skipped. Set "dev": true to move the nodes to the rolling per-commit dev channel instead of the latest stable release. Returns a per-node result list.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "ids": [
    1,
    2,
    3
  ],
  "dev": false
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "id": 1,
      "name": "de-1",
      "ok": true
    },
    {
      "id": 2,
      "name": "fr-1",
      "ok": false,
      "error": "node is offline"
    }
  ]
}
No links

GET
/panel/api/nodes/history/{id}/{metric}/{bucket}
Aggregated metric history for a node — same shape as /server/history, scoped to one node.



Parameters
Cancel
Name	Description
id *
integer
(path)
Node ID.

id
metric *
string
(path)
cpu | mem.

metric
bucket *
integer
(path)
Bucket size in seconds. Allowed: 2, 30, 60, 120, 180, 300.

bucket
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- hosts

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Hosts
Per-inbound override endpoints. Each enabled host renders one extra subscription link/proxy with its own address/port/TLS, superseding the legacy externalProxy array. All endpoints under /panel/api/hosts.



GET
/panel/api/hosts/list
List every host across all inbounds, grouped by inbound then ordered by sort order.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "allowInsecure": false,
      "alpn": [
        ""
      ],
      "echConfigList": "",
      "excludeFromSubTypes": [
        ""
      ],
      "finalMask": "",
      "fingerprint": "",
      "groupId": "",
      "hostHeader": "",
      "hosts": [
        ""
      ],
      "inboundIds": [
        0
      ],
      "isDisabled": false,
      "isHidden": false,
      "keepSniBlank": false,
      "mihomoIpVersion": "dual",
      "mihomoX25519": false,
      "muxParams": "",
      "nodeGuids": [
        ""
      ],
      "overrideSniFromAddress": false,
      "path": "",
      "pinnedPeerCertSha256": [
        ""
      ],
      "port": 0,
      "remark": "",
      "security": "same",
      "serverDescription": "",
      "shuffleHost": false,
      "sni": "",
      "sockoptParams": "",
      "sortOrder": 0,
      "tags": [
        ""
      ],
      "verifyPeerCertByName": "",
      "vlessRoute": ""
    }
  ]
}
No links

GET
/panel/api/hosts/get/{groupId}
Fetch a single host group by Group ID.



Parameters
Cancel
Name	Description
groupId *
string
(path)
Host Group ID.

groupId
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "allowInsecure": false,
    "alpn": [
      ""
    ],
    "echConfigList": "",
    "excludeFromSubTypes": [
      ""
    ],
    "finalMask": "",
    "fingerprint": "",
    "groupId": "",
    "hostHeader": "",
    "hosts": [
      ""
    ],
    "inboundIds": [
      0
    ],
    "isDisabled": false,
    "isHidden": false,
    "keepSniBlank": false,
    "mihomoIpVersion": "dual",
    "mihomoX25519": false,
    "muxParams": "",
    "nodeGuids": [
      ""
    ],
    "overrideSniFromAddress": false,
    "path": "",
    "pinnedPeerCertSha256": [
      ""
    ],
    "port": 0,
    "remark": "",
    "security": "same",
    "serverDescription": "",
    "shuffleHost": false,
    "sni": "",
    "sockoptParams": "",
    "sortOrder": 0,
    "tags": [
      ""
    ],
    "verifyPeerCertByName": "",
    "vlessRoute": ""
  }
}
No links

GET
/panel/api/hosts/byInbound/{inboundId}
Fetch one inbound's hosts, grouped by host group.



Parameters
Cancel
Name	Description
inboundId *
integer
(path)
Inbound ID.

inboundId
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "allowInsecure": false,
      "alpn": [
        ""
      ],
      "echConfigList": "",
      "excludeFromSubTypes": [
        ""
      ],
      "finalMask": "",
      "fingerprint": "",
      "groupId": "",
      "hostHeader": "",
      "hosts": [
        ""
      ],
      "inboundIds": [
        0
      ],
      "isDisabled": false,
      "isHidden": false,
      "keepSniBlank": false,
      "mihomoIpVersion": "dual",
      "mihomoX25519": false,
      "muxParams": "",
      "nodeGuids": [
        ""
      ],
      "overrideSniFromAddress": false,
      "path": "",
      "pinnedPeerCertSha256": [
        ""
      ],
      "port": 0,
      "remark": "",
      "security": "same",
      "serverDescription": "",
      "shuffleHost": false,
      "sni": "",
      "sockoptParams": "",
      "sortOrder": 0,
      "tags": [
        ""
      ],
      "verifyPeerCertByName": "",
      "vlessRoute": ""
    }
  ]
}
No links

GET
/panel/api/hosts/tags
Distinct, sorted set of tags used across all hosts.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    "CDN",
    "EU",
    "FAST"
  ]
}
No links

POST
/panel/api/hosts/add
Create a host group on inbounds.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "inboundIds": [
    1
  ],
  "remark": "cdn-front",
  "hosts": [
    "cdn.example.com"
  ],
  "port": 8443,
  "security": "same",
  "tags": [
    "CDN"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "address": "cdn.example.com",
      "allowInsecure": false,
      "alpn": [
        ""
      ],
      "createdAt": 0,
      "echConfigList": "",
      "excludeFromSubTypes": [
        ""
      ],
      "finalMask": "",
      "fingerprint": "",
      "groupId": "",
      "hostHeader": "",
      "id": 1,
      "inboundId": 1,
      "isDisabled": false,
      "isHidden": false,
      "keepSniBlank": false,
      "mihomoIpVersion": "dual",
      "mihomoX25519": false,
      "muxParams": null,
      "nodeGuids": [
        ""
      ],
      "overrideSniFromAddress": false,
      "path": "",
      "pinnedPeerCertSha256": [
        ""
      ],
      "port": 8443,
      "remark": "cdn-front",
      "security": "same",
      "serverDescription": "",
      "shuffleHost": false,
      "sni": "",
      "sockoptParams": null,
      "sortOrder": 0,
      "tags": [
        ""
      ],
      "updatedAt": 0,
      "verifyPeerCertByName": "",
      "vlessRoute": "443"
    }
  ]
}
No links

POST
/panel/api/hosts/update/{groupId}
Replace a host group’s content.



Parameters
Cancel
Name	Description
groupId *
string
(path)
Host Group ID.

groupId
Request body

application/json
Edit Value
Schema
{
  "inboundIds": [
    1
  ],
  "remark": "cdn-front",
  "hosts": [
    "cdn.example.com"
  ],
  "port": 8443,
  "security": "same",
  "tags": [
    "CDN"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "address": "cdn.example.com",
      "allowInsecure": false,
      "alpn": [
        ""
      ],
      "createdAt": 0,
      "echConfigList": "",
      "excludeFromSubTypes": [
        ""
      ],
      "finalMask": "",
      "fingerprint": "",
      "groupId": "",
      "hostHeader": "",
      "id": 1,
      "inboundId": 1,
      "isDisabled": false,
      "isHidden": false,
      "keepSniBlank": false,
      "mihomoIpVersion": "dual",
      "mihomoX25519": false,
      "muxParams": null,
      "nodeGuids": [
        ""
      ],
      "overrideSniFromAddress": false,
      "path": "",
      "pinnedPeerCertSha256": [
        ""
      ],
      "port": 8443,
      "remark": "cdn-front",
      "security": "same",
      "serverDescription": "",
      "shuffleHost": false,
      "sni": "",
      "sockoptParams": null,
      "sortOrder": 0,
      "tags": [
        ""
      ],
      "updatedAt": 0,
      "verifyPeerCertByName": "",
      "vlessRoute": "443"
    }
  ]
}
No links

POST
/panel/api/hosts/del/{groupId}
Delete a host group.



Parameters
Cancel
Name	Description
groupId *
string
(path)
Host Group ID.

groupId
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/hosts/setEnable/{groupId}
Enable or disable a host group.



Parameters
Cancel
Name	Description
groupId *
string
(path)
Host Group ID.

groupId
Request body

application/json
Edit Value
Schema
{
  "enable": true
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/hosts/reorder
Set host group sort order by the position of each groupId in the array.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "ids": [
    "abc-123",
    "def-456"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/hosts/bulk/add
Add a host group to inbounds (same as /add).



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "inboundIds": [
    1,
    2
  ],
  "hosts": [
    "cdn.example.com",
    "cdn2.example.com:443"
  ],
  "remark": "Cloudflare CDN",
  "port": 0,
  "security": "same",
  "isDisabled": false
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "address": "cdn.example.com",
      "allowInsecure": false,
      "alpn": [
        ""
      ],
      "createdAt": 0,
      "echConfigList": "",
      "excludeFromSubTypes": [
        ""
      ],
      "finalMask": "",
      "fingerprint": "",
      "groupId": "",
      "hostHeader": "",
      "id": 1,
      "inboundId": 1,
      "isDisabled": false,
      "isHidden": false,
      "keepSniBlank": false,
      "mihomoIpVersion": "dual",
      "mihomoX25519": false,
      "muxParams": null,
      "nodeGuids": [
        ""
      ],
      "overrideSniFromAddress": false,
      "path": "",
      "pinnedPeerCertSha256": [
        ""
      ],
      "port": 8443,
      "remark": "cdn-front",
      "security": "same",
      "serverDescription": "",
      "shuffleHost": false,
      "sni": "",
      "sockoptParams": null,
      "sortOrder": 0,
      "tags": [
        ""
      ],
      "updatedAt": 0,
      "verifyPeerCertByName": "",
      "vlessRoute": "443"
    }
  ]
}
No links

POST
/panel/api/hosts/bulk/setEnable
Enable or disable many host groups in one call.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "ids": [
    "abc-123",
    "def-456"
  ],
  "enable": false
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/hosts/bulk/del
Delete many host groups in one call.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "ids": [
    "abc-123",
    "def-456"
  ]
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- backup

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Backup
Operations that interact with the configured Telegram bot.



POST
/panel/api/backuptotgbot
Send a fresh DB backup to every Telegram chat configured as an admin recipient. No body, no params.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- setting

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Settings
Panel configuration and user credentials. All endpoints live under /panel/api/setting and require a logged-in session or Bearer token.



POST
/panel/api/setting/all
Return every panel setting: web server, Telegram bot, subscription, security, LDAP. The full JSON blob that the Settings page edits.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/setting/defaultSettings
Return the computed default settings based on the request host. Useful to preview what a fresh install would use.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/setting/factoryDefaults
Return the shipped (factory) default value per browser-safe setting key, so clients can tell a stored value apart from the default it would fall back to. Per-install material (secret, panelGuid, mTLS keys) and credential fields are never included.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/setting/update
Persist every setting at once. The body mirrors the shape returned by /all. Invalid values (bad ports, missing cert pairs, etc.) are rejected before write.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/setting/validateRegex
Validate any regular expression with the backend Go RE2 compiler without saving it.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "regex": "(?m)^general-purpose$"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": ""
}
No links

POST
/panel/api/setting/updateUser
Change the panel admin username and password. Requires the current credentials for verification. The session is refreshed with the new values on success.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "oldUsername": "admin",
  "oldPassword": "admin",
  "newUsername": "newadmin",
  "newPassword": "newpass"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/setting/restartPanel
Restart the entire 3x-ui process after a 3-second grace period. The connection drops immediately; the panel comes back online ~5-10 seconds later.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/setting/testSmtp
Test SMTP connection with stage-by-stage reporting (connect, auth, send). Returns structured result with stage and message.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "stage": "send",
  "msg": "Test email sent successfully"
}
No links

POST
/panel/api/setting/testTgBot
Test Telegram bot connection by sending a test message to the configured chat.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "Test message sent to Telegram"
}
No links

POST
/panel/api/setting/testDiscord
Test Discord bot connection by sending a test embed to the configured channel.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "Test notification sent successfully"
}
No links

GET
/panel/api/setting/getDefaultJsonConfig
Return the built-in default Xray JSON config template that ships with this panel version.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- api tokens

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
API Tokens
Manage scoped Bearer tokens for programmatic auth. Tokens grant admin, monitor, or node-sync access, may expire, and are stored as SHA-256 hashes. The plaintext is returned only once at creation.



GET
/panel/api/setting/apiTokens
List every API token, enabled or not. The token value is never returned — only metadata.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": [
    {
      "id": 1,
      "name": "default",
      "enabled": true,
      "createdAt": 1736000000
    }
  ]
}
No links

POST
/panel/api/setting/apiTokens/create
Mint a scoped API token. The server-generated plaintext is returned only once and stored as a hash.



Parameters
Cancel
No parameters

Request body

application/json
Edit Value
Schema
{
  "name": "central-panel-a",
  "scope": "node-sync",
  "expiresAt": 1798761600000
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "createdAt": 1736000000,
    "enabled": true,
    "expiresAt": 0,
    "id": 2,
    "name": "central-panel-a",
    "scope": "admin",
    "token": "new-token-string"
  }
}
No links
400	
Error response

Media type

application/json
Example Value
Schema
{
  "success": false,
  "msg": "a token with that name already exists"
}
No links

POST
/panel/api/setting/apiTokens/delete/{id}
Permanently delete a token. Any caller using it stops authenticating immediately.



Parameters
Cancel
Name	Description
id *
integer
(path)
Token row ID.

id
Request body

application/json
Edit Value
Schema
{
  "expectedScope": "node-sync"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

POST
/panel/api/setting/apiTokens/setEnabled/{id}
Toggle a token enabled/disabled without deleting it. Disabled tokens are rejected by checkAPIAuth on the next request.



Parameters
Cancel
Name	Description
id *
integer
(path)
Token row ID.

id
Request body

application/json
Edit Value
Schema
{
  "enabled": false,
  "expectedScope": "node-sync"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- xray settigs

3X-UI Panel API
WebSocket events
3X-UI Panel API
 3.x 
OAS 3.0
/qwees_administrator/panel/api/openapi.json
Programmatic interface to a 3X-UI panel. Authenticate either by logging in (cookie) or with an API token from Settings → Security → API Token (Bearer). All endpoints under /panel/api/* honour both modes — an API token is a full-admin credential, so treat it like the panel password.

Servers

/qwees_administrator - Current panel

Authorize
Authentication
Inbounds
Server
Clients
Nodes
Hosts
Backup
Settings
API Tokens
Xray Settings
Subscription Balancers
Subscription Server
WebSocket
Xray Settings
Xray configuration template, outbound management, Warp/Nord/PIA integration, and config testing. All endpoints under /panel/api/xray.



POST
/panel/api/xray/
Return the Xray config template (JSON string), available inbound tags, client reverse tags, and the configured outbound test URL in one response.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "obj": {
    "xraySetting": "{...raw xray config...}",
    "inboundTags": "[\"in-443-tcp\"]",
    "clientReverseTags": "[]",
    "outboundTestUrl": "https://www.google.com/generate_204"
  }
}
No links

GET
/panel/api/xray/getDefaultJsonConfig
Return the built-in default Xray config shipped with the panel (identical to /panel/api/setting/getDefaultJsonConfig).



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/xray/getOutboundsTraffic
Return traffic statistics for every outbound. Each outbound shows up/down/total counters.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/xray/getXrayResult
Return the most recent Xray process stdout/stderr output. Useful to check for startup errors or runtime warnings.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/update
Save the Xray JSON config template and optionally the outbound test URL. Both are sent as form fields.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
xraySetting *
string
Full Xray JSON config template.

string
outboundTestUrl
string
URL used for outbound reachability tests. Defaults to https://www.google.com/generate_204.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/warp/{action}
Manage Cloudflare Warp integration. The action parameter selects the operation.



Parameters
Cancel
Reset
Name	Description
action *
string
(path)
data — return Warp stats. del — delete Warp data. config — return current config. reg — register (sends keys). changeIp — rotate the endpoint. license — set a Warp+ key. interval — set automatic rotation in hours.

action
Request body

application/x-www-form-urlencoded
privateKey
string
Required when action=reg.

string
Send empty value
publicKey
string
Required when action=reg.

string
Send empty value
license
string
Required when action=license.

string
Send empty value
interval
integer
Non-negative hours between automatic rotations. Required when action=interval; 0 disables rotation.

0
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/nord/{action}
Manage NordVPN integration. The action parameter selects the operation.



Parameters
Cancel
Name	Description
action *
string
(path)
countries — list available countries. servers — list servers in a country (sends countryId). reg — get NordVPN credentials (sends token). setKey — store NordVPN API key (sends key). data — return current NordVPN connection data. del — delete NordVPN data.

action
Request body

application/x-www-form-urlencoded
countryId
string
Required when action=servers.

string
Send empty value
token
string
Required when action=reg.

string
Send empty value
key
string
Required when action=setKey.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/pia/{action}
Manage PIA WireGuard integration. The action parameter selects the operation.



Parameters
Cancel
Name	Description
action *
string
(path)
countries — list available countries from the signed PIA server list. servers — list regions and WireGuard servers in a country (sends countryCode). reg — sign in with a PIA username and password (sends username, password). data — return the signed-in account hint. del — delete stored PIA credentials. addKey — register a WireGuard key with the selected server (sends hostname) and return fields to build the outbound.

action
Request body

application/x-www-form-urlencoded
username
string
Required when action=reg.

string
Send empty value
password
string
Required when action=reg.

string
Send empty value
countryCode
string
Required when action=servers.

string
Send empty value
hostname
string
Required when action=addKey.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/resetOutboundsTraffic
Reset traffic counters for a specific outbound by tag.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
tag *
string
Outbound tag to reset (e.g. "proxy", "direct").

string
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/testOutbound
Test an outbound configuration. Sends the outbound JSON (required), optionally all outbounds (to resolve sockopt.dialerProxy dependencies), and a mode flag.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
outbound *
string
JSON-encoded single outbound to test (required).

string
allOutbounds
string
JSON array of all outbounds — used to resolve dialerProxy chains.

string
Send empty value
mode
string
"tcp" for a fast dial-only probe (parallel-safe), "real" for a real-delay probe whose delay is the full request time including tunnel establishment. Default/empty uses a full HTTP probe reporting the warm per-request round-trip. Both HTTP variants run through a temp xray instance.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/testOutbounds
Test a batch of outbounds (max 50) through one shared temp xray instance. Returns an array of results in input order, each with the outbound tag, delay, HTTP status and a connect/TLS/TTFB timing breakdown.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
outbounds *
string
JSON array of outbound configs to test (required).

string
allOutbounds
string
JSON array of all outbounds — used to resolve dialerProxy chains.

string
Send empty value
mode
string
"tcp" for fast dial-only probes (UDP-transport outbounds are still probed over HTTP), "real" for real-delay probes whose delay is the full request time including tunnel establishment. Default/empty routes an HTTP request through each outbound and reports the warm per-request round-trip.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/balancerStatus
Live state of routing balancers in the running core (RoutingService.GetBalancerInfo): current override and the targets the strategy prefers. Returns a map keyed by balancer tag.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
tags *
string
Comma-separated balancer tags to query (e.g. "b1,b2").

string
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/balancerOverride
Force a balancer in the running core to always pick one outbound (RoutingService.OverrideBalancerTarget). Applied live without a restart; cleared automatically when Xray restarts.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
tag *
string
Balancer tag (required).

string
target
string
Outbound tag to force. Empty clears the override and returns control to the strategy.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/routeTest
Ask the running core which outbound its router would pick for a synthetic connection (RoutingService.TestRoute). No traffic is sent.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
Edit Value
Schema
{
  "domain": "string",
  "ip": "string",
  "port": 0,
  "network": "string",
  "inboundTag": "string",
  "protocol": "string",
  "email": "string"
}
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/xray/geodata/files
List the geo databases (.dat files) in the Xray asset folder, with the layout detected from their contents, size, modification time and category count. A database that fails to parse is still listed, with the reason in "error".



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/xray/geodata/categories
One page of a database's categories, each with its entry count and the attributes its domains carry (e.g. "ads", "cn").



Parameters
Cancel
Name	Description
file *
string
(query)
Database file name inside the asset folder, e.g. geosite.dat (required).

file
q
string
(query)
Case-insensitive substring filter on the category code.

q
offset
integer
(query)
Rows to skip. Defaults to 0.

offset
limit
integer
(query)
Rows to return, capped at 500. Omit it to return every category — the index is small and the panel filters it client-side.

limit
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/xray/geodata/entries
One page of the rules inside a category — domain rules typed as domain/full/keyword/regexp for geosite databases, CIDRs for geoip ones.



Parameters
Cancel
Name	Description
file *
string
(query)
Database file name inside the asset folder (required).

file
code *
string
(query)
Category code, case-insensitive, e.g. google (required).

code
q
string
(query)
Case-insensitive substring filter on the rule value.

q
offset
integer
(query)
Rows to skip. Defaults to 0.

offset
limit
integer
(query)
Rows to return, capped at 500. Defaults to the cap.

limit
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/geodata/validate
Check routing tokens against the databases on disk and return only the ones that do not resolve. Plain domains and CIDRs are ignored. Each issue carries a reason: syntax, fileMissing or categoryMissing.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
tokens *
string
Comma-separated routing tokens, e.g. "geosite:google,geosite:blabla". Max 500 per request.

string
kind
string
"ip" to parse the tokens as IP rules (geoip:, ext-ip:, leading !). Anything else parses them as domain rules (geosite:, ext-site:).

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

GET
/panel/api/xray/outbound-subs
List all outbound subscriptions (remote URLs that supply additional outbounds), newest first.



Parameters
Cancel
No parameters

Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/outbound-subs
Create an outbound subscription. The URL is fetched, parsed into outbounds with stable tags, and merged additively into the running Xray config.



Parameters
Cancel
Reset
No parameters

Request body

application/x-www-form-urlencoded
remark
string
Optional display label.

string
Send empty value
url *
string
Subscription URL (required). Must be a public http(s) address; private/internal targets are blocked unless allowPrivate is true.

string
tagPrefix
string
Prefix for generated outbound tags. Defaults to the lowest free "sub-" prefix.

string
Send empty value
userAgent
string
Custom User-Agent sent when fetching this subscription. Defaults to "3x-ui-outbound-sub/1.0".

3x-ui-outbound-sub/1.0
Send empty value
updateInterval
integer
Seconds between auto-refreshes. Default 600.

600
Send empty value
enabled
boolean
Whether the subscription is active. Default true.


true
Send empty value
allowPrivate
boolean
Allow the URL to point at a private/internal/loopback address. Default false.


false
Send empty value
allowInsecure
boolean
Skip TLS certificate verification when fetching the subscription's URL. Default false.


false
Send empty value
prepend
boolean
Place this subscription's outbounds before the manual template outbounds. Default false.


false
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/outbound-subs/{id}
Update an existing outbound subscription by id. Accepts the same form fields as create.



Parameters
Cancel
Reset
Name	Description
id *
integer
(path)
Subscription id.

id
Request body

application/x-www-form-urlencoded
remark
string
Optional display label.

string
Send empty value
url *
string
Subscription URL (required). Must be a public http(s) address; private/internal targets are blocked unless allowPrivate is true.

string
tagPrefix
string
Prefix for generated outbound tags. Defaults to the lowest free "sub-" prefix.

string
Send empty value
userAgent
string
Custom User-Agent sent when fetching this subscription. Defaults to "3x-ui-outbound-sub/1.0".

3x-ui-outbound-sub/1.0
Send empty value
updateInterval
integer
Seconds between auto-refreshes. Default 600.

600
Send empty value
enabled
boolean
Whether the subscription is active. Default true.


true
Send empty value
allowPrivate
boolean
Allow the URL to point at a private/internal/loopback address. Default false.


false
Send empty value
allowInsecure
boolean
Skip TLS certificate verification when fetching the subscription's URL. Default false.


false
Send empty value
prepend
boolean
Place this subscription's outbounds before the manual template outbounds. Default false.


false
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

DELETE
/panel/api/xray/outbound-subs/{id}
Delete an outbound subscription by id.



Parameters
Cancel
Name	Description
id *
integer
(path)
Subscription id.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/outbound-subs/{id}/del
Delete an outbound subscription by id (POST alias of DELETE for clients that cannot send DELETE).



Parameters
Cancel
Name	Description
id *
integer
(path)
Subscription id.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/outbound-subs/{id}/refresh
Force an immediate re-fetch of the subscription and return the parsed outbounds. Signals Xray to reload.



Parameters
Cancel
Name	Description
id *
integer
(path)
Subscription id.

id
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/outbound-subs/{id}/move
Reorder a subscription one step up or down in priority (controls its position in the merged outbounds).



Parameters
Cancel
Name	Description
id *
integer
(path)
Subscription id.

id
Request body

application/x-www-form-urlencoded
dir
string
"up" to raise priority, anything else to lower it.

string
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

POST
/panel/api/xray/outbound-subs/parse
Preview a subscription URL: fetch and parse it into outbounds without persisting anything.



Parameters
Cancel
No parameters

Request body

application/x-www-form-urlencoded
url *
string
Subscription URL to preview (required).

string
userAgent
string
Custom User-Agent sent while fetching the preview.

string
Send empty value
allowPrivate
boolean
Allow a private/internal/loopback URL. Default false.


true
Send empty value
allowInsecure
boolean
Skip TLS certificate verification. Default false.


true
Send empty value
Execute
Responses
Code	Description	Links
200	
Successful response

Media type

application/json
Controls Accept header.
Example Value
Schema
{
  "success": true,
  "msg": "string",
  "obj": "string"
}
No links

Schemas
AllSetting
AllSettingView
AmneziaWGLogs
ApiToken
ApiTokenView
Client
ClientInbound
ClientPageResponse
ClientRecord
ClientReverse
ClientSlim
ClientTraffic
ClientsSummary
FallbackParentInfo
GeoCategory
GeoCategoryPage
GeoEntry
GeoEntryPage
GeoFile
GeodataTokenIssue
HappLinkResult
HistoryOfSeeders
Host
HostGroup
HwidSlotStatus
Inbound
InboundClientIps
InboundFallback
InboundOption
InboundTrafficSummary
LogEntry
MLDSA65Response
MLKEM768Response
Msg
NewUUIDResponse
Node
NodeMutationRequest
NodeView
OutboundTraffics
PanelUpdateStatus
PeerActivity
ProbeResultUI
RealityScanResult
ServerSettings
Setting
SubBalancer
Traffic
TuicClientSettings
TuicServerSettings
User
WebSocketEnvelope

- 
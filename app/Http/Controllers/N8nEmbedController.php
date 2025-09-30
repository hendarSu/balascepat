<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use App\Models\NotificationChannel;

class N8nEmbedController extends Controller
{
    public function __invoke(Request $request, string $path = '')
    {
        $channel = NotificationChannel::forCurrentUser()->ofType('n8n')->firstOrFail();

        $base = rtrim((string) $channel->base_url, '/');
        $targetUrl = $base . '/' . ltrim($path, '/');

        $method = strtoupper($request->getMethod());
        $headers = [];
        // Start with incoming headers (except hop-by-hop and restricted)
        foreach ($request->headers->all() as $key => $values) {
            $name = strtolower($key);
            if (in_array($name, ['host','content-length','accept-encoding','connection','upgrade-insecure-requests'])) {
                continue;
            }
            // Normalize capitalization of common headers
            $passName = implode('-', array_map('ucfirst', explode('-', $name)));
            $headers[$passName] = implode(', ', $values);
        }
        // Ensure cookies passed (overrides any)
        if ($request->headers->has('cookie')) {
            $headers['Cookie'] = $request->headers->get('cookie');
        }
        // Inject API key header if configured, but avoid for /rest/* where UI expects session auth
        $isRest = str_starts_with(ltrim($path, '/'), 'rest/');
        if (!$isRest && $channel->headers_key && $channel->headers_value) {
            $headers[$channel->headers_key] = $channel->headers_value;
        }
        // Align Origin/Referer to upstream origin for CSRF-friendly behavior
        $origin = preg_replace('#^(https?://[^/]+).*$#', '$1', $base);
        $headers['Origin'] = $origin;
        $headers['Referer'] = rtrim($base, '/').'/';

        // Forward request to N8N
        $options = [
            'query' => $request->query(),
        ];
        if (!in_array($method, ['GET', 'HEAD'])) {
            $options['body'] = $request->getContent();
        }

        $client = Http::withHeaders($headers);
        $pendingSetCookies = [];
        try {
            $resp = $client->send($method, $targetUrl, $options);
        } catch (\Throwable $e) {
            return response('Upstream error: ' . $e->getMessage(), 502);
        }

        // If unauthorized and credentials are available, attempt background login then retry once
        $password = $channel->getDecryptedN8nPassword();
        if ($resp->status() === 401 && $channel->n8n_username && $password && !str_starts_with(ltrim($path, '/'), 'rest/login')) {
            try {
                $loginHeaders = [
                    'Accept' => 'application/json, text/plain, */*',
                    'Content-Type' => 'application/json',
                    'Origin' => preg_replace('#^(https?://[^/]+).*$#', '$1', $base),
                    'Referer' => rtrim($base, '/').'/',
                    'X-Requested-With' => 'XMLHttpRequest',
                ];
                // Preload CSRF cookies if required
                $preCookies = [];
                $csrfToken = null;
                $preCandidates = ['/rest/login', '/rest/csrf', '/rest/csrf-token', '/rest/csrfToken'];
                foreach ($preCandidates as $c) {
                    try {
                        $pre = Http::withHeaders($loginHeaders)->get(rtrim($base, '/').$c);
                        // collect cookies
                        $preHeadersAll = $pre->headers();
                        $preSet = [];
                        if (isset($preHeadersAll['Set-Cookie'])) {
                            $preSet = $preHeadersAll['Set-Cookie'];
                        } elseif ($pre->header('Set-Cookie')) {
                            $h = $pre->header('Set-Cookie');
                            $preSet = is_array($h) ? $h : [$h];
                        }
                        foreach ($preSet as $sc) {
                            if (preg_match('/^([^=;]+)=([^;]*)/', $sc, $m)) {
                                $preCookies[$m[1]] = $m[2];
                                if (!$csrfToken && preg_match('/csrf|xsrf/i', $m[1])) {
                                    $csrfToken = $m[2];
                                }
                            }
                        }
                        if (count($preSet)) {
                            // collect to set on client later
                            $pendingSetCookies = array_merge($pendingSetCookies, $preSet);
                        }
                        if ($csrfToken) {
                            break;
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                // Build cookie header for login attempt
                $loginCookieHeader = '';
                if (count($preCookies)) {
                    $loginCookieHeader = implode('; ', array_map(fn($k,$v)=>$k.'='.$v, array_keys($preCookies), $preCookies));
                }
                if ($loginCookieHeader) {
                    $loginHeaders['Cookie'] = $loginCookieHeader;
                }
                if ($csrfToken) {
                    $loginHeaders['X-CSRF-Token'] = $csrfToken;
                    $loginHeaders['X-XSRF-TOKEN'] = $csrfToken;
                }

                $loginResp = Http::withHeaders($loginHeaders)
                    ->asJson()
                    ->post(rtrim($base, '/').'/rest/login', [
                        'email' => $channel->n8n_username,
                        'password' => $password,
                    ]);

                if ($loginResp->successful()) {
                    // Merge upstream cookies from login into Cookie header
                    $loginHeadersAll = $loginResp->headers();
                    $loginSetCookies = [];
                    if (isset($loginHeadersAll['Set-Cookie'])) {
                        $loginSetCookies = $loginHeadersAll['Set-Cookie'];
                    } elseif ($loginResp->header('Set-Cookie')) {
                        $h = $loginResp->header('Set-Cookie');
                        $loginSetCookies = is_array($h) ? $h : [$h];
                    }
                    if (count($loginSetCookies)) {
                        $pendingSetCookies = array_merge($pendingSetCookies, $loginSetCookies);
                    }
                    // Build Cookie header string
                    $cookiePairs = [];
                    foreach ($loginSetCookies as $sc) {
                        if (preg_match('/^([^=;]+)=([^;]*)/', $sc, $m)) {
                            $cookiePairs[$m[1]] = $m[2];
                        }
                    }
                    // Include existing cookies too
                    if ($request->headers->has('cookie')) {
                        foreach (explode(';', $request->headers->get('cookie')) as $kv) {
                            if (strpos($kv, '=') !== false) {
                                [$k,$v] = array_map('trim', explode('=', $kv, 2));
                                if ($k !== '') $cookiePairs[$k] = $v;
                            }
                        }
                    }
                    if (count($cookiePairs)) {
                        $headers['Cookie'] = implode('; ', array_map(fn($k,$v)=>$k.'='.$v, array_keys($cookiePairs), $cookiePairs));
                    }
                    // Retry original request once with updated cookies
                    $resp = Http::withHeaders($headers)->send($method, $targetUrl, $options);
                }
            } catch (\Throwable $e) {
                // Ignore login failure; fall through with original 401 response
            }
        }

        $body = $resp->body();
        $status = $resp->status();
        $contentType = $resp->header('Content-Type');

        // If HTML, rewrite root-relative URLs and inject <base> to keep resource paths under /n8n/embed
        if ($contentType && str_contains($contentType, 'text/html')) {
            $prefix = route('n8n.embed');
            // Ensure trailing slash for base href
            if (!str_ends_with($prefix, '/')) {
                $prefix .= '/';
            }
            $embedPrefix = $prefix; // used in injected script
            $script = '<script>(function(){var P="' . addslashes($embedPrefix) . '";function r(u){try{return (typeof u==="string"&&u.startsWith("/"))?P+u.replace(/^\\\//,""):u}catch(e){return u}}var of=window.fetch;window.fetch=function(i,n){try{if(typeof i==="string"){i=r(i)}else if(i&&i.url){i=new Request(r(i.url),i)}}catch(e){}return of.call(this,i,n)};var xo=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(m,u){try{arguments[1]=r(u)}catch(e){}return xo.apply(this,arguments)};})();</script>';
            // Inject base tag after <head>
            $body = preg_replace(
                '/<head(.*?)>/i',
                '<head$1><base href="' . addcslashes($prefix, '\\"') . '">' . $script,
                $body,
                1
            );
            // Rewrite root-relative links src|href|action starting with "/"
            $patterns = [
                '/(src|href|action)=(\"|\')\/(?!\/)/i',
            ];
            $replacements = [
                '$1=$2' . addcslashes($prefix, '\\"'),
            ];
            $body = preg_replace($patterns, $replacements, $body);
        }

        // Pass-through body and status
        $response = response($body, $status);

        // Copy content-type if present
        if ($contentType) {
            $response->header('Content-Type', $contentType);
        }

        // Rewrite and pass Set-Cookie headers from upstream to client for this path
        // First, expose any login/CSRF cookies accumulated
        $headersAll = $resp->headers();
        $setCookies = [];
        if (!empty($pendingSetCookies)) {
            $setCookies = array_merge($setCookies, $pendingSetCookies);
        }
        if (isset($headersAll['Set-Cookie'])) {
            $setCookies = array_merge($setCookies, $headersAll['Set-Cookie']);
        } elseif ($resp->header('Set-Cookie')) {
            $h = $resp->header('Set-Cookie');
            $setCookies = array_merge($setCookies, is_array($h) ? $h : [$h]);
        }
        $embedPath = parse_url(route('n8n.embed'), PHP_URL_PATH) ?: '/n8n/embed';
        foreach ($setCookies as $cookieLine) {
            $c = $cookieLine;
            // Remove Domain attribute to scope to current host
            $c = preg_replace('/;\s*Domain=[^;]*/i', '', $c);
            // Force Path to embed path
            if (preg_match('/;\s*Path=[^;]*/i', $c)) {
                $c = preg_replace('/;\s*Path=[^;]*/i', '; Path=' . $embedPath, $c);
            } else {
                $c .= '; Path=' . $embedPath;
            }
            // In dev over http, drop Secure to allow cookies
            if (!$request->isSecure()) {
                $c = preg_replace('/;\s*Secure/i', '', $c);
            }
            // Re-add header without replacing previous
            $response->headers->set('Set-Cookie', $c, false);
        }

        // Rewrite Location header on redirects to stay within proxy
        $location = $resp->header('Location');
        if ($location) {
            $proxiedBase = rtrim(route('n8n.embed'), '/');
            // If upstream location is absolute to N8N origin, rewrite to proxy
            if (preg_match('#^https?://#i', $location)) {
                // Convert absolute N8N URLs to proxied path
                if (str_starts_with($location, rtrim($base,'/').'/')) {
                    $location = $proxiedBase . '/' . ltrim(substr($location, strlen(rtrim($base,'/').'/')), '/');
                }
            } else {
                // Root-relative or relative
                $location = $proxiedBase . '/' . ltrim($location, '/');
            }
            $response->headers->set('Location', $location);
        }

        // Strip frame-blocking headers
        $response->headers->remove('X-Frame-Options');
        $response->headers->remove('Content-Security-Policy');

        // Relax frame-ancestors for this proxied response
        $response->header('Content-Security-Policy', "frame-ancestors 'self'");

        return $response;
    }
}

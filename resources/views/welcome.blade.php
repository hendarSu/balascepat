<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Balas Cepat</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles: embedded Tailwind subset from Laravel welcome -->
        <style>
            /*! tailwindcss v4.0.14 | MIT License | https://tailwindcss.com */
            @layer theme{:root,:host{--font-sans:ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-mono:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;--color-green-600:oklch(.627 .194 149.214);--color-gray-900:oklch(.21 .034 264.665);--color-zinc-50:oklch(.985 0 0);--color-zinc-200:oklch(.92 .004 286.32);--color-zinc-400:oklch(.705 .015 286.067);--color-zinc-500:oklch(.552 .016 285.938);--color-zinc-600:oklch(.442 .017 285.786);--color-zinc-700:oklch(.37 .013 285.805);--color-zinc-800:oklch(.274 .006 286.033);--color-zinc-900:oklch(.21 .006 285.885);--color-neutral-100:oklch(.97 0 0);--color-neutral-200:oklch(.922 0 0);--color-neutral-700:oklch(.371 0 0);--color-neutral-800:oklch(.269 0 0);--color-neutral-900:oklch(.205 0 0);--color-neutral-950:oklch(.145 0 0);--color-black:#000;--color-white:#fff;--spacing:.25rem;--text-sm:.875rem;--text-lg:1.125rem;--font-weight-medium:500;--font-weight-semibold:600;--radius-md:.375rem;--radius-lg:.5rem;--default-font-family:var(--font-sans)}}@layer base{*,:after,:before,::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0}html,:host{-webkit-text-size-adjust:100%;tab-size:4;line-height:1.5;font-family:var(--default-font-family,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji");-webkit-tap-highlight-color:transparent}body{line-height:inherit}a{color:inherit;text-decoration:inherit}img,svg{display:block;vertical-align:middle}button,input,select,textarea{font:inherit;color:inherit;background-color:#0000;border-radius:0}img,video{max-width:100%;height:auto}}
            .shadow-sm{box-shadow:0 1px 2px 0 rgb(0 0 0 / .05)}
            .ring-1{box-shadow:0 0 0 1px rgb(0 0 0 / .05) inset}
        </style>
    </head>
    <body class="antialiased bg-neutral-100 text-neutral-800 selection:bg-green-600 selection:text-white">
        <header class="flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-3 text-neutral-900">
                <!-- Logo (chat bubble + lightning) -->
                <svg width="28" height="28" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                    <path d="M24 6c9.389 0 17 6.716 17 15 0 8.284-7.611 15-17 15-1.97 0-3.867-.286-5.636-.818L8 42l2.723-7.308C8.43 31.997 7 29.14 7 26 7 17.716 14.611 6 24 6Z" fill="#10B981"/>
                    <path d="M26 12l-5 9h6l-3 8 10-11h-7l4-6h-5Z" fill="#fff"/>
                </svg>
                <span class="font-semibold tracking-tight">Balas Cepat</span>
            </div>
            <nav class="text-sm flex items-center gap-4">
                <a class="text-neutral-600 hover:text-neutral-900" href="#docs">Docs</a>
                <a class="text-neutral-600 hover:text-neutral-900" href="#fitur">Fitur</a>
                <a class="inline-flex items-center gap-2 bg-neutral-900 text-white px-4 py-2 rounded-md" href="/login">Masuk</a>
            </nav>
        </header>

        <main class="px-6">
            <section class="mx-auto max-w-7xl grid grid-cols-1 gap-8 lg:grid-cols-2 items-start">
                <!-- Left: Docs-like panel -->
                <div id="docs" class="rounded-2xl bg-white ring-1 ring-neutral-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5" style="border-bottom:1px solid rgba(0,0,0,.06)">
                        <p class="text-sm text-neutral-500">WA Unofficial — API Docs</p>
                        <h1 class="text-2xl font-semibold text-neutral-900 mt-1">Sessions, Messages, Broadcasts</h1>
                    </div>
                    <div class="divide-y" style="border-top:1px solid rgba(0,0,0,.06)">
                        <div class="p-6 flex items-start gap-3" style="border-bottom:1px solid rgba(0,0,0,.06)">
                            <span class="px-2 py-1 text-xs font-medium rounded" style="background:#dcfce7;color:#15803d">POST</span>
                            <div>
                                <p class="font-medium text-neutral-900">/sessions/{clientId}/reconnect</p>
                                <p class="text-sm text-neutral-600">Reconnect sesi WhatsApp yang terputus.</p>
                            </div>
                        </div>
                        <div class="p-6 flex items-start gap-3" style="border-bottom:1px solid rgba(0,0,0,.06)">
                            <span class="px-2 py-1 text-xs font-medium rounded" style="background:#dcfce7;color:#15803d">POST</span>
                            <div>
                                <p class="font-medium text-neutral-900">/sessions</p>
                                <p class="text-sm text-neutral-600">Buat atau mulai sesi WhatsApp baru.</p>
                            </div>
                        </div>
                        <div class="p-6 flex items-start gap-3" style="border-bottom:1px solid rgba(0,0,0,.06)">
                            <span class="px-2 py-1 text-xs font-medium rounded" style="background:#dbeafe;color:#1d4ed8">GET</span>
                            <div>
                                <p class="font-medium text-neutral-900">/jobs/{id}</p>
                                <p class="text-sm text-neutral-600">Cek status background job.</p>
                            </div>
                        </div>
                        <div class="p-6 flex items-start gap-3" style="border-bottom:1px solid rgba(0,0,0,.06)">
                            <span class="px-2 py-1 text-xs font-medium rounded" style="background:#dbeafe;color:#1d4ed8">GET</span>
                            <div>
                                <p class="font-medium text-neutral-900">/inbound</p>
                                <p class="text-sm text-neutral-600">Daftar pesan masuk terbaru.</p>
                            </div>
                        </div>
                        <div class="p-6 flex items-start gap-3">
                            <span class="px-2 py-1 text-xs font-medium rounded" style="background:#dcfce7;color:#15803d">POST</span>
                            <div>
                                <p class="font-medium text-neutral-900">/broadcasts</p>
                                <p class="text-sm text-neutral-600">Buat kampanye broadcast.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: WhatsApp-like chat preview -->
                <div class="rounded-2xl bg-[#ECE5DD] ring-1 ring-neutral-200 shadow-sm overflow-hidden">
                    <div class="h-14 bg-[#075E54] text-white flex items-center px-4">Contoh Percakapan</div>
                    <div class="p-4 space-y-2" style="background-image:url('data:image/svg+xml;utf8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' viewBox=\'0 0 120 120\'%3E%3Cg fill=\'%23d9d7d1\' fill-opacity=\'0.3\'%3E%3Ccircle cx=\'10\' cy=\'10\' r=\'2\'/%3E%3Ccircle cx=\'60\' cy=\'60\' r=\'2\'/%3E%3Ccircle cx=\'110\' cy=\'110\' r=\'2\'/%3E%3C/g%3E%3C/svg%3E')">
                        <div class="max-w-[75%] rounded-lg bg-white px-3 py-2 shadow-sm">Hai juga! Ada yang bisa saya bantu?</div>
                        <div class="flex justify-end">
                            <div class="max-w-[75%] rounded-lg" style="background:#DCF8C6"><div class="px-3 py-2">Alhamdulillah baik, saya mau lihat minuman segar dong.</div></div>
                        </div>
                        <div class="max-w-[75%] rounded-lg bg-white px-3 py-2 shadow-sm">Kita ada Cola dan Sprite, keduanya 10 ribu. Mau yang mana?</div>
                        <div class="flex justify-end">
                            <div class="max-w-[75%] rounded-lg" style="background:#DCF8C6"><div class="px-3 py-2">Ambil Cola ya.</div></div>
                        </div>
                        <div class="max-w-[75%] rounded-lg bg-white px-3 py-2 shadow-sm">Siap. Total 10.000. Transfer ke BCA a.n. Daud 003003002.</div>
                        <div class="flex justify-end">
                            <div class="max-w-[75%] rounded-lg" style="background:#DCF8C6"><div class="px-3 py-2">Oke, sudah transfer. Terima kasih!</div></div>
                        </div>
                        <div class="max-w-[75%] rounded-lg bg-white px-3 py-2 shadow-sm">Sama-sama! Jika ada kebutuhan lain, kabari ya 😊</div>
                    </div>
                </div>
            </section>

            <!-- Footer brand strip -->
            <section class="mt-12 rounded-2xl bg-black text-white py-10 flex items-center justify-center gap-3">
                <svg width="32" height="32" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                    <path d="M24 6c9.389 0 17 6.716 17 15 0 8.284-7.611 15-17 15-1.97 0-3.867-.286-5.636-.818L8 42l2.723-7.308C8.43 31.997 7 29.14 7 26 7 17.716 14.611 6 24 6Z" fill="#10B981"/>
                    <path d="M26 12l-5 9h6l-3 8 10-11h-7l4-6h-5Z" fill="#fff"/>
                </svg>
                <span class="text-xl font-semibold tracking-tight">Balas Cepat</span>
            </section>
        </main>
    </body>
</html>


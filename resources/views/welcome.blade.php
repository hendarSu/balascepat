<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ config('app.description') }}">

        <title>{{ config('app.name', 'BalasCepat') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles: Tailwind via CDN just for the landing page -->
        <script src="https://cdn.tailwindcss.com"></script>

        @if (config('services.meta_pixel.enabled') && config('services.meta_pixel.id'))
            <!-- Meta Pixel Code -->
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{{ config('services.meta_pixel.id') }}');
                fbq('track', 'PageView');
            </script>
            <noscript>
                <img height="1" width="1" style="display:none" alt=""
                     src="https://www.facebook.com/tr?id={{ config('services.meta_pixel.id') }}&ev=PageView&noscript=1"/>
            </noscript>
            <!-- End Meta Pixel Code -->
        @endif
    </head>
    <body class="antialiased text-gray-800">
        <header class="border-b">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="/favicon.svg" alt="Logo" class="h-8 w-8 rounded-md">
                    <span class="font-semibold">{{ config('app.name', 'BalasCepat') }}</span>
                </div>
                <nav class="flex items-center gap-3 text-sm">
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Masuk</a>
                    <a href="{{ route('register') }}" class="js-lead-cta inline-flex items-center rounded-md bg-green-600 px-3 py-2 font-medium text-white hover:bg-green-700">Daftar</a>
                </nav>
            </div>
        </header>

        <main>
            <!-- Hero -->
            <section class="bg-gradient-to-b from-green-50 to-white">
                <div class="max-w-7xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-10 items-center">
                    <div>
                        <h1 class="text-3xl md:text-5xl font-bold leading-tight text-gray-900">
                            Satu platform untuk otomasi pesan, pelanggan, dan konten Anda
                        </h1>
                        <p class="mt-4 text-lg text-gray-600">
                            Kelola broadcast WhatsApp, otomatisasi alur, pelanggan & grup, dan integrasi N8N — semuanya dalam satu tempat.
                        </p>
                        <div class="mt-6 flex gap-3">
                            <a href="{{ route('register') }}" class="js-lead-cta inline-flex items-center rounded-md bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">
                                Daftar Sekarang
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                                Coba Masuk
                            </a>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">Tidak perlu kartu kredit.</p>
                    </div>
                    <div class="relative">
                        <img src="/heros.png" alt="Ilustrasi fitur BalasCepat" class="rounded-xl border bg-white shadow-sm w-full h-auto" loading="lazy">
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section class="py-14">
                <div class="max-w-7xl mx-auto px-6">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Fitur utama</h2>
                    <div class="mt-8 grid md:grid-cols-3 gap-6">
                        <div class="p-6 rounded-xl border bg-white">
                            <div class="font-semibold">Broadcast WhatsApp</div>
                            <p class="mt-2 text-gray-600">Buat dan kirim kampanye, pantau riwayat pengiriman, dan lihat detail tiap broadcast.</p>
                        </div>
                        <div class="p-6 rounded-xl border bg-white">
                            <div class="font-semibold">WA Unofficial</div>
                            <p class="mt-2 text-gray-600">Kelola sesi, automation, playground pesan, serta dokumentasi & plugin pendukung.</p>
                        </div>
                        <div class="p-6 rounded-xl border bg-white">
                            <div class="font-semibold">Manajemen Pelanggan</div>
                            <p class="mt-2 text-gray-600">Kelola pelanggan dan grup, impor data, dan gunakan untuk segmentasi broadcast.</p>
                        </div>
                        <div class="p-6 rounded-xl border bg-white">
                            <div class="font-semibold">Channel Notifikasi</div>
                            <p class="mt-2 text-gray-600">Dukungan WA Unofficial, dan integrasi N8N sebagai saluran notifikasi.</p>
                        </div>
                        <div class="p-6 rounded-xl border bg-white">
                            <div class="font-semibold">Integrasi N8N</div>
                            <p class="mt-2 text-gray-600">Lihat detail dan embed workflow N8N langsung di aplikasi.</p>
                        </div>

                    </div>
                    <div class="mt-10">
                        <a href="{{ route('register') }}" class="js-lead-cta inline-flex items-center rounded-md bg-green-600 px-6 py-3 font-semibold text-white hover:bg-green-700">
                            Mulai Gratis — Daftar Sekarang
                        </a>
                    </div>
                </div>
            </section>

            <!-- Social proof / benefits -->
            <section class="py-12 bg-gray-50 border-t border-b">
                <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-6 text-center">
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">Aman</div>
                        <div class="mt-2 text-gray-600">Keamanan data dan kontrol akses yang terjaga.</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">Cepat</div>
                        <div class="mt-2 text-gray-600">Proses broadcast dan otomasi yang andal.</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">Sederhana</div>
                        <div class="mt-2 text-gray-600">Antarmuka ringkas untuk tim pemasaran & operasi.</div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Pricing -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-6">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center">Paket Harga</h2>
                <p class="mt-2 text-center text-gray-600">Pilih paket sesuai kebutuhan operasional Anda</p>

                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <!-- WA Service -->
                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <div class="text-sm font-semibold text-green-700">Layanan WA</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <div class="text-3xl font-extrabold text-gray-900">Rp99K</div>
                            <div class="text-gray-500">/ bulan</div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Fitur WA Unofficial untuk operasional inti</p>
                        <ul class="mt-5 space-y-2 text-sm text-gray-700">
                            <li>• Session & Automation WA</li>
                            <li>• Playground Pesan</li>
                            <li>• Dokumentasi & Plugins</li>
                            <li>• Broadcast WhatsApp + Riwayat</li>
                            <li>• Pelanggan & Grup</li>
                            <li>• Channel Notifikasi: WA Unofficial (Header)</li>
                        </ul>
                        <div class="mt-6">
                            <a href="{{ route('register') }}" class="js-lead-cta inline-flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-2.5 font-semibold text-white hover:bg-green-700">
                                Pilih Paket
                            </a>
                        </div>
                    </div>

                    <!-- WA + N8N -->
                    <div class="rounded-2xl border-2 border-green-600 bg-white p-6 shadow-sm">
                        <div class="inline-flex items-center gap-2">
                            <div class="text-sm font-semibold text-green-700">WA + Integrasi N8N</div>
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-semibold text-green-700">Paling Populer</span>
                        </div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <div class="text-3xl font-extrabold text-gray-900">Rp199K</div>
                            <div class="text-gray-500">/ bulan</div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Semua fitur WA, plus workflow otomatis via N8N</p>
                        <ul class="mt-5 mb-5 space-y-2 text-sm text-gray-700">
                            <li>• Semua fitur paket WA</li>
                            <li>• Integrasi N8N (embed workflow & detail)</li>
                            <li>• Channel Notifikasi: N8N</li>
                            <li>• Otomasi lanjutan via workflow</li>
                        </ul>
                        <div class="mt-10">
                            <a href="{{ route('register') }}" class="js-lead-cta inline-flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-2.5 font-semibold text-white hover:bg-green-700" style="margin-top: 35px;">
                                Pilih Paket
                            </a>
                        </div>
                    </div>

                    <!-- Custom / Enterprise -->
                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <div class="text-sm font-semibold text-green-700">Custom / Enterprise</div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <div class="text-3xl font-extrabold text-gray-900">Kebutuhan Khusus</div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Konsultasi kebutuhan, SLA, dan dukungan implementasi</p>
                        <ul class="mt-5 space-y-2 text-sm text-gray-700">
                            <li>• Penyesuaian alur & integrasi</li>
                            <li>• Bantuan migrasi & onboarding</li>
                            <li>• Dukungan prioritas & SLA</li>
                        </ul>
                        <div class="mt-6">
                            @php($contact = config('mail.from.address'))
                            <a href="{{ $contact ? 'mailto:'.$contact : '#' }}" class="inline-flex w-full items-center justify-center rounded-md border px-4 py-2.5 font-semibold text-gray-800 hover:bg-gray-50">
                                Kontak Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-8">
            <div class="max-w-7xl mx-auto px-6 text-sm text-gray-500 flex items-center justify-between">
                <div>© {{ date('Y') }} {{ config('app.name', 'BalasCepat') }}</div>
                <div class="flex gap-4">
                    <a href="{{ route('login') }}" class="hover:text-gray-700">Masuk</a>
                    <a href="{{ route('register') }}" class="js-lead-cta hover:text-gray-700">Daftar</a>
                </div>
            </div>
        </footer>
        @if (config('services.meta_pixel.enabled') && config('services.meta_pixel.id'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var ctas = document.querySelectorAll('.js-lead-cta');
                ctas.forEach(function(el){
                    el.addEventListener('click', function(){
                        if (window.fbq) { fbq('track', 'Lead'); }
                    });
                });
            });
        </script>
        @endif
    </body>
    </html>

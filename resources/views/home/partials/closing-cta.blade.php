@php
    $whatsappNumber = preg_replace('/\D+/', '', config('services.daiku.whatsapp_number'));
    $whatsappMessage = rawurlencode('Halo Daiku, saya ingin berkonsultasi mengenai kebutuhan interior saya.');
    $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
@endphp

<section class="relative isolate overflow-hidden bg-[#fff8ed] text-slate-900" aria-labelledby="closing-cta-title">
    <div class="absolute -left-32 top-1/2 h-80 w-80 -translate-y-1/2 rounded-full bg-amber-300/25 blur-3xl" aria-hidden="true"></div>

    <div class="grid lg:min-h-[560px] lg:grid-cols-2">
        <div class="relative z-10 flex items-center">
            <div class="mx-auto w-full max-w-2xl px-6 py-20 sm:px-10 lg:px-16 lg:py-24 xl:px-24">
                <div class="flex items-center gap-3 text-sm font-bold uppercase tracking-[0.2em] text-amber-400">
                    <span class="h-px w-10 bg-amber-400" aria-hidden="true"></span>
                    Siapkan data awal
                </div>

                <h2 id="closing-cta-title" class="mt-7 max-w-xl text-4xl font-bold leading-tight sm:text-5xl">
                    Bawa ukuran, denah, atau foto ruang Anda.
                </h2>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                    Kirim informasi awal melalui formulir atau WhatsApp. Tim Daiku akan meninjau jenis ruang, fungsi, dan cakupan yang ingin dikerjakan.
                </p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('konsultasi.index') }}" class="group inline-flex items-center justify-center gap-3 rounded-lg bg-amber-400 px-6 py-3.5 font-bold text-slate-950 transition hover:bg-amber-300">
                        Isi formulir konsultasi
                        <i class="fas fa-arrow-right text-sm transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-3 rounded-lg border border-slate-300 bg-white px-6 py-3.5 font-semibold text-slate-800 transition hover:border-amber-400 hover:bg-amber-50">
                        <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i>
                        WhatsApp Daiku
                    </a>
                </div>

                <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 border-t border-amber-200 pt-6 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-location-dot text-amber-400" aria-hidden="true"></i>
                        Pekanbaru, Riau
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-house text-amber-400" aria-hidden="true"></i>
                        Hunian, kantor, dan usaha
                    </span>
                </div>
            </div>
        </div>

        <div class="relative min-h-[360px] overflow-hidden lg:min-h-full">
            <img src="{{ asset('images/katalog/rumah/rumah (628).jpg') }}"
                 alt="Interior ruang keluarga karya Daiku"
                 class="absolute inset-0 h-full w-full object-cover"
                 loading="lazy"
                 decoding="async"
                 width="1307"
                 height="1067">
            <div class="absolute inset-0 bg-gradient-to-t from-white/35 via-transparent to-transparent"></div>
            <div class="absolute bottom-7 left-7 right-7 max-w-sm border-l-2 border-amber-400 bg-white/90 px-5 py-4 shadow-lg backdrop-blur-md sm:bottom-10 sm:left-10">
                <p class="text-sm leading-relaxed text-slate-700">
                    Ukuran, kondisi eksisting, dan referensi visual membantu pembahasan desain menjadi lebih tepat.
                </p>
            </div>
        </div>
    </div>
</section>

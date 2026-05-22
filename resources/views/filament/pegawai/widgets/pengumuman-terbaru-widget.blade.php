<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-megaphone" icon-color="primary">
        <x-slot name="heading">
            <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Pengumuman Terbaru</span>
        </x-slot>

        @php
            $pengumumanList = $this->getPengumuman();
        @endphp

        @if($pengumumanList->isEmpty())
            <div class="flex flex-col items-center justify-center p-8 text-center bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-400 mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4h16z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tidak Ada Pengumuman</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Saat ini belum ada pengumuman aktif dari pihak admin.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                @foreach($pengumumanList as $item)
                    <div x-data="{ open: false }" class="relative flex flex-col p-5 bg-white dark:bg-gray-900/40 backdrop-blur-md rounded-2xl border border-gray-100 dark:border-gray-800/80 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-0.5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 mb-2 border border-teal-100 dark:border-teal-900/30">
                                    {{ $item->created_at->diffForHumans() }}
                                </span>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">
                                    {{ $item->judul }}
                                </h3>
                            </div>
                        </div>

                        <!-- Shortened Preview -->
                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-300 line-clamp-3">
                            {!! strip_tags($item->isi) !!}
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                            <span class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                                {{ $item->creator->name ?? 'Admin' }}
                            </span>
                            
                            <button @click="open = true" class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:text-teal-500 transition-colors flex items-center gap-1">
                                Baca Selengkapnya
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Beautiful Details Modal with Blur Backdrop -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/65 backdrop-blur-sm"
                             style="display: none;">
                            <div @click.away="open = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-800 flex flex-col max-h-[85vh] transition-all duration-300">
                                <div class="flex justify-between items-start pb-4 border-b border-gray-100 dark:border-gray-800/80">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900/30">
                                                {{ $item->created_at->format('d M Y H:i') }}
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                Oleh: {{ $item->creator->name ?? 'Admin' }}
                                            </span>
                                        </div>
                                        <h2 class="text-xl font-extrabold text-gray-900 dark:text-white leading-tight">
                                            {{ $item->judul }}
                                        </h2>
                                    </div>
                                    <button @click="open = false" class="p-1.5 rounded-full text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="mt-4 overflow-y-auto pr-2 text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none">
                                    {!! $item->isi !!}
                                </div>
                                
                                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/80 flex justify-end">
                                    <button @click="open = false" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-semibold text-sm shadow-sm hover:shadow transition-colors">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

<x-mail::layout>
    {{-- ================= HEADER ================= --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="Visa Simplify"
                style="
        max-width: 160px;
        height: auto;
        display: block;
        margin: 0 auto;
    "
            >

        </x-mail::header>
    </x-slot:header>

    {{-- ================= BODY ================= --}}
    <div style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
        {!! $slot !!}
    </div>

    {{-- ================= SUBCOPY ================= --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {!! $subcopy !!}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- ================= FOOTER ================= --}}
    <x-slot:footer>
        <x-mail::footer>
            <p style="margin: 0; font-size: 12px; color: #6b7280;">
                © {{ date('Y') }} <strong>Visa Simplify</strong>. All rights reserved.
            </p>

            <p style="margin: 6px 0 0; font-size: 12px; color: #9ca3af;">
                This email was sent by Visa Simplify.
            </p>
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>

{{-- resources/views/schedule/form.blade.php --}}

<x-layout>
    <div class="max-w-xl mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Agendar com {{ $user->name }}</h2>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 text-red-800 p-2 rounded mb-3">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('schedule.store', $user->id) }}" class="space-y-4">
            @csrf

            <label for="date" class="block font-semibold">Data:</label>
            <select name="date" id="date" class="w-full border rounded p-2">
                @foreach ($available as $date => $times)
                    <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                @endforeach
            </select>

            <label for="time" class="block font-semibold">Horário:</label>
            <select name="time" id="time" class="w-full border rounded p-2">
                {{-- JavaScript vai preencher com base no dia selecionado --}}
            </select>

            <input type="text" name="name" placeholder="Seu nome" class="w-full border rounded p-2" required />
            <input type="email" name="email" placeholder="Seu email" class="w-full border rounded p-2" required />

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Agendar</button>
        </form>
    </div>

    <script>
        const slots = @json($available);
        const dateSelect = document.getElementById('date');
        const timeSelect = document.getElementById('time');

        function updateTimes() {
            const date = dateSelect.value;
            timeSelect.innerHTML = '';

            if (slots[date]) {
                slots[date].forEach(time => {
                    const opt = document.createElement('option');
                    opt.value = time;
                    opt.text = time;
                    timeSelect.appendChild(opt);
                });
            }
        }

        dateSelect.addEventListener('change', updateTimes);
        updateTimes();
    </script>
</x-layout>
